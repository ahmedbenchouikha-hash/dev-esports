<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function countAll(): int
    {
        return $this->createQueryBuilder('t')
            ->select('NEW App\DTO\EntityCountDTO(COUNT(t.id))')
            ->getQuery()
            ->getSingleResult()->count;
    }

    /**
     * Search teams by multiple criteria
     */
    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.players', 'p')
            ->addSelect('p')
            ->leftJoin('t.creator', 'c')
            ->addSelect('c')
            ->where('t.name LIKE :searchTerm')
            ->orWhere('t.country LIKE :searchTerm')
            ->orWhere('t.jeu LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('t.name', 'ASC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all teams ordered by a specific field
     */
    public function findAllOrdered(string $orderBy = 'name', string $direction = 'ASC'): array
    {
        $validOrderBy = ['name', 'createdAt', 'score', 'jeu', 'niveau', 'country'];
        $orderBy = in_array($orderBy, $validOrderBy) ? $orderBy : 'name';
        $direction = in_array(strtoupper($direction), ['ASC', 'DESC']) ? strtoupper($direction) : 'ASC';

        return $this->createQueryBuilder('t')
            ->leftJoin('t.players', 'p')
            ->addSelect('p')
            ->leftJoin('t.creator', 'c')
            ->addSelect('c')
            ->orderBy('t.' . $orderBy, $direction)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * Search with advanced filters
     */
    public function searchAdvanced(
        ?string $searchTerm = null,
        ?string $game = null,
        ?string $level = null,
        ?string $status = null,
        ?string $country = null,
        string $orderBy = 'name',
        string $direction = 'ASC'
    ): array {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.players', 'p')
            ->addSelect('p')
            ->leftJoin('t.creator', 'c')
            ->addSelect('c');

        if ($searchTerm) {
            $qb->andWhere('t.name LIKE :searchTerm OR t.description LIKE :searchTerm OR t.country LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        if ($game) {
            $qb->andWhere('t.jeu = :game')
                ->setParameter('game', $game);
        }

        if ($level) {
            $qb->andWhere('t.niveau = :level')
                ->setParameter('level', $level);
        }

        if ($status) {
            $qb->andWhere('t.statut = :status')
                ->setParameter('status', $status);
        }

        if ($country) {
            $qb->andWhere('t.country = :country')
                ->setParameter('country', $country);
        }

        $validOrderBy = ['name', 'createdAt', 'score', 'jeu', 'niveau', 'country'];
        $orderBy = in_array($orderBy, $validOrderBy) ? $orderBy : 'name';
        $direction = in_array(strtoupper($direction), ['ASC', 'DESC']) ? strtoupper($direction) : 'ASC';

        $qb->orderBy('t.' . $orderBy, $direction)
            ->distinct();

        return $qb->getQuery()->getResult();
    }

    /**
     * Find teams by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.players', 'p')
            ->addSelect('p')
            ->leftJoin('t.creator', 'c')
            ->addSelect('c')
            ->where('t.statut = :status')
            ->setParameter('status', $status)
            ->orderBy('t.name', 'ASC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * Find teams by game
     */
    public function findByGame(string $game): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.players', 'p')
            ->addSelect('p')
            ->leftJoin('t.creator', 'c')
            ->addSelect('c')
            ->where('t.jeu = :game')
            ->setParameter('game', $game)
            ->orderBy('t.name', 'ASC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * Find teams by level
     */
    public function findByLevel(string $level): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.players', 'p')
            ->addSelect('p')
            ->leftJoin('t.creator', 'c')
            ->addSelect('c')
            ->where('t.niveau = :level')
            ->setParameter('level', $level)
            ->orderBy('t.name', 'ASC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * Find top teams by score
     */
    public function findTopByScore(int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.players', 'p')
            ->addSelect('p')
            ->leftJoin('t.creator', 'c')
            ->addSelect('c')
            ->orderBy('t.score', 'DESC')
            ->setMaxResults($limit)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * Count teams by status
     */
    public function countByStatus(string $status): int
    {
        return (int)$this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.statut = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find paginated results
     */
    public function findPaginated(int $page = 1, int $limit = 10, string $orderBy = 'name', string $direction = 'ASC'): array
    {
        $offset = ($page - 1) * $limit;
        $validOrderBy = ['name', 'createdAt', 'score', 'jeu', 'niveau', 'country'];
        $orderBy = in_array($orderBy, $validOrderBy) ? $orderBy : 'name';
        $direction = in_array(strtoupper($direction), ['ASC', 'DESC']) ? strtoupper($direction) : 'ASC';

        return $this->createQueryBuilder('t')
            ->leftJoin('t.players', 'p')
            ->addSelect('p')
            ->leftJoin('t.creator', 'c')
            ->addSelect('c')
            ->orderBy('t.' . $orderBy, $direction)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /**
     * Find teams with available slots
     */
    public function findTeamsWithAvailableSlots()
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.members', 'm')
            ->groupBy('t.id')
            ->having('COUNT(m) < 5')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
