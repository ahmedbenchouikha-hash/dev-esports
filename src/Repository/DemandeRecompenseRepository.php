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
}
