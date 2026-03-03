<?php

namespace App\Service;

use App\Entity\Player;
use App\Entity\Team;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TeamInvitationEmailService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EmailService $emailService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function sendInvitationEmail(Player $player, Team $team, Player $manager, ?string $dashboardUrl = null): bool
    {
        $toEmail = $player->getEmail();

        if (!$toEmail) {
            $this->logger->warning('Team invitation email skipped: invited player has no email.', [
                'player_id' => $player->getId(),
                'team_id' => $team->getId(),
            ]);

            return false;
        }

        $managerName = $manager->getNickname() ?: $manager->getEmail() ?: 'Team Manager';
        $playerName = $player->getNickname() ?: 'Player';
        $teamName = $team->getName() ?: 'Unknown Team';

        $subject = sprintf('You are invited to join %s', $teamName);
        $html = $this->buildHtmlMessage($playerName, $team, $managerName, $dashboardUrl);

        $brevoApiKey = trim((string) (getenv('BREVO_API_KEY') ?: ($_ENV['BREVO_API_KEY'] ?? '')));
        $senderEmail = (string) (getenv('BREVO_SENDER_EMAIL') ?: ($_ENV['BREVO_SENDER_EMAIL'] ?? 'no-reply@pidev.local'));
        $senderName = (string) (getenv('BREVO_SENDER_NAME') ?: ($_ENV['BREVO_SENDER_NAME'] ?? 'PiDev Esports'));

        if ($brevoApiKey === '') {
            return $this->emailService->send($toEmail, $subject, $html);
        }

        try {
            $response = $this->httpClient->request('POST', 'https://api.brevo.com/v3/smtp/email', [
                'headers' => [
                    'accept' => 'application/json',
                    'api-key' => $brevoApiKey,
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'sender' => [
                        'name' => $senderName,
                        'email' => $senderEmail,
                    ],
                    'to' => [
                        ['email' => $toEmail, 'name' => $playerName],
                    ],
                    'subject' => $subject,
                    'htmlContent' => $html,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode >= 400) {
                throw new \RuntimeException('Brevo API returned HTTP ' . $statusCode . '.');
            }

            $this->logger->info('Team invitation email sent.', [
                'player_id' => $player->getId(),
                'team_id' => $team->getId(),
                'email' => $toEmail,
            ]);

            return true;
        } catch (\Throwable $exception) {
            $this->logger->error('Brevo invitation email failed. Falling back to EmailService.', [
                'error' => $exception->getMessage(),
                'player_id' => $player->getId(),
                'team_id' => $team->getId(),
            ]);

            return $this->emailService->send($toEmail, $subject, $html);
        }
    }

    private function buildHtmlMessage(string $playerName, Team $team, string $managerName, ?string $dashboardUrl): string
    {
        $teamName = $team->getName() ?: 'Unknown Team';
        $country = $team->getCountry() ?: 'Not specified';
        $game = $team->getJeu() ?: 'Not specified';
        $level = $team->getNiveau() ?: 'Not specified';

        $safePlayerName = htmlspecialchars($playerName, ENT_QUOTES);
        $safeTeamName = htmlspecialchars($teamName, ENT_QUOTES);
        $safeManagerName = htmlspecialchars($managerName, ENT_QUOTES);
        $safeGame = htmlspecialchars($game, ENT_QUOTES);
        $safeLevel = htmlspecialchars($level, ENT_QUOTES);
        $safeCountry = htmlspecialchars($country, ENT_QUOTES);
        $safeDashboardUrl = $dashboardUrl ? htmlspecialchars($dashboardUrl, ENT_QUOTES) : null;

        $cta = $safeDashboardUrl
            ? '<p style="margin-top:20px;"><a href="' . $safeDashboardUrl . '" style="display:inline-block;padding:10px 18px;background:#4f46e5;color:#fff;text-decoration:none;border-radius:6px;">Open Dashboard</a></p>'
            : '';

        return '<!doctype html><html><body style="font-family:Arial,sans-serif;background:#f5f7fb;padding:24px;">'
            . '<div style="max-width:620px;margin:0 auto;background:#fff;border-radius:12px;padding:24px;">'
            . '<h2 style="margin:0 0 12px;">Team Invitation 🎮</h2>'
            . '<p>Hello <strong>' . $safePlayerName . '</strong>,</p>'
            . '<p><strong>' . $safeManagerName . '</strong> invited you to join <strong>' . $safeTeamName . '</strong>.</p>'
            . '<table style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;margin:16px 0;">'
            . '<tr><td style="padding:8px;border-bottom:1px solid #e5e7eb;"><strong>Game</strong></td><td style="padding:8px;border-bottom:1px solid #e5e7eb;text-align:right;">' . $safeGame . '</td></tr>'
            . '<tr><td style="padding:8px;border-bottom:1px solid #e5e7eb;"><strong>Level</strong></td><td style="padding:8px;border-bottom:1px solid #e5e7eb;text-align:right;">' . $safeLevel . '</td></tr>'
            . '<tr><td style="padding:8px;"><strong>Country</strong></td><td style="padding:8px;text-align:right;">' . $safeCountry . '</td></tr>'
            . '</table>'
            . '<p>You can accept or reject this invitation from your player dashboard.</p>'
            . $cta
            . '<p style="margin-top:24px;color:#6b7280;font-size:13px;">Dev Esports Team</p>'
            . '</div></body></html>';
    }
}
