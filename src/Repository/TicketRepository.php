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
