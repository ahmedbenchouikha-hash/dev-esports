<?php

namespace App\Service;

use App\Entity\DemandeRecompense;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class EmailService
{
    private const FROM_EMAIL = 'ramzi.benhmida@esprit.tn';
    private const FROM_NAME = 'RankUp Esports';

    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function send(string $to, string $subject, string $htmlContent): void
    {
        try {
            $email = (new Email())
                ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
                ->to($to)
                ->subject($subject)
                ->html($htmlContent);

            $this->mailer->send($email);
        } catch (\Throwable $e) {
            error_log('Email send error: ' . $e->getMessage());
        }
    }

    public function sendVerificationEmail(DemandeRecompense $demande): bool
    {
        try {
            error_log("===== EmailService::sendVerificationEmail =====");
            error_log("Demande ID: " . $demande->getId());
            error_log("Demande Email: " . $demande->getEmail());
            error_log("Verification Token: " . $demande->getVerificationToken());
            
            $verificationUrl = $this->urlGenerator->generate(
                'demande_recompense_verify_email',
                [
                    'id' => $demande->getId(),
                    'token' => $demande->getVerificationToken(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            error_log("Verification URL: " . $verificationUrl);

            $email = (new Email())
                ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
                ->to((string) $demande->getEmail())
                ->subject('Verification email - Demande de recompense')
                ->html($this->twig->render('demande_recompense/email/verification.html.twig', [
                    'demande' => $demande,
                    'verificationUrl' => $verificationUrl,
                ]));

            error_log("Sending email from: " . self::FROM_EMAIL . " to: " . $demande->getEmail());
            $this->mailer->send($email);
            error_log("Verification email SENT successfully");
            return true;
        } catch (\Throwable $e) {
            error_log('Verification email ERROR: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    public function sendConfirmationEmail(DemandeRecompense $demande): bool
    {
        try {
            error_log("===== EmailService::sendConfirmationEmail =====");
            error_log("Demande ID: " . $demande->getId());
            error_log("Demande Email: " . $demande->getEmail());
            
            $email = (new Email())
                ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
                ->to((string) $demande->getEmail())
                ->subject('Confirmation de demande de recompense')
                ->html($this->twig->render('demande_recompense/email/confirmation.html.twig', [
                    'demande' => $demande,
                ]));

            error_log("Sending email from: " . self::FROM_EMAIL . " to: " . $demande->getEmail());
            $this->mailer->send($email);
            error_log("Confirmation email SENT successfully");
            return true;
        } catch (\Throwable $e) {
            error_log('Confirmation email ERROR: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    public function sendStatusChangeEmail(DemandeRecompense $demande, string $newStatut): bool
    {
        try {
            $statusMessages = [
                'approuvee' => 'Votre demande a ete approuvee.',
                'rejetee' => 'Votre demande a ete rejetee.',
                'en_attente' => 'Votre demande est en attente.',
            ];

            $email = (new Email())
                ->from(new Address(self::FROM_EMAIL, self::FROM_NAME))
                ->to((string) $demande->getEmail())
                ->subject('Mise a jour du statut de votre demande')
                ->html($this->twig->render('demande_recompense/email/status_change.html.twig', [
                    'demande' => $demande,
                    'newStatut' => $newStatut,
                    'message' => $statusMessages[$newStatut] ?? 'Votre demande a ete mise a jour.',
                ]));

            $this->mailer->send($email);
            return true;
        } catch (\Throwable $e) {
            error_log('Status email error: ' . $e->getMessage());
            return false;
        }
    }
}
