<?php

namespace App\Repository;

use App\Entity\Budget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Budget>
 */
class BudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Budget::class);
    }

    /**
     * Find budgets by status
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('b.dateAllocation', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find budgets by team
     */
    public function findByTeam(int $teamId): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.team = :teamId')
            ->setParameter('teamId', $teamId)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get total allocated amount
     */
    public function getTotalMontantAlloue(): float
    {
        $result = $this->createQueryBuilder('b')
            ->select('SUM(b.montantAlloue) as total')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result['total'] ?? 0;
    }

    /**
     * Get total used amount
     */
    public function getTotalMontantUtilise(): float
    {
        $result = $this->createQueryBuilder('b')
            ->select('SUM(b.montantUtilise) as total')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result['total'] ?? 0;
    }

    /**
     * Find budgets exceeding their allocation
     */
    public function findBudgetsExceeding(): array
    {
        $budgets = $this->findAll();
        $exceeding = [];

        foreach ($budgets as $budget) {
            if ($budget->isDepassement()) {
                $exceeding[] = $budget;
            }
        }

        return $exceeding;
    }

    /**
     * Count budgets by status
     */
    public function countByStatut(string $statut): int
    {
        return $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->andWhere('b.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
