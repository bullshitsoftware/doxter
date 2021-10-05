<?php

namespace App\Repository;

use App\Doctrine\OrderByNullSqlWalker;
use App\Doctrine\Pagination;
use App\Entity\Task;
use App\Entity\User;
use App\Service\TaskSearchFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
    private TaskSearchFilter $searchFilter;

    public function __construct(ManagerRegistry $registry, TaskSearchFilter $searchFilter)
    {
        $this->searchFilter = $searchFilter;

        parent::__construct($registry, Task::class);
    }

    /**
     * @return Task[]
     */
    public function findCurrentByUser(User $user, ?string $search = null): array
    {
        $queryBuilder = $this->createQueryBuilder('task')
            ->select('task', 'tags')
            ->leftJoin('task.tags', 'tags')
            ->andWhere('task.user = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->andWhere('task.wait IS NULL OR task.wait < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->andWhere('task.ended IS NULL')
            ->addOrderBy('task.started', 'ASC')
            ->addOrderBy('task.created', 'ASC');

        if ($search !== null) {
            $this->searchFilter->apply($queryBuilder, 'task', $search);
        }

        return $queryBuilder->getQuery()
            ->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, OrderByNullSqlWalker::class)
            ->setHint(OrderByNullSqlWalker::HINT, ['started' => OrderByNullSqlWalker::LAST])
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findWaitingByUser(User $user, ?string $search = null): array
    {
        $queryBuilder = $this->createQueryBuilder('task')
            ->select('task', 'tags')
            ->leftJoin('task.tags', 'tags')
            ->andWhere('task.user = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->andWhere('task.wait IS NOT NULL AND task.wait > :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->andWhere('task.ended IS NULL')
            ->addOrderBy('task.wait', 'ASC')
            ->addOrderBy('task.created', 'ASC');

        if ($search !== null) {
            $this->searchFilter->apply($queryBuilder, 'task', $search);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return Task[]
     */
    public function findCompletedByUser(User $user, int $page, ?string $search = null): Pagination
    {
        $queryBuilder = $this->createQueryBuilder('task')
            ->select('task', 'tags')
            ->leftJoin('task.tags', 'tags')
            ->andWhere('task.user = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->andWhere('task.ended IS NOT NULL')
            ->addOrderBy('task.ended', 'DESC')
            ->addOrderBy('task.created', 'ASC')
            ->setFirstResult(($page - 1) * 30)
            ->setMaxResults(31);

        if ($search !== null) {
            $this->searchFilter->apply($queryBuilder, 'task', $search);
        }
        $paginator = new Paginator($queryBuilder->getQuery());

        $items = iterator_to_array($paginator);
        if (count($items) > 10) {
            return new Pagination(array_slice($items, 0, -1), true);
        }

        return new Pagination($items, false);
    }
}
