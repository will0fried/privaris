<?php

namespace App\Repository;

use App\Entity\Subscriber;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subscriber>
 */
class SubscriberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscriber::class);
    }

    public function existsForEmail(string $email): bool
    {
        return null !== $this->findOneBy(['email' => strtolower(trim($email))]);
    }

    public function save(Subscriber $subscriber, bool $flush = true): void
    {
        $this->getEntityManager()->persist($subscriber);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
