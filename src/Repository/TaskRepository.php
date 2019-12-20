<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Find a task with author different
     *
     * @param $user User
     * @return Task
     */
    public function findTaskWithDifferentUser($user = null)
    {

        // do query
        $queryBuilder = $this->createQueryBuilder('t')
            ->addSelect('u')
            ->leftJoin('t.user', 'u', 'WITH', null, 'u.id')
            ->andWhere('t.user != :user')
            ->andWhere('u.roles LIKE \'%ROLE_USER%\'')
            ->andWhere('t.user != \'\'')
            ->setParameter('user', $user)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
        return $queryBuilder[0];

    }

}
