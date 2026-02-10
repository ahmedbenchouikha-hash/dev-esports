<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Player>
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function findBySearchTerm(string $searchTerm): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.nickname LIKE :searchTerm')
            ->orWhere('p.firstName LIKE :searchTerm')
            ->orWhere('p.lastName LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('p.nickname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByTeam(Team $team): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.team = :team')
            ->setParameter('team', $team)
            ->orderBy('p.nickname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrdered(string $orderBy = 'nickname'): array
    {
        $validOrderBy = ['nickname', 'firstName', 'lastName', 'createdAt'];
        $orderBy = in_array($orderBy, $validOrderBy) ? $orderBy : 'nickname';

        return $this->createQueryBuilder('p')
            ->orderBy('p.' . $orderBy, 'ASC')
            ->getQuery()
            ->getResult();
    }
}
