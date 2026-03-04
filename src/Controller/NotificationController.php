<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notifications')]
final class NotificationController extends AbstractController
{
    #[Route('', name: 'app_notifications', methods: ['GET'])]
    public function index(NotificationRepository $repo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view notifications');
        }

        $notifications = $repo->createQueryBuilder('n')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/unread-count', name: 'notification_unread_count', methods: ['GET'])]
    public function unreadCount(NotificationRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['count' => 0]);
        }

        $count = $repo->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.user = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json(['count' => (int)$count]);
    }

    #[Route('/recent', name: 'notification_recent', methods: ['GET'])]
    public function recent(NotificationRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['notifications' => []]);
        }

        $items = $repo->createQueryBuilder('n')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $data = array_map(function(Notification $n) {
            return [
                'id' => $n->getId(),
                'message' => $n->getMessage(),
                'read' => $n->isRead(),
                'created_at' => $n->getCreatedAt()?->format('M d, Y H:i'),
            ];
        }, $items);

        return $this->json(['notifications' => $data]);
    }

    #[Route('/mark-all-read', name: 'notification_mark_all_read', methods: ['POST'])]
    public function markAllRead(EntityManagerInterface $em, NotificationRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['success' => false]);
        }

        $repo->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', true)
            ->where('n.user = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        $em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/{id}/mark-read', name: 'notification_mark_read', methods: ['POST'])]
    public function markRead(Notification $notification, EntityManagerInterface $em): JsonResponse
    {
        $notification->setIsRead(true);
        $em->flush();
        return $this->json(['success' => true]);
    }
}
