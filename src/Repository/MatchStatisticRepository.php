<?php

namespace App\Repository;

use App\Entity\MatchStatistic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MatchStatisticRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MatchStatistic::class);
    }

    public function findByGame($gameId)
    {
        return $this->createQueryBuilder('ms')
            ->andWhere('ms.game = :val')
            ->setParameter('val', $gameId)
            ->orderBy('ms.kills', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByPlayer($playerId)
    {
        return $this->createQueryBuilder('ms')
            ->andWhere('ms.player = :val')
            ->setParameter('val', $playerId)
            ->orderBy('ms.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function searchAndFilter($search = null, $role = null, $kdaMin = null, $kdaMax = null)
    {
        $qb = $this->createQueryBuilder('ms')
            ->leftJoin('ms.player', 'p')
            ->leftJoin('ms.game', 'g');

        // Search by player nickname
        if ($search) {
            $qb->andWhere('p.nickname LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Filter by role
        if ($role) {
            $qb->andWhere('ms.role = :role')
                ->setParameter('role', $role);
        }

        // Filter by KDA minimum
        if ($kdaMin !== null) {
            $qb->andWhere('CASE WHEN ms.deaths = 0 THEN ms.kills + ms.assists ELSE (ms.kills + ms.assists) / ms.deaths END >= :kdaMin')
                ->setParameter('kdaMin', $kdaMin);
        }

        // Filter by KDA maximum
        if ($kdaMax !== null) {
            $qb->andWhere('CASE WHEN ms.deaths = 0 THEN ms.kills + ms.assists ELSE (ms.kills + ms.assists) / ms.deaths END <= :kdaMax')
                ->setParameter('kdaMax', $kdaMax);
        }

        return $qb->orderBy('ms.createdAt', 'DESC');
    }

    public function getAvailableRoles()
    {
        return $this->createQueryBuilder('ms')
            ->select('DISTINCT ms.role')
            ->where('ms.role IS NOT NULL')
            ->orderBy('ms.role', 'ASC')
            ->getQuery()
            ->getScalarResult();
    }
}
