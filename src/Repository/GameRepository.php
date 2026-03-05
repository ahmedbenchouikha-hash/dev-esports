<?php

namespace App\Repository;

use App\DTO\EntityCountDTO;
use App\DTO\GameStatusCountDTO;
use App\DTO\TournamentMatchCountDTO;
use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    public function countAll(): int
    {
        return $this->createQueryBuilder('g')
            ->select('NEW App\DTO\EntityCountDTO(COUNT(g.id))')
            ->getQuery()
            ->getSingleResult()->count;
    }

    public function countByStatus(string $status): int
    {
        return $this->createQueryBuilder('g')
            ->select('NEW App\DTO\EntityCountDTO(COUNT(g.id))')
            ->where('g.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleResult()->count;
    }

    /**
     * @return GameStatusCountDTO[]
     */
    public function countAllByStatus(): array
    {
        return $this->createQueryBuilder('g')
            ->select('NEW App\DTO\GameStatusCountDTO(g.status, COUNT(g.id))')
            ->groupBy('g.status')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }

    public function findRecentWithTeams(int $limit = 6): array
    {
        // Step 1: Get IDs with LIMIT (no joins = no collection join issue)
        $ids = $this->createQueryBuilder('g')
            ->select('g.id')
            ->orderBy('g.matchdate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getSingleColumnResult();

        if (empty($ids)) {
            return [];
        }

        // Step 2: Load full entities with joins, ordered by ID position from step 1
        $games = $this->createQueryBuilder('g')
            ->leftJoin('g.team1', 't1')
            ->addSelect('t1')
            ->leftJoin('g.team2', 't2')
            ->addSelect('t2')
            ->leftJoin('g.tournament', 'tr')
            ->addSelect('tr')
            ->where('g.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        // Sort to match the order from step 1 (by matchdate DESC)
        $idOrder = array_flip($ids);
        usort($games, fn($a, $b) => ($idOrder[$a->getId()] ?? 0) - ($idOrder[$b->getId()] ?? 0));

        return $games;
    }

    /**
     * @return \App\DTO\TeamWinsDTO[]
     */
    public function getTopTeamsByWins(int $limit = 5): array
    {
        $em = $this->getEntityManager();

        // Team1 wins (score1 > score2)
        $wins1 = $em->createQuery(
            'SELECT t1.name AS name FROM App\Entity\Game g JOIN g.team1 t1 WHERE g.status = :status AND g.score1 > g.score2'
        )->setParameter('status', 'finished')->getArrayResult();

        // Team2 wins (score2 > score1)
        $wins2 = $em->createQuery(
            'SELECT t2.name AS name FROM App\Entity\Game g JOIN g.team2 t2 WHERE g.status = :status AND g.score2 > g.score1'
        )->setParameter('status', 'finished')->getArrayResult();

        $counts = [];
        foreach (array_merge($wins1, $wins2) as $row) {
            $name = $row['name'];
            $counts[$name] = ($counts[$name] ?? 0) + 1;
        }
        arsort($counts);

        $result = [];
        foreach (array_slice($counts, 0, $limit, true) as $name => $wins) {
            $result[] = new \App\DTO\TeamWinsDTO($name, $wins);
        }
        return $result;
    }

    public function getTopTeamsByWinsForChart(int $limit = 8): array
    {
        return $this->getTopTeamsByWins($limit);
    }

    /**
     * @return TournamentMatchCountDTO[]
     */
    public function countMatchesByTournament(): array
    {
        return $this->createQueryBuilder('g')
            ->select('NEW App\DTO\TournamentMatchCountDTO(tr.name, COUNT(g.id))')
            ->join('g.tournament', 'tr')
            ->groupBy('tr.id, tr.name')
            ->having('COUNT(g.id) > 0')
            ->getQuery()
            ->getResult();
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
            ->setMaxResults(100)
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
            ->setMaxResults(100)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function findAllOrdered(string $orderBy = 'matchdate'): array
    {
        $orderByMap = [
            'matchdate' => 'g.matchdate',
            'status' => 'g.status',
            'createdAt' => 'g.createdAt',
        ];
        $orderByField = $orderByMap[$orderBy] ?? $orderByMap['matchdate'];

        return $this->createQueryBuilder('g')
            ->leftJoin('g.team1', 't1')
            ->addSelect('t1')
            ->leftJoin('g.team2', 't2')
            ->addSelect('t2')
            ->leftJoin('g.tournament', 'tr')
            ->addSelect('tr')
            ->orderBy($orderByField, 'DESC')
            ->setMaxResults(100)
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
