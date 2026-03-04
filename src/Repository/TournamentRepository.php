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

    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.games', 'g')
            ->addSelect('g')
            ->leftJoin('t.registrations', 'r')
            ->addSelect('r')
            ->where('t.name LIKE :searchTerm')
            ->orWhere('t.description LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('t.name', 'ASC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.games', 'g')
            ->addSelect('g')
            ->leftJoin('t.registrations', 'r')
            ->addSelect('r')
            ->where('t.status = :status')
            ->setParameter('status', $status)
            ->orderBy('t.startDate', 'DESC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function findUpcoming(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.games', 'g')
            ->addSelect('g')
            ->leftJoin('t.registrations', 'r')
            ->addSelect('r')
            ->where('t.status IN (:statuses)')
            ->setParameter('statuses', ['pending', 'ongoing'])
            ->orderBy('t.startDate', 'ASC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function findAllOrdered(string $orderBy = 'startDate'): array
    {
        $validOrderBy = ['name', 'startDate', 'endDate', 'createdAt'];
        $orderBy = in_array($orderBy, $validOrderBy) ? $orderBy : 'startDate';

        return $this->createQueryBuilder('t')
            ->leftJoin('t.games', 'g')
            ->addSelect('g')
            ->leftJoin('t.registrations', 'r')
            ->addSelect('r')
            ->orderBy('t.' . $orderBy, 'DESC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function createAdminListQueryBuilder(string $search = '', string $status = '', string $orderBy = 'startDate', string $direction = 'DESC')
    {
        $validOrderBy = ['name', 'startDate', 'endDate', 'createdAt'];
        $orderBy = in_array($orderBy, $validOrderBy) ? $orderBy : 'startDate';
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.games', 'g')
            ->addSelect('g')
            ->leftJoin('t.registrations', 'r')
            ->addSelect('r');

        // Apply search filter
        if (!empty($search)) {
            $qb->andWhere('t.name LIKE :search OR t.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Apply status filter
        if (!empty($status)) {
            $qb->andWhere('t.status = :status')
               ->setParameter('status', $status);
        }

        $qb->orderBy('t.' . $orderBy, $direction)
            ->distinct();

        return $qb;
    }

    public function getStatusCountsForFilters(string $search = '', string $status = ''): array
    {
        $statuses = ['pending', 'ongoing', 'completed', 'cancelled'];
        $counts = [];

        foreach ($statuses as $stat) {
            $qb = $this->createQueryBuilder('t')
                ->select('COUNT(t.id)')
                ->where('t.status = :status')
                ->setParameter('status', $stat);

            // Apply search filter if provided
            if (!empty($search)) {
                $qb->andWhere('t.name LIKE :search OR t.description LIKE :search')
                   ->setParameter('search', '%' . $search . '%');
            }

            // Note: $status parameter in this context is the filter, not the stat
            // So we only apply it if it matches current stat
            if (!empty($status) && $status !== $stat) {
                $counts[$stat] = 0;
            } else {
                $counts[$stat] = (int) $qb->getQuery()->getSingleScalarResult();
            }
        }

        return $counts;
    }
}
