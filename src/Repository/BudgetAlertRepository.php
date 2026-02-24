<?php

namespace App\Repository;

use App\Entity\BudgetAlert;
use App\Entity\Budget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;

/**
 * @extends ServiceEntityRepository<BudgetAlert>
 */
class BudgetAlertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetAlert::class);
    }

    /**
     * Find recent alerts for a budget (within last hour)
     */
    public function findRecentAlerts(Budget $budget, string $type): ?BudgetAlert
    {
        $oneHourAgo = new DateTime('-1 hour');
        
        return $this->createQueryBuilder('ba')
            ->where('ba.budget = :budget')
            ->andWhere('ba.type = :type')
            ->andWhere('ba.sentAt > :oneHourAgo')
            ->setParameter('budget', $budget)
            ->setParameter('type', $type)
            ->setParameter('oneHourAgo', $oneHourAgo)
            ->orderBy('ba.sentAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all alerts for a budget
     */
    public function findByBudget(Budget $budget)
    {
        return $this->createQueryBuilder('ba')
            ->where('ba.budget = :budget')
            ->setParameter('budget', $budget)
            ->orderBy('ba.sentAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find alerts not yet sent to manager
     */
    public function findUnsentManagerAlerts()
    {
        return $this->createQueryBuilder('ba')
            ->where('ba.managerNotificationSentAt IS NULL')
            ->orderBy('ba.sentAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find alerts not yet sent to admin
     */
    public function findUnsentAdminAlerts()
    {
        return $this->createQueryBuilder('ba')
            ->where('ba.adminNotificationSentAt IS NULL')
            ->andWhere('ba.type IN (:types)')
            ->setParameter('types', ['high_threshold', 'critical'])
            ->orderBy('ba.sentAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
