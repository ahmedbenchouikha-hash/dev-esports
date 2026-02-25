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
     * Search and sort demandes
     */
    public function searchAndSort(?string $search = null, ?string $sort = null, ?string $statut = null, ?string $userEmail = null): array
    {
        $qb = $this->createQueryBuilder('d');

        if ($search) {
            $orConditions = $qb->expr()->orX(
                'd.nomDemandeur LIKE :search',
                'd.email LIKE :search',
                'd.motif LIKE :search'
            );
            $qb->andWhere($orConditions)
                ->setParameter('search', '%' . $search . '%');
        }

        if ($userEmail) {
            $qb->andWhere('(d.createdByEmail = :userEmail OR (d.createdByEmail IS NULL AND d.email = :userEmail))')
                ->setParameter('userEmail', $userEmail);
        }

        if ($statut) {
            $qb->andWhere('d.statut = :statut')
                ->setParameter('statut', $statut);
        }

        if ($sort === 'date_asc') {
            $qb->orderBy('d.dateDemande', 'ASC');
        } elseif ($sort === 'prioritaire') {
            $qb->orderBy('d.isPrioritaire', 'DESC')
                ->addOrderBy('d.dateDemande', 'DESC');
        } else {
            $qb->orderBy('d.dateDemande', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Count demandes by email
     */
    public function countByEmail(string $email): int
    {
        return (int) $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('(d.createdByEmail = :email OR (d.createdByEmail IS NULL AND d.email = :email))')
            ->setParameter('email', $email)
            ->getQuery()
            ->getSingleScalarResult();
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
     * Create a Query for pagination with search and sort
     */
    public function createSearchAndSortQuery(?string $search = null, ?string $sort = null, ?string $statut = null, ?string $userEmail = null)
    {
        $qb = $this->createQueryBuilder('d');

        if ($search) {
            $orConditions = $qb->expr()->orX(
                'd.nomDemandeur LIKE :search',
                'd.email LIKE :search',
                'd.motif LIKE :search'
            );
            $qb->andWhere($orConditions)
                ->setParameter('search', '%' . $search . '%');
        }

        if ($userEmail) {
            $qb->andWhere('(d.createdByEmail = :userEmail OR (d.createdByEmail IS NULL AND d.email = :userEmail))')
                ->setParameter('userEmail', $userEmail);
        }

        if ($statut) {
            $qb->andWhere('d.statut = :statut')
                ->setParameter('statut', $statut);
        }

        if ($sort === 'date_asc') {
            $qb->orderBy('d.dateDemande', 'ASC');
        } elseif ($sort === 'prioritaire') {
            $qb->orderBy('d.isPrioritaire', 'DESC')
                ->addOrderBy('d.dateDemande', 'DESC');
        } else {
            $qb->orderBy('d.dateDemande', 'DESC');
        }

        return $qb->getQuery();
    }
}
