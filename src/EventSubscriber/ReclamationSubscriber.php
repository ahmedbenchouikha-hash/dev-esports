<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsDoctrineListener(event: Events::postPersist)]
final class ReclamationSubscriber
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire('%env(RECLAMATION_NOTIFY_TO)%')]
        private readonly string $notificationEmail,
    )
    {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Reclamation) {
            return;
        }

        $email = (new Email())
            ->from($this->notificationEmail)
            ->to($this->notificationEmail)
            ->subject('Nouvelle Réclamation')
            ->text(sprintf(
                "Une nouvelle réclamation a été créée.\n\nTitre : %s\nDescription : %s",
                $entity->getTitre() ?? '',
                $entity->getDescription() ?? ''
            ));

        $this->mailer->send($email);
    }
}
