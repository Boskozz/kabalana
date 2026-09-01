<?php

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    /**
     * Trouve une page active par son slug.
     */
    public function findActiveBySlug(string $slug): ?Page
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.isActive = true')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Retourne toutes les pages actives, triées par section puis position.
     * Utilisé pour construire l'arborescence du menu principal.
     */
    public function findAllActiveOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = true')
            ->orderBy('p.section', 'ASC')
            ->addOrderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByPageOrdered(int $pageId): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.page = :pageId')
            ->setParameter('pageId', $pageId)
            ->orderBy('b.articleNumber', 'ASC')
            ->addOrderBy('b.blocNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }


    /**
     * Trouve une page active par son slug. --> Pour le menu
     */
    public function findActiveSousSectionBySection(string $section): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.section = :section')
            ->andWhere('p.isActive = true')
            ->andWhere('p.position > 0')
            ->andWhere('p.parentId IS NULL')
            ->setParameter('section', $section)
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les pages enfants actives d'une page donnée (3e niveau de menu).
     */
    public function findActiveChildrenByParentId(int $parentId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.parentId = :parentId')
            ->andWhere('p.isActive = true')
            ->andWhere('p.position > 0')
            ->setParameter('parentId', $parentId)
            ->orderBy('p.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
