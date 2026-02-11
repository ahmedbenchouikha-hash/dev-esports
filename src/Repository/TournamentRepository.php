<?php

namespace App\Repository;

use App\Entity\Tournament;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tournament>
 */
class TournamentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournament::class);
    }

    public function findAllOrdered(string $sort = 'startDate', string $direction = 'ASC'): array
    {
        $validSortFields = ['startDate', 'name', 'createdAt', 'status', 'prizePool'];
        $sortField = in_array($sort, $validSortFields) ? $sort : 'startDate';
        $sortDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return $this->createQueryBuilder('t')
            ->orderBy('t.' . $sortField, $sortDirection)
            ->getQuery()
            ->getResult();
    }

    public function findBySearchTerm(string $term, string $sort = 'startDate', string $direction = 'ASC'): array
    {
        $validSortFields = ['startDate', 'name', 'createdAt', 'status', 'prizePool'];
        $sortField = in_array($sort, $validSortFields) ? $sort : 'startDate';
        $sortDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return $this->createQueryBuilder('t')
            ->where('t.name LIKE :term OR t.description LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('t.' . $sortField, $sortDirection)
            ->getQuery()
            ->getResult();
    }

    public function findByStatus(string $status, string $sort = 'startDate', string $direction = 'ASC'): array
    {
        $validSortFields = ['startDate', 'name', 'createdAt', 'status', 'prizePool'];
        $sortField = in_array($sort, $validSortFields) ? $sort : 'startDate';
        $sortDirection = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->setParameter('status', $status)
            ->orderBy('t.' . $sortField, $sortDirection)
            ->getQuery()
            ->getResult();
    }
}
