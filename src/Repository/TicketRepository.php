<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    /**
     * Find all tickets with game and teams eagerly loaded (avoids N+1)
     */
    public function findAllWithGameAndTeams(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.game', 'g')
            ->addSelect('g')
            ->leftJoin('g.team1', 't1')
            ->addSelect('t1')
            ->leftJoin('g.team2', 't2')
            ->addSelect('t2')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find paginated tickets with game and teams eagerly loaded
     */
    public function findPaginatedWithGameAndTeams(int $limit = 50, int $offset = 0): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.game', 'g')
            ->addSelect('g')
            ->leftJoin('g.team1', 't1')
            ->addSelect('t1')
            ->leftJoin('g.team2', 't2')
            ->addSelect('t2')
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function findByGame($gameId)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.game = :val')
            ->setParameter('val', $gameId)
            ->orderBy('t.type', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByStatus($status)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.status = :status')
            ->setParameter('status', $status)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchAndFilter($search = null, $gameId = null, $status = null)
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.game', 'g');

        if ($search) {
            $qb->andWhere('t.ticketNumber LIKE :search OR t.type LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($gameId) {
            $qb->andWhere('t.game = :gameId')
                ->setParameter('gameId', $gameId);
        }

        if ($status) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        return $qb->orderBy('t.createdAt', 'DESC');
    }

    public function getTotalRevenue($gameId = null)
    {
        $qb = $this->createQueryBuilder('t')
            ->select('SUM(t.sold * t.price) as revenue');

        if ($gameId) {
            $qb->andWhere('t.game = :gameId')
                ->setParameter('gameId', $gameId);
        }

        return $qb->getQuery()->getSingleScalarResult() ?? 0;
    }
}
