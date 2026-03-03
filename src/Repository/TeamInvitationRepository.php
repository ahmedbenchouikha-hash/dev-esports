<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\Team;
use App\Entity\TeamInvitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TeamInvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamInvitation::class);
    }

    public function findByTeamAndPlayer(Team $team, Player $player): ?TeamInvitation
    {
        return $this->findOneBy([
            'team' => $team,
            'player' => $player,
        ]);
    }

    public function findPendingInvitationForPlayer(Player $player): array
    {
        return $this->findBy([
            'player' => $player,
            'status' => 'pending',
            'type' => 'invitation',
        ], ['createdAt' => 'DESC']);
    }

    public function findTeamInvitations(Team $team, string $status = null): array
    {
        $qb = $this->createQueryBuilder('ti')
            ->where('ti.team = :team')
            ->setParameter('team', $team)
            ->orderBy('ti.createdAt', 'DESC');

        if ($status) {
            $qb->andWhere('ti.status = :status')
               ->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }

    public function findPendingRequestsForManager(Player $manager): array
    {
        return $this->createQueryBuilder('ti')
            ->join('ti.team', 't')
            ->join('t.players', 'tp')
            ->where('tp = :manager')
            ->andWhere('ti.status = :status')
            ->andWhere('ti.type = :type')
            ->setParameter('manager', $manager)
            ->setParameter('status', 'pending')
            ->setParameter('type', 'request')
            ->orderBy('ti.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findSentInvitationsForManager(Player $manager): array
    {
        return $this->createQueryBuilder('ti')
            ->join('ti.team', 't')
            ->join('t.players', 'tp')
            ->where('tp = :manager')
            ->andWhere('ti.type = :type')
            ->setParameter('manager', $manager)
            ->setParameter('type', 'invitation')
            ->orderBy('ti.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
