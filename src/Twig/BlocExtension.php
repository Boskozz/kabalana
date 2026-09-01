<?php

namespace App\Twig;

use App\Entity\Bloc;
use App\Security\BlocAccessManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Environment;

class BlocExtension extends AbstractExtension
{
    private Environment $twig;
    private BlocAccessManager $accessManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct(Environment $twig, BlocAccessManager $accessManager, TokenStorageInterface $tokenStorage)
    {
        $this->twig = $twig;
        $this->accessManager = $accessManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_bloc', [$this, 'renderBloc'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('en_rangees', [$this, 'blocsEnRangees']),
        ];
    }

    /**
     * Regroupe les blocs actifs en rangées Bootstrap selon leur nombre de colonnes.
     * Un bloc "1 colonne" occupe la pleine largeur et coupe la rangée courante.
     *
     * @param iterable<Bloc> $blocs
     * @return array<int, array<int, array{bloc: Bloc, width: int}>>
     */
    public function blocsEnRangees(iterable $blocs): array
    {
        $rows = [];
        $currentRow = [];
        $currentWidth = 0;

        foreach ($blocs as $bloc) {
            if (!$bloc instanceof Bloc || !$bloc->isActive()) {
                continue;
            }

            $user = $this->tokenStorage->getToken()?->getUser();
            if (!$this->accessManager->isAccessible($bloc, $user instanceof UserInterface ? $user : null)) {
                continue;
            }

            $width = 12;
            if ($bloc->getColonnes() !== null && $bloc->getColonnes() > 1) {
                $width = max(1, intdiv(12, $bloc->getColonnes()));
            }

            if ($width >= 12) {
                if ($currentRow !== []) {
                    $rows[] = $currentRow;
                    $currentRow = [];
                    $currentWidth = 0;
                }
                $rows[] = [['bloc' => $bloc, 'width' => 12]];
                continue;
            }

            if ($currentWidth + $width > 12) {
                $rows[] = $currentRow;
                $currentRow = [];
                $currentWidth = 0;
            }

            $currentRow[] = ['bloc' => $bloc, 'width' => $width];
            $currentWidth += $width;
        }

        if ($currentRow !== []) {
            $rows[] = $currentRow;
        }

        return $rows;
    }

    public function renderBloc(Bloc $bloc): string
    {
        $template = match ($bloc->getType()) {
            'titre' => 'front/bloc/titre.html.twig',
            'paragraphe' => 'front/bloc/paragraphe.html.twig',
            'images_groupe' => 'front/bloc/images_groupe.html.twig',
            'liens' => 'front/bloc/liens.html.twig',
            'video' => 'front/bloc/video.html.twig',
            'audio' => 'front/bloc/audio.html.twig',  // 👈 Nouveau
            'document' => 'front/bloc/document.html.twig',  // 👈 Nouveau
            default => 'front/bloc/default.html.twig',
        };

        $content = json_decode($bloc->getContent(), true);

        return $this->twig->render($template, [
            'bloc' => $bloc,
            'data' => is_array($content) ? $content : []
        ]);
    }
}
