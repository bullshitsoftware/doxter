<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task|null findOneByTitle(string $title)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return Task[]
     */
    public function findCurrentByUser(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->andWhere('t.wait IS NULL OR t.wait < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->andWhere('t.ended IS NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findWaitingByUser(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->andWhere('t.wait IS NOT NULL AND t.wait > :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->andWhere('t.ended IS NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findDoneByUser(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->andWhere('t.ended IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}
