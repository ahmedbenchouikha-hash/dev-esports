<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Tournament;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.team1', 't1')
            ->addSelect('t1')
            ->leftJoin('g.team2', 't2')
            ->addSelect('t2')
            ->leftJoin('g.tournament', 'tr')
            ->addSelect('tr')
            ->where('g.status = :status')
            ->setParameter('status', $status)
            ->orderBy('g.matchdate', 'DESC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function findByTournament(Tournament $tournament): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.team1', 't1')
            ->addSelect('t1')
            ->leftJoin('g.team2', 't2')
            ->addSelect('t2')
            ->where('g.tournament = :tournament')
            ->setParameter('tournament', $tournament)
            ->orderBy('g.matchdate', 'DESC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.team1', 't1')
            ->addSelect('t1')
            ->leftJoin('g.team2', 't2')
            ->addSelect('t2')
            ->leftJoin('g.tournament', 'tr')
            ->addSelect('tr')
            ->where('t1.name LIKE :searchTerm')
            ->orWhere('t2.name LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('g.matchdate', 'DESC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function findAllOrdered(string $orderBy = 'matchdate'): array
    {
        $validOrderBy = ['matchdate', 'status', 'createdAt'];
        $orderBy = in_array($orderBy, $validOrderBy) ? $orderBy : 'matchdate';

        return $this->createQueryBuilder('g')
            ->leftJoin('g.team1', 't1')
            ->addSelect('t1')
            ->leftJoin('g.team2', 't2')
            ->addSelect('t2')
            ->leftJoin('g.tournament', 'tr')
            ->addSelect('tr')
            ->orderBy('g.' . $orderBy, 'DESC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function findUpcoming(): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.team1', 't1')
            ->addSelect('t1')
            ->leftJoin('g.team2', 't2')
            ->addSelect('t2')
            ->leftJoin('g.tournament', 'tr')
            ->addSelect('tr')
            ->where('g.status IN (:statuses)')
            ->andWhere('g.matchdate > :now')
            ->setParameter('statuses', ['pending', 'ongoing'])
            ->setParameter('now', new \DateTime())
            ->orderBy('g.matchdate', 'ASC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }
}
