<?php

namespace App\Twig;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class MediaExtension extends AbstractExtension
{
    private EntityManagerInterface $entityManager;

    /** @var array<int, Media|null> */
    private array $cache = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('resolve_medias', [$this, 'resolveMedias']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('media', [$this, 'getMedia']),
        ];
    }

    public function getMedia(?int $id): ?Media
    {
        if ($id === null) {
            return null;
        }

        if (!array_key_exists($id, $this->cache)) {
            $this->cache[$id] = $this->entityManager->getRepository(Media::class)->find($id);
        }

        return $this->cache[$id];
    }

    /**
     * Enrichit une liste d'images avec les données de la médiathèque.
     *
     * Chaque image peut référencer un média via "media" (son identifiant).
     * L'URL, le texte alternatif et la légende sont alors lus dans la table
     * "medias". Les valeurs écrites dans le JSON servent uniquement de repli
     * (rétrocompatibilité avec l'ancien format).
     *
     * @param array<int, array<string, mixed>> $images
     * @return array<int, array{url: string, alt: string, legende: ?string, class: string}>
     */
    public function resolveMedias(array $images): array
    {
        $resolved = [];

        foreach ($images as $image) {
            if (!is_array($image)) {
                continue;
            }

            $media = isset($image['media']) ? $this->getMedia((int) $image['media']) : null;
            $url = $media?->getUrl() ?? ($image['url'] ?? null);

            if ($url === null) {
                continue;
            }

            $resolved[] = [
                'url' => $url,
                'alt' => $media?->getAlt() ?? ($image['alt'] ?? ''),
                'legende' => $media?->getLegende() ?? ($image['legende'] ?? null),
                'class' => $image['class'] ?? '',
            ];
        }

        return $resolved;
    }
}
