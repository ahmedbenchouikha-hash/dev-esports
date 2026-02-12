<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send(string $to, string $subject, string $htmlContent): void
    {
        try {
            $email = (new Email())
                ->from('no-reply@dev-esports.com')
                ->to($to)
                ->subject($subject)
                ->html($htmlContent);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log or handle error silently for now
        }
    }
}
