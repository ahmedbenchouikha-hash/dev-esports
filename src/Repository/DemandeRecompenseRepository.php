<?php

namespace App\Repository;

use App\Entity\DemandeRecompense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandeRecompense>
 */
class DemandeRecompenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeRecompense::class);
    }

    /**
     * Find all demandes ordered by date (most recent first)
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.dateDemande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find demandes by status
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('d.dateDemande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find demandes by recompense ID
     */
    public function findByRecompenseId(int $recompenseId): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.recompense = :recompenseId')
            ->setParameter('recompenseId', $recompenseId)
            ->orderBy('d.dateDemande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count demandes by email
     */
    public function countByEmail(string $email): int
    {
        return (int) $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Create a query builder for searching and sorting demandes
     */
    public function createSearchAndSortQuery(?string $search = null, ?string $sort = null, ?string $statut = null, ?string $userEmail = null)
    {
        $qb = $this->createQueryBuilder('d');
        $qb->select('d', 'r')
            ->leftJoin('d.recompense', 'r');

        // Filter by user email if player
        if ($userEmail) {
            $qb->andWhere('d.email = :userEmail')
                ->setParameter('userEmail', $userEmail);
        }

        // Filter by search term (name, email, motif)
        if ($search) {
            $qb->andWhere('d.nomDemandeur LIKE :search OR d.email LIKE :search OR d.motif LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Filter by status
        if ($statut) {
            $qb->andWhere('d.statut = :statut')
                ->setParameter('statut', $statut);
        }

        // Sort
        if ($sort === 'date_asc') {
            $qb->orderBy('d.dateDemande', 'ASC');
        } elseif ($sort === 'prioritaire') {
            $qb->orderBy('d.isPrioritaire', 'DESC')
                ->addOrderBy('d.dateDemande', 'DESC');
        } else {
            // Default: most recent
            $qb->orderBy('d.dateDemande', 'DESC');
        }

        return $qb;
    }

    /**
     * Search and sort demandes with filters
     */
    public function searchAndSort(?string $search = null, ?string $sort = null, ?string $statut = null, ?string $userEmail = null): array
    {
        return $this->createSearchAndSortQuery($search, $sort, $statut, $userEmail)
            ->getQuery()
            ->getResult();
    }
}
