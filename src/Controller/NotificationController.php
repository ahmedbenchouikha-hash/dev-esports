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
<<<<<<< HEAD
    #[Route('', name: 'app_notifications', methods: ['GET'])]
    public function index(NotificationRepository $repo): Response
    {
        $notifications = $repo->findBy(['user' => $this->getUser()], ['createdAt' => 'DESC']);
        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

=======
>>>>>>> module-rewards
    #[Route('/recent', name: 'notifications_recent', methods: ['GET'])]
    public function recent(NotificationRepository $repo): Response
    {
        $items = $repo->findRecent(10);
        $data = array_map(function(Notification $n) {
            $link = null;
            if ($n->getReclamation()) {
                $link = $this->generateUrl('app_reclamation_index') . '#reclamation-' . $n->getReclamation()->getId();
            }
            return [
                'id' => $n->getId(),
                'title' => $n->getTitle(),
                'message' => $n->getMessage(),
                'isRead' => $n->isRead(),
                'createdAt' => $n->getCreatedAt()?->format('c'),
                'link' => $link,
            ];
        }, $items);

        return $this->json(['success' => true, 'items' => $data]);
    }

    #[Route('/{id}/mark-read', name: 'notification_mark_read', methods: ['POST'])]
    public function markRead(Notification $notification, EntityManagerInterface $em): Response
    {
        $notification->setIsRead(true);
        $em->flush();
        return $this->json(['success' => true]);
    }
}
