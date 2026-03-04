<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\Tournament;
use App\Entity\TournamentRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TournamentRegistration>
 *
 * @method TournamentRegistration|null find($id, $lockMode = null, $lockVersion = null)
 * @method TournamentRegistration|null findOneBy(array $criteria, array $orderBy = null)
 * @method TournamentRegistration[]    findAll()
 * @method TournamentRegistration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournamentRegistration::class);
    }

    public function save(TournamentRegistration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TournamentRegistration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByTeamAndTournament(Team $team, Tournament $tournament): ?TournamentRegistration
    {
        return $this->findOneBy([
            'team' => $team,
            'tournament' => $tournament,
        ]);
    }

    public function findByTeam(Team $team): array
    {
        return $this->findBy(
            ['team' => $team],
            ['createdAt' => 'DESC']
        );
    }

    public function findByTournament(Tournament $tournament): array
    {
        return $this->findBy(
            ['tournament' => $tournament],
            ['createdAt' => 'DESC']
        );
    }

    public function findPendingByTournament(Tournament $tournament): array
    {
        return $this->findBy(
            ['tournament' => $tournament, 'status' => 'pending'],
            ['createdAt' => 'DESC']
        );
    }

    public function findApprovedByTournament(Tournament $tournament): array
    {
        return $this->findBy(
            ['tournament' => $tournament, 'status' => 'approved'],
            ['createdAt' => 'DESC']
        );
    }
}
