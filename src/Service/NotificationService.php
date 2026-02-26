<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $router
    ) {}

    public function createRegistrationApprovedNotification(User $user, string $tournamentName, int $tournamentId): void
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType('registration_approved');
        $notification->setMessage("Great news! Your team's registration for '{$tournamentName}' has been APPROVED! Get ready to compete!");
        $notification->setLink($this->router->generate('tournament_show', ['id' => $tournamentId]));
        $notification->setIsRead(false);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    public function createRegistrationRejectedNotification(User $user, string $tournamentName, int $tournamentId, ?string $reason = null): void
    {
        $message = "Your team's registration for '{$tournamentName}' has been rejected.";
        if ($reason) {
            $message .= " Reason: {$reason}";
        }

        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType('registration_rejected');
        $notification->setMessage($message);
        $notification->setLink($this->router->generate('tournament_show', ['id' => $tournamentId]));
        $notification->setIsRead(false);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    public function createTournamentCreatedNotification(User $user, string $tournamentName, int $tournamentId): void
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType('tournament_created');
        $notification->setMessage("A new tournament '{$tournamentName}' has been created! Check it out and register your team.");
        $notification->setLink($this->router->generate('tournament_show', ['id' => $tournamentId]));
        $notification->setIsRead(false);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}