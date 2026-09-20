<?php

namespace App\Repository;

use App\Entity\Constat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Constat>
 */
class ConstatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Constat::class);
    }

    /**
     * Le constat majeur le plus récent, non retiré.
     */
    public function findConstatMajeur(): ?Constat
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.majeur = true')
            ->andWhere('c.retire = false')
            ->orderBy('c.datePublication', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Les constats les plus récents (hors un constat à exclure, ex. le majeur déjà affiché).
     *
     * @return Constat[]
     */
    public function findRecents(int $limit = 4, ?Constat $exclu = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.retire = false')
            ->orderBy('c.datePublication', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->setMaxResults($limit);

        if ($exclu instanceof Constat && null !== $exclu->getId()) {
            $qb->andWhere('c.id != :exclu')->setParameter('exclu', $exclu->getId());
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Tous les constats, du plus récent au plus ancien (page d'archive).
     *
     * @return Constat[]
     */
    public function findAllRecent(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.datePublication', 'DESC')
            ->addOrderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countActifs(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.retire = false')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
