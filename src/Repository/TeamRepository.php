<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.name LIKE :searchTerm')
            ->orWhere('t.country LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrdered(string $orderBy = 'name'): array
    {
        $validOrderBy = ['name', 'createdAt'];
        $orderBy = in_array($orderBy, $validOrderBy) ? $orderBy : 'name';

        return $this->createQueryBuilder('t')
            ->orderBy('t.' . $orderBy, 'ASC')
            ->getQuery()
            ->getResult();
    }
}
