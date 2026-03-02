<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\TeamChatMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamChatMessage>
 */
class TeamChatMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamChatMessage::class);
    }

    /**
     * @return TeamChatMessage[]
     */
    public function findRecentForTeam(Team $team, int $limit = 50): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.team = :team')
            ->setParameter('team', $team)
            ->orderBy('m.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return TeamChatMessage[]
     */
    public function findAfterIdForTeam(Team $team, int $afterId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.team = :team')
            ->andWhere('m.id > :afterId')
            ->setParameter('team', $team)
            ->setParameter('afterId', $afterId)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
