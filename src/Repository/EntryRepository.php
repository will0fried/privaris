<?php

namespace App\Repository;

use App\Entity\Entry;
use App\Enum\EntryStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entry>
 */
class EntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entry::class);
    }

    /**
     * Toutes les entrées destinées au journal de bord (publiées + planifiées + en rédaction),
     * les plus récentes / prioritaires d'abord.
     *
     * @return Entry[]
     */
    public function findForJournal(int $limit = 12): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.publishedAt', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Uniquement les entrées publiées.
     *
     * @return Entry[]
     */
    public function findPublished(int $limit = 20): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.status = :status')
            ->setParameter('status', EntryStatus::PUBLISHED)
            ->orderBy('e.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Toutes les entrées du journal, sans limite (pour la page /carnet).
     *
     * @return Entry[]
     */
    public function findAllForJournal(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.publishedAt', 'DESC')
            ->addOrderBy('e.reference', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre total d'entrées du journal.
     */
    public function countForJournal(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPublished(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.status = :st')
            ->setParameter('st', EntryStatus::PUBLISHED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * La dernière entrée (par date) ayant produit au moins un constat non retiré.
     */
    public function findLatestWithConstats(): ?Entry
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.constats', 'c')
            ->andWhere('c.retire = false')
            ->andWhere('e.status = :published')
            ->setParameter('published', EntryStatus::PUBLISHED)
            ->orderBy('e.publishedAt', 'DESC')
            ->addOrderBy('e.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneBySlug(string $slug): ?Entry
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
