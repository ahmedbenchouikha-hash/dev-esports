<?php

namespace App\Repository;

use App\Entity\Tournament;
use App\Entity\Team;
use App\Entity\TournamentRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TournamentRegistration>
 */
class TournamentRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournamentRegistration::class);
    }

    public function findPendingRegistrations(): array
    {
        return $this->createQueryBuilder('tr')
            ->andWhere('tr.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('tr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByTournament(Tournament $tournament): array
    {
        return $this->createQueryBuilder('tr')
            ->andWhere('tr.tournament = :tournament')
            ->setParameter('tournament', $tournament)
            ->orderBy('tr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('tr')
            ->andWhere('tr.status = :status')
            ->setParameter('status', $status)
            ->orderBy('tr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByTeamAndTournament(Team $team, Tournament $tournament): ?TournamentRegistration
    {
        return $this->createQueryBuilder('tr')
            ->andWhere('tr.team = :team')
            ->andWhere('tr.tournament = :tournament')
            ->setParameter('team', $team)
            ->setParameter('tournament', $tournament)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByTeam(Team $team): array
    {
        return $this->createQueryBuilder('tr')
            ->andWhere('tr.team = :team')
            ->setParameter('team', $team)
            ->orderBy('tr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countPendingRegistrations(): int
    {
        return (int) $this->createQueryBuilder('tr')
            ->select('COUNT(tr.id)')
            ->andWhere('tr.status = :status')
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getSingleScalarResult();
    }
}