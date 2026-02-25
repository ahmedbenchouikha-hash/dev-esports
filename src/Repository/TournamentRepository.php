<?php

namespace App\Repository;

use App\Entity\Tournament;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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
            ->where('t.name LIKE :searchTerm')
            ->orWhere('t.description LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->setParameter('status', $status)
            ->orderBy('t.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findUpcoming(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.status IN (:statuses)')
            ->setParameter('statuses', ['pending', 'ongoing'])
            ->orderBy('t.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrdered(string $orderBy = 'startDate'): array
    {
        $validOrderBy = ['name', 'startDate', 'endDate', 'createdAt'];
        $orderBy = in_array($orderBy, $validOrderBy) ? $orderBy : 'startDate';

        return $this->createQueryBuilder('t')
            ->orderBy('t.' . $orderBy, 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function createAdminListQueryBuilder(
        string $search = '',
        string $status = '',
        string $sort = 'startDate',
        string $direction = 'ASC'
    ): QueryBuilder {
        $validSorts = ['name', 'startDate', 'endDate', 'location', 'prizePool', 'status', 'createdAt'];
        $sort = in_array($sort, $validSorts, true) ? $sort : 'startDate';
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $qb = $this->createQueryBuilder('t');

        if ($search !== '') {
            $qb
                ->andWhere('t.name LIKE :search OR t.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status !== '') {
            $qb
                ->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        return $qb->orderBy('t.' . $sort, $direction);
    }

    public function getStatusCountsForFilters(string $search = '', string $status = ''): array
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t.status AS status, COUNT(t.id) AS total')
            ->groupBy('t.status');

        if ($search !== '') {
            $qb
                ->andWhere('t.name LIKE :search OR t.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status !== '') {
            $qb
                ->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        $rows = $qb->getQuery()->getArrayResult();

        $statusOrder = ['pending', 'ongoing', 'completed', 'cancelled'];
        $statusCounts = array_fill_keys($statusOrder, 0);

        foreach ($rows as $row) {
            $currentStatus = strtolower((string) ($row['status'] ?? ''));
            if (!array_key_exists($currentStatus, $statusCounts)) {
                $statusCounts[$currentStatus] = 0;
            }
            $statusCounts[$currentStatus] = (int) ($row['total'] ?? 0);
        }

        return $statusCounts;
    }
}
