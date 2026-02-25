<?php

namespace App\Controller;

use App\Entity\TournamentRegistration;
use App\Entity\User;
use App\Repository\TournamentRegistrationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/notifications')]
#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    private const READ_SESSION_KEY_PREFIX = 'read_registration_notifications_user_';

    #[Route('', name: 'app_notifications')]
    public function index(TournamentRegistrationRepository $registrationRepository, Request $request): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $registrations = $registrationRepository->findBy(
            ['player' => $user],
            ['updatedAt' => 'DESC'],
            50
        );

        $readIds = $this->getReadIds($request, $user);
        $notifications = [];

        foreach ($registrations as $registration) {
            $notifications[] = $this->mapRegistrationToNotification($registration, $readIds);
        }

        $unreadCount = count(array_filter($notifications, static fn(array $item): bool => !$item['isRead']));

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    #[Route('/unread-count', name: 'app_notifications_unread_count', methods: ['GET'])]
    public function unreadCount(TournamentRegistrationRepository $registrationRepository, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['count' => 0]);
        }

        $registrations = $registrationRepository->findBy(['player' => $user]);
        $readIds = $this->getReadIds($request, $user);
        $count = 0;

        foreach ($registrations as $registration) {
            if (!in_array($registration->getId(), $readIds, true)) {
                $count++;
            }
        }

        return $this->json(['count' => $count]);
    }

    #[Route('/recent', name: 'app_notifications_recent', methods: ['GET'])]
    public function recent(TournamentRegistrationRepository $registrationRepository, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['notifications' => []]);
        }

        $registrations = $registrationRepository->findBy(
            ['player' => $user],
            ['updatedAt' => 'DESC'],
            5
        );
        $readIds = $this->getReadIds($request, $user);

        $data = [];
        foreach ($registrations as $registration) {
            $notification = $this->mapRegistrationToNotification($registration, $readIds);

            $data[] = [
                'id' => $notification['id'],
                'type' => $notification['type'],
                'message' => $notification['message'],
                'link' => $notification['link'],
                'isRead' => $notification['isRead'],
                'time' => $notification['createdAt']->format('M d, H:i'),
                'icon' => $notification['iconClass'],
            ];
        }

        return $this->json(['notifications' => $data]);
    }

    #[Route('/mark-read/{id}', name: 'app_notifications_mark_read', methods: ['POST'])]
    public function markRead(int $id, TournamentRegistrationRepository $registrationRepository, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $registration = $registrationRepository->find($id);

        if (!$registration || $registration->getPlayer()?->getId() !== $user->getId()) {
            return $this->json(['error' => 'Notification not found'], 404);
        }

        $readIds = $this->getReadIds($request, $user);
        if (!in_array($id, $readIds, true)) {
            $readIds[] = $id;
        }
        $this->setReadIds($request, $user, $readIds);

        return $this->json(['success' => true]);
    }

    #[Route('/mark-all-read', name: 'app_notifications_mark_all_read', methods: ['POST'])]
    public function markAllRead(TournamentRegistrationRepository $registrationRepository, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $registrations = $registrationRepository->findBy(['player' => $user], ['updatedAt' => 'DESC']);
        $ids = array_map(static fn(TournamentRegistration $item): int => (int) $item->getId(), $registrations);
        $this->setReadIds($request, $user, array_values(array_unique($ids)));

        return $this->json(['success' => true]);
    }

    private function mapRegistrationToNotification(TournamentRegistration $registration, array $readIds): array
    {
        $status = (string) $registration->getStatus();
        $tournamentName = $registration->getTournament()?->getName() ?? 'Tournament';
        $tournamentId = $registration->getTournament()?->getId();

        $message = match ($status) {
            'approved' => sprintf("Good news: your registration for '%s' was approved.", $tournamentName),
            'rejected' => sprintf(
                "Update: your registration for '%s' was rejected.%s",
                $tournamentName,
                $registration->getAdminNotes() ? ' Reason: ' . $registration->getAdminNotes() : ''
            ),
            default => sprintf("Your registration for '%s' is pending review.", $tournamentName),
        };

        $iconClass = match ($status) {
            'approved' => 'fas fa-check-circle text-success',
            'rejected' => 'fas fa-times-circle text-danger',
            default => 'fas fa-hourglass-half text-warning',
        };

        $updatedAt = $registration->getUpdatedAt() ?? $registration->getCreatedAt() ?? new \DateTimeImmutable();
        $id = (int) $registration->getId();

        return [
            'id' => $id,
            'type' => 'registration_' . $status,
            'message' => $message,
            'link' => $tournamentId ? $this->generateUrl('tournament_show', ['id' => $tournamentId], UrlGeneratorInterface::ABSOLUTE_PATH) : null,
            'isRead' => in_array($id, $readIds, true),
            'createdAt' => $updatedAt,
            'iconClass' => $iconClass,
        ];
    }

    private function getReadIds(Request $request, User $user): array
    {
        $session = $request->getSession();
        $raw = $session->get($this->getReadSessionKey($user), []);

        if (!is_array($raw)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $raw)));
    }

    private function setReadIds(Request $request, User $user, array $ids): void
    {
        $request->getSession()->set($this->getReadSessionKey($user), array_values(array_unique(array_map('intval', $ids))));
    }

    private function getReadSessionKey(User $user): string
    {
        return self::READ_SESSION_KEY_PREFIX . $user->getId();
    }
}
