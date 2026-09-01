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
                    'children' => $this->buildSubChildren($page->getId(), $byParent),
                ];
            }

            $menu[] = [
                'label' => $item['label'],
                'section' => $item['section'],
                'url' => 'app_page_show',
                'params' => ['slug' => $root->getSlug()],
                'children' => $children,
            ];
        }

        return $menu;
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
