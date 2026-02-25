<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Find unread notifications for a user
     */
    public function findUnreadByUser(User $user): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->andWhere('n.isRead = :isRead')
            ->setParameter('user', $user)
            ->setParameter('isRead', false)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count unread notifications for a user
     */
    public function countUnreadByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.user = :user')
            ->andWhere('n.isRead = :isRead')
            ->setParameter('user', $user)
            ->setParameter('isRead', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find recent notifications for a user
     */
    public function findRecentByUser(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', ':isRead')
            ->set('n.readAt', ':readAt')
            ->andWhere('n.user = :user')
            ->andWhere('n.isRead = :false')
            ->setParameter('isRead', true)
            ->setParameter('readAt', new \DateTimeImmutable())
            ->setParameter('user', $user)
            ->setParameter('false', false)
            ->getQuery()
            ->execute();
    }
}