<?php

namespace App\Twig;

use App\Entity\Page;
use App\Repository\PageRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MenuExtension extends AbstractExtension
{
    private const MAIN_ITEMS = [
        ['label' => 'Accueil', 'url' => 'app_home', 'params' => [], 'section' => 'home'],
        ['label' => 'Philosophe', 'section' => 'philosophe', 'root' => 'philosophie'],
        ['label' => 'Musicienne', 'section' => 'musicienne', 'root' => 'musicienne'],
        ['label' => 'Marionnettiste', 'section' => 'marionnettiste', 'root' => 'marionnettiste'],
        ['label' => 'Thérapeute', 'section' => 'therapeute', 'root' => 'therapeute'],
        ['label' => 'Contact', 'section' => 'contact', 'root' => 'contact'],
    ];

    public function __construct(private PageRepository $pageRepository) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('main_menu', [$this, 'getMainMenu']),
            new TwigFunction('breadcrumb', [$this, 'getBreadcrumb']),
        ];
    }

    /**
     * Construit l'arborescence du menu principal :
     * niveau 1 = sections, niveau 2 = sous-pages, niveau 3 = pages enfants.
     */
    public function getMainMenu(): array
    {
        // Si la base de données est en panne, on affiche un menu vide plutôt que
        // de faire planter la page (important pour la page d'erreur 500).
        try {
            $pages = $this->pageRepository->findAllActiveOrdered();
        } catch (\Throwable) {
            return [];
        }

        $bySection = [];
        $byParent = [];
        foreach ($pages as $page) {
            $bySection[$page->getSection()][] = $page;
            if ($page->getParentId() !== null) {
                $byParent[$page->getParentId()][] = $page;
            }
        }

        $menu = [];
        foreach (self::MAIN_ITEMS as $item) {
            if (isset($item['url'])) {
                $menu[] = [
                    'label' => $item['label'],
                    'section' => $item['section'],
                    'url' => $item['url'],
                    'params' => $item['params'],
                    'clickable' => true,
                    'children' => [],
                ];
                continue;
            }

            $root = $this->findRoot($pages, $item['root']);
            if (!$root) {
                continue;
            }

            $children = [];
            foreach ($bySection[$item['section']] ?? [] as $page) {
                if ($page->getId() === $root->getId() || $page->getPosition() === 0) {
                    continue;
                }
                $parentId = $page->getParentId();
                if ($parentId !== null && $parentId !== $root->getId()) {
                    continue;
                }
                $children[] = [
                    'label' => $page->getTitle(),
                    'url' => 'app_page_show',
                    'params' => ['slug' => $page->getSlug()],
                    'clickable' => !$page->isMenuWithoutLink(),
                    'children' => $this->buildSubChildren($page->getId(), $byParent),
                ];
            }

            $menu[] = [
                'label' => $item['label'],
                'section' => $item['section'],
                'url' => 'app_page_show',
                'params' => ['slug' => $root->getSlug()],
                'clickable' => !$root->isMenuWithoutLink(),
                'children' => $children,
            ];
        }

        return $menu;
    }

    /**
     * Construit le fil d'Ariane d'une page : Accueil > ...parents... > page courante.
     * Retourne un tableau de ['label' => string, 'url' => string, 'params' => array].
     * Renvoie un tableau vide pour la page d'accueil.
     */
    public function getBreadcrumb(Page $page): array
    {
        if ($page->getSection() === 'home') {
            return [];
        }

        try {
            $trail = [];
            $current = $page;
            $guard = 0;

            while ($current !== null && $guard++ < 10) {
                $trail[] = [
                    'label' => $current->getTitle(),
                    'url' => 'app_page_show',
                    'params' => ['slug' => $current->getSlug()],
                ];

                if ($current->getParentId() === null) {
                    break;
                }

                $current = $this->pageRepository->findActiveById($current->getParentId());
            }

            $trail = array_reverse($trail);

            // Les pages de 2e niveau ont souvent parentId = null : on rattache
            // explicitement la page racine de la section (ex. Musicienne > Pianiste).
            $rootSlug = $this->sectionRootSlug($page->getSection());
            if ($rootSlug !== null && $page->getSlug() !== $rootSlug) {
                $root = $this->pageRepository->findActiveBySlug($rootSlug);
                if ($root !== null && ($trail[0]['params']['slug'] ?? null) !== $rootSlug) {
                    array_unshift($trail, [
                        'label' => $root->getTitle(),
                        'url' => 'app_page_show',
                        'params' => ['slug' => $root->getSlug()],
                    ]);
                }
            }
        } catch (\Throwable) {
            $trail = [[
                'label' => $page->getTitle(),
                'url' => 'app_page_show',
                'params' => ['slug' => $page->getSlug()],
            ]];
        }

        array_unshift($trail, [
            'label' => 'Accueil',
            'url' => 'app_home',
            'params' => [],
        ]);

        return $trail;
    }

    /**
     * Retourne le slug de la page racine d'une section (ou null si inconnue).
     */
    private function sectionRootSlug(string $section): ?string
    {
        foreach (self::MAIN_ITEMS as $item) {
            if (($item['section'] ?? null) === $section) {
                return $item['root'] ?? null;
            }
        }

        return null;
    }

    private function buildSubChildren(int $parentId, array $byParent): array
    {
        $children = [];
        foreach ($byParent[$parentId] ?? [] as $page) {
            if ($page->getPosition() === 0) {
                continue;
            }
            $children[] = [
                'label' => $page->getTitle(),
                'url' => 'app_page_show',
                'params' => ['slug' => $page->getSlug()],
                'clickable' => !$page->isMenuWithoutLink(),
                'children' => [],
            ];
        }
        return $children;
    }

    private function findRoot(array $pages, string $slug): ?Page
    {
        foreach ($pages as $page) {
            if ($page->getSlug() === $slug) {
                return $page;
            }
        }
        return null;
    }
}
