<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    /**
     * Find all payments for a specific ticket
     */
    public function findByTicket(int $ticketId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.ticket = :ticketId')
            ->setParameter('ticketId', $ticketId)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all succeeded payments for a ticket
     */
    public function findSucceededByTicket(int $ticketId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.ticket = :ticketId')
            ->andWhere('p.status = :status')
            ->setParameter('ticketId', $ticketId)
            ->setParameter('status', 'succeeded')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find payment by Stripe payment intent ID
     */
    public function findByPaymentIntentId(string $intentId): ?Payment
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.paymentIntentId = :intentId')
            ->setParameter('intentId', $intentId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get total revenue from succeeded payments
     */
    public function getTotalRevenue(): float
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.amount) as total')
            ->andWhere('p.status = :status')
            ->setParameter('status', 'succeeded')
            ->getQuery()
            ->getOneOrNullResult();

        return $result['total'] ?? 0;
    }

    /**
     * Get total revenue for a specific ticket
     */
    public function getTicketRevenue(int $ticketId): float
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.amount) as total')
            ->andWhere('p.ticket = :ticketId')
            ->andWhere('p.status = :status')
            ->setParameter('ticketId', $ticketId)
            ->setParameter('status', 'succeeded')
            ->getQuery()
            ->getOneOrNullResult();

        return $result['total'] ?? 0;
    }

    /**
     * Find recent payments
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find pending payments (older than 30 minutes)
     */
    public function findStalePayments(): array
    {
        $threshold = (new \DateTime())->modify('-30 minutes');

        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.createdAt < :threshold')
            ->setParameter('status', 'pending')
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getResult();
    }
}
