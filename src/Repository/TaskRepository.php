<?php

declare(strict_types=1);

namespace App\Repository;

use App\Doctrine\Pagination;
use App\Entity\Task;
use App\Entity\User;
use App\Service\TaskSearchFilter;
use function array_slice;
use function count;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task|null findOneByTitle(string $title)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    private const TASK_PER_PAGE = 30;

    public function __construct(ManagerRegistry $registry, private TaskSearchFilter $searchFilter)
    {
        $this->searchFilter = $searchFilter;

        parent::__construct($registry, Task::class);
    }

    /**
     * @return Task[]
     */
    public function findCurrentByUser(User $user, ?string $search = null, DateTimeInterface $now): array
    {
        $queryBuilder = $this->createQueryBuilder('task')
            ->select('task')
            ->andWhere('task.user = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->andWhere('task.wait IS NULL OR task.wait < :now')
            ->setParameter('now', $now)
            ->andWhere('task.ended IS NULL')
        ;

        if (null !== $search) {
            $this->searchFilter->apply($queryBuilder, 'task', $search);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return Task[]
     */
    public function findWaitingByUser(User $user, ?string $search = null, DateTimeInterface $now): array
    {
        $queryBuilder = $this->createQueryBuilder('task')
            ->select('task')
            ->andWhere('task.user = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->andWhere('task.wait IS NOT NULL AND task.wait > :now')
            ->setParameter('now', $now)
            ->andWhere('task.ended IS NULL')
            ->addOrderBy('task.wait', 'ASC')
            ->addOrderBy('task.created', 'ASC');

        if (null !== $search) {
            $this->searchFilter->apply($queryBuilder, 'task', $search);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return Pagination<Task>
     */
    public function findCompletedByUser(User $user, int $page, ?string $search = null): Pagination
    {
        $queryBuilder = $this->createQueryBuilder('task')
            ->select('task')
            ->andWhere('task.user = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->andWhere('task.ended IS NOT NULL')
            ->addOrderBy('task.ended', 'DESC')
            ->addOrderBy('task.created', 'DESC')
            ->setFirstResult(($page - 1) * self::TASK_PER_PAGE)
            ->setMaxResults(self::TASK_PER_PAGE + 1);

        if (null !== $search) {
            $this->searchFilter->apply($queryBuilder, 'task', $search);
        }
        $paginator = new Paginator($queryBuilder->getQuery());

        $items = iterator_to_array($paginator);
        if (count($items) > self::TASK_PER_PAGE) {
            return new Pagination(array_slice($items, 0, -1), true);
        }

        return new Pagination($items, false);
    }
}
