<?php

namespace App\Repository;

use App\Entity\Depense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Depense>
 */
class DepenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Depense::class);
    }

    /**
     * Find depenses by status
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('d.date_creation', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find depenses by category
     */
    public function findByCategorie(string $categorie): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->orderBy('d.date_creation', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find depenses by team
     */
    public function findByTeam(int $teamId): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.team = :teamId')
            ->setParameter('teamId', $teamId)
            ->orderBy('d.date_creation', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find depenses with advanced search
     */
    public function searchAdvanced(string $search = '', string $statut = 'all', string $categorie = 'all', string $minAmount = '', string $maxAmount = ''): array
    {
        $qb = $this->createQueryBuilder('d');

        if (!empty($search)) {
            $qb->andWhere('d.titre LIKE :search OR d.team.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($statut !== 'all') {
            $qb->andWhere('d.statut = :statut')
                ->setParameter('statut', $statut);
        }

        if ($categorie !== 'all') {
            $qb->andWhere('d.categorie = :categorie')
                ->setParameter('categorie', $categorie);
        }

        if (!empty($minAmount) && is_numeric($minAmount)) {
            $qb->andWhere('d.montant >= :minAmount')
                ->setParameter('minAmount', (float)$minAmount);
        }

        if (!empty($maxAmount) && is_numeric($maxAmount)) {
            $qb->andWhere('d.montant <= :maxAmount')
                ->setParameter('maxAmount', (float)$maxAmount);
        }

        return $qb->orderBy('d.date_creation', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get total amount of depenses by status
     */
    public function getTotalByStatut(string $statut): float
    {
        $result = $this->createQueryBuilder('d')
            ->select('SUM(d.montant) as total')
            ->andWhere('d.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result['total'] ?? 0;
    }

    /**
     * Count depenses by status
     */
    public function countByStatut(string $statut): int
    {
        return $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->andWhere('d.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
