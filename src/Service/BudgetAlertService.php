<?php

namespace App\Service;

use App\Entity\Budget;
use App\Entity\BudgetAlert;
use App\Entity\Depense;
use App\Entity\Team;
use App\Repository\BudgetRepository;
use App\Repository\DepenseRepository;
use App\Repository\BudgetAlertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use DateTime;

class BudgetAlertService
{
    private EmailService $emailService;
    private BudgetRepository $budgetRepository;
    private DepenseRepository $depenseRepository;
    private BudgetAlertRepository $budgetAlertRepository;
    private EntityManagerInterface $entityManager;
    private MailerInterface $mailer;
    private LoggerInterface $logger;

    public function __construct(
        EmailService $emailService,
        BudgetRepository $budgetRepository,
        DepenseRepository $depenseRepository,
        BudgetAlertRepository $budgetAlertRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        LoggerInterface $logger
    ) {
        $this->emailService = $emailService;
        $this->budgetRepository = $budgetRepository;
        $this->depenseRepository = $depenseRepository;
        $this->budgetAlertRepository = $budgetAlertRepository;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    /**
     * Check budget for a specific team and send alerts if needed
     */
    public function checkBudgetAndAlert(Team $team): void
    {
        $logFile = __DIR__ . '/../../var/log/budget_alert.log';
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] BudgetAlertService: Checking budget for team {$team->getName()}\n", FILE_APPEND);
        
        $budget = $this->budgetRepository->findOneBy(['team' => $team, 'statut' => 'actif']);
        
        if (!$budget) {
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] No active budget found for team {$team->getName()}\n", FILE_APPEND);
            return;
        }
        
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Found budget for team {$team->getName()}\n", FILE_APPEND);

        // Calculate used amount
        $depenses = $this->depenseRepository->findBy([
            'team' => $team,
            'statut' => 'validée'
        ]);

        $montantUtilise = 0;
        foreach ($depenses as $depense) {
            $montantUtilise += $depense->getMontant();
        }

        $budget->setMontantUtilise($montantUtilise);
        $remainingAmount = $budget->getMontantRestant();
        $percentageUsed = $budget->getPourcentageUtilisation();

        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] BudgetAlertService: Team {$team->getName()} - Budget allocation: {$budget->getMontantAlloue()}€, Used: {$montantUtilise}€, Percentage: {$percentageUsed}%\n", FILE_APPEND);

        // Determine alert type and trigger
        if ($budget->isDepassement()) {
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] BudgetAlertService: Budget EXCEEDED for team {$team->getName()}\n", FILE_APPEND);
            $this->sendCriticalAlert($budget, $percentageUsed, $remainingAmount);
        } elseif ($percentageUsed >= 90) {
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] BudgetAlertService: 90% threshold REACHED for team {$team->getName()}\n", FILE_APPEND);
            $this->sendHighThresholdAlert($budget, $percentageUsed, $remainingAmount);
        } elseif ($percentageUsed >= 75) {
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] BudgetAlertService: 75% threshold REACHED for team {$team->getName()}\n", FILE_APPEND);
            $this->sendAttentionAlert($budget, $percentageUsed, $remainingAmount);
        } else {
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] BudgetAlertService: No alert needed for team {$team->getName()} (usage: {$percentageUsed}%)\n", FILE_APPEND);
        }
    }

    /**
     * Send critical alert (budget exceeded)
     */
    private function sendCriticalAlert(Budget $budget, float $percentageUsed, float $remainingAmount): void
    {
        $logFile = __DIR__ . '/../../var/log/budget_alert.log';
        
        // Check if alert was already sent recently
        $recentAlert = $this->budgetAlertRepository->findRecentAlerts($budget, 'critical');
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendCriticalAlert: Checking for recent alerts. Found: " . ($recentAlert ? 'YES' : 'NO') . "\n", FILE_APPEND);
        
        if ($recentAlert && !$recentAlert->getAdminNotificationSentAt()) {
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendCriticalAlert: Alert already sent recently, returning early\n", FILE_APPEND);
            return; // Already alerted in the last hour
        }

        $team = $budget->getTeam();
        $overage = abs($remainingAmount);

        // Send to approved team managers
        $managers = $this->getApprovedManagers($team);
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendCriticalAlert: Found " . count($managers) . " approved managers for team {$team->getName()}\n", FILE_APPEND);
        
        foreach ($managers as $player) {
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendCriticalAlert: Sending alert to manager {$player->getEmail()}\n", FILE_APPEND);
            $this->sendManagerAlert(
                $player->getEmail(),
                $team,
                'critical',
                $percentageUsed,
                $remainingAmount,
                $overage
            );
        }

        // Send to admins
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendCriticalAlert: Sending alert to admins\n", FILE_APPEND);
        $this->sendAdminAlert($team, 'critical', $percentageUsed, $remainingAmount);

        // Record alert and mark as sent
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendCriticalAlert: Recording alert\n", FILE_APPEND);
        $this->recordAlert($budget, 'critical', $percentageUsed, $remainingAmount);
        $this->markAlertAsManagerNotified($budget, 'critical');
        $this->markAlertAsAdminNotified($budget, 'critical');
    }

    /**
     * Send high threshold alert (90% utilization)
     */
    private function sendHighThresholdAlert(Budget $budget, float $percentageUsed, float $remainingAmount): void
    {
        $logFile = __DIR__ . '/../../var/log/budget_alert.log';
        
        // Check if alert was already sent recently
        $recentAlert = $this->budgetAlertRepository->findRecentAlerts($budget, 'high_threshold');
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendHighThresholdAlert: Checking for recent alerts. Found: " . ($recentAlert ? 'YES' : 'NO') . "\n", FILE_APPEND);
        
        if ($recentAlert && !$recentAlert->getManagerNotificationSentAt()) {
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendHighThresholdAlert: Alert already sent recently, returning early\n", FILE_APPEND);
            return; // Already alerted in the last hour
        }

        $team = $budget->getTeam();

        // Send to approved team managers
        $managers = $this->getApprovedManagers($team);
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendHighThresholdAlert: Found " . count($managers) . " approved managers for team {$team->getName()}\n", FILE_APPEND);
        
        foreach ($managers as $player) {
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendHighThresholdAlert: Sending alert to manager {$player->getEmail()}\n", FILE_APPEND);
            $this->sendManagerAlert(
                $player->getEmail(),
                $team,
                'high_threshold',
                $percentageUsed,
                $remainingAmount
            );
        }

        // Send to admins
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendHighThresholdAlert: Sending alert to admins\n", FILE_APPEND);
        $this->sendAdminAlert($team, 'high_threshold', $percentageUsed, $remainingAmount);

        // Record alert and mark as sent
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendHighThresholdAlert: Recording alert\n", FILE_APPEND);
        $this->recordAlert($budget, 'high_threshold', $percentageUsed, $remainingAmount);
        $this->markAlertAsManagerNotified($budget, 'high_threshold');
        $this->markAlertAsAdminNotified($budget, 'high_threshold');
    }

    /**
     * Send attention alert (75% utilization)
     */
    private function sendAttentionAlert(Budget $budget, float $percentageUsed, float $remainingAmount): void
    {
        $logFile = __DIR__ . '/../../var/log/budget_alert.log';
        
        // Check if alert was already sent recently (daily)
        $recentAlert = $this->budgetAlertRepository->findRecentAlerts($budget, 'attention');
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendAttentionAlert: Checking for recent alerts. Found: " . ($recentAlert ? 'YES' : 'NO') . "\n", FILE_APPEND);
        
        if ($recentAlert && !$recentAlert->getManagerNotificationSentAt()) {
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendAttentionAlert: Alert already sent recently, returning early\n", FILE_APPEND);
            return; // Already alerted
        }

        $team = $budget->getTeam();

        // Send to approved team managers only
        $managers = $this->getApprovedManagers($team);
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendAttentionAlert: Found " . count($managers) . " approved managers for team {$team->getName()}\n", FILE_APPEND);
        
        foreach ($managers as $player) {
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendAttentionAlert: Sending alert to manager {$player->getEmail()}\n", FILE_APPEND);
            $this->sendManagerAlert(
                $player->getEmail(),
                $team,
                'attention',
                $percentageUsed,
                $remainingAmount
            );
        }

        // Record alert and mark as sent
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] sendAttentionAlert: Recording alert\n", FILE_APPEND);
        $this->recordAlert($budget, 'attention', $percentageUsed, $remainingAmount);
        $this->markAlertAsManagerNotified($budget, 'attention');
    }

    /**
     * Send email alert to team manager
     */
    private function sendManagerAlert(string $email, Team $team, string $type, float $percentageUsed, float $remainingAmount, ?float $overage = null): void
    {
        $logFile = __DIR__ . '/../../var/log/budget_alert.log';
        $subject = '';
        $message = '';

        if ($type === 'critical') {
            $subject = "⚠️ CRITIQUE: Budget dépassé pour l'équipe {$team->getName()}";
            $message = $this->buildCriticalManagerEmail($team, $percentageUsed, $remainingAmount, $overage);
        } elseif ($type === 'high_threshold') {
            $subject = "🔴 Alerte: Budget à 90% pour l'équipe {$team->getName()}";
            $message = $this->buildHighThresholdManagerEmail($team, $percentageUsed, $remainingAmount);
        } elseif ($type === 'attention') {
            $subject = "🟡 Attention: Budget à 75% pour l'équipe {$team->getName()}";
            $message = $this->buildAttentionManagerEmail($team, $percentageUsed, $remainingAmount);
        }

        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Sending {$type} alert to manager {$email} for team {$team->getName()}\n", FILE_APPEND);
        $this->emailService->send($email, $subject, $message);
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Email sent to {$email}\n", FILE_APPEND);
    }

    /**
     * Send email alert to admin
     */
    private function sendAdminAlert(Team $team, string $type, float $percentageUsed, float $remainingAmount): void
    {
        $logFile = __DIR__ . '/../../var/log/budget_alert.log';
        // Get all admins from database
        // For now, we'll use hardcoded admin email
        $adminEmail = 'melkimalek888@gmail.com'; // TODO: Load from settings or admin users

        $subject = '';
        $message = '';

        if ($type === 'critical') {
            $subject = "🔴 CRITIQUE: Équipe {$team->getName()} a dépassé son budget";
            $message = $this->buildCriticalAdminEmail($team, $percentageUsed, $remainingAmount);
        } elseif ($type === 'high_threshold') {
            $subject = "🟠 L'équipe {$team->getName()} a atteint 90% de son budget";
            $message = $this->buildHighThresholdAdminEmail($team, $percentageUsed, $remainingAmount);
        }

        if ($subject && $message) {
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Sending admin alert to {$adminEmail} for team {$team->getName()} - Type: {$type}\n", FILE_APPEND);
            $this->emailService->send($adminEmail, $subject, $message);
            @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Admin email sent to {$adminEmail}\n", FILE_APPEND);
        }
    }

    /**
     * Update alert to mark manager notification as sent
     */
    private function markAlertAsManagerNotified(Budget $budget, string $type): void
    {
        $recentAlert = $this->budgetAlertRepository->findRecentAlerts($budget, $type);
        if ($recentAlert) {
            $recentAlert->setManagerNotificationSentAt(new DateTime());
            $this->entityManager->persist($recentAlert);
            $this->entityManager->flush();
        }
    }

    /**
     * Update alert to mark admin notification as sent
     */
    private function markAlertAsAdminNotified(Budget $budget, string $type): void
    {
        $recentAlert = $this->budgetAlertRepository->findRecentAlerts($budget, $type);
        if ($recentAlert) {
            $recentAlert->setAdminNotificationSentAt(new DateTime());
            $this->entityManager->persist($recentAlert);
            $this->entityManager->flush();
        }
    }

    /**
     * Build critical alert email for manager
     */
    private function buildCriticalManagerEmail(Team $team, float $percentageUsed, float $remainingAmount, float $overage): string
    {
        return <<<HTML
        <html>
        <body style="font-family: Arial, sans-serif; background-color: #f5f5f5;">
            <div style="max-width: 600px; margin: 20px auto; background-color: white; padding: 20px; border-radius: 8px; border-left: 5px solid #d32f2f;">
                <h2 style="color: #d32f2f;">⚠️ ALERTE CRITIQUE - BUDGET DÉPASSÉ</h2>
                <p>Bonjour,</p>
                <p>Votre équipe <strong>{$team->getName()}</strong> a <strong>dépassé son budget alloué</strong>.</p>
                
                <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p><strong>Situation actuelle:</strong></p>
                    <ul>
                        <li>Taux d'utilisation: <strong style="color: #d32f2f;">{$percentageUsed}%</strong></li>
                        <li>Montant dépassé: <strong style="color: #d32f2f;">+{$overage}€</strong></li>
                    </ul>
                </div>

                <p style="color: #666;">Actions recommandées:</p>
                <ul>
                    <li>Réduire les dépenses immédiates</li>
                    <li>Planifier un budget révisé</li>
                    <li>Contacter l'administrateur pour une assistance</li>
                </ul>

                <p style="margin-top: 30px; color: #999; font-size: 12px;">
                    Ceci est un message automatique. Merci de ne pas y répondre.
                </p>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Build high threshold alert email for manager
     */
    private function buildHighThresholdManagerEmail(Team $team, float $percentageUsed, float $remainingAmount): string
    {
        return <<<HTML
        <html>
        <body style="font-family: Arial, sans-serif; background-color: #f5f5f5;">
            <div style="max-width: 600px; margin: 20px auto; background-color: white; padding: 20px; border-radius: 8px; border-left: 5px solid #f57c00;">
                <h2 style="color: #f57c00;">🔴 ALERTE IMPORTANTE</h2>
                <p>Bonjour,</p>
                <p>Votre équipe <strong>{$team->getName()}</strong> a atteint <strong>90% de son budget</strong>.</p>
                
                <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p><strong>Situation actuelle:</strong></p>
                    <ul>
                        <li>Taux d'utilisation: <strong style="color: #f57c00;">{$percentageUsed}%</strong></li>
                        <li>Budget restant: <strong>{$remainingAmount}€</strong></li>
                    </ul>
                </div>

                <p>Il vous reste très peu de budget pour le reste de la période. Veuillez:</p>
                <ul>
                    <li>Réduire les dépenses non essentielles</li>
                    <li>Planifier les dépenses à venir</li>
                    <li>Évaluer si un augmentation de budget est nécessaire</li>
                </ul>

                <p style="margin-top: 30px; color: #999; font-size: 12px;">
                    Ceci est un message automatique. Merci de ne pas y répondre.
                </p>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Build attention alert email for manager
     */
    private function buildAttentionManagerEmail(Team $team, float $percentageUsed, float $remainingAmount): string
    {
        return <<<HTML
        <html>
        <body style="font-family: Arial, sans-serif; background-color: #f5f5f5;">
            <div style="max-width: 600px; margin: 20px auto; background-color: white; padding: 20px; border-radius: 8px; border-left: 5px solid #fbc02d;">
                <h2 style="color: #f57f17;">🟡 ATTENTION</h2>
                <p>Bonjour,</p>
                <p>Votre équipe <strong>{$team->getName()}</strong> a atteint <strong>75% de son budget alloué</strong>.</p>
                
                <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p><strong>Situation actuelle:</strong></p>
                    <ul>
                        <li>Taux d'utilisation: <strong>{$percentageUsed}%</strong></li>
                        <li>Budget restant: <strong>{$remainingAmount}€</strong></li>
                    </ul>
                </div>

                <p>Nous vous conseillons de:</p>
                <ul>
                    <li>Examiner vos dépenses actuelles</li>
                    <li>Planifier les futures dépenses avec prudence</li>
                    <li>Réduire les dépenses optionnelles si possible</li>
                </ul>

                <p style="margin-top: 30px; color: #999; font-size: 12px;">
                    Ceci est un message automatique. Merci de ne pas y répondre.
                </p>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Build critical alert email for admin
     */
    private function buildCriticalAdminEmail(Team $team, float $percentageUsed, float $remainingAmount): string
    {
        return <<<HTML
        <html>
        <body style="font-family: Arial, sans-serif; background-color: #f5f5f5;">
            <div style="max-width: 600px; margin: 20px auto; background-color: white; padding: 20px; border-radius: 8px; border-left: 5px solid #c62828;">
                <h2 style="color: #c62828;">🔴 ALERTE CRITIQUE - BUDGET DÉPASSÉ</h2>
                <p>Action requise:</p>
                <p>L'équipe <strong>{$team->getName()}</strong> a <strong>dépassé son budget alloué</strong>.</p>
                
                <div style="background-color: #ffebee; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p><strong>Détails:</strong></p>
                    <ul>
                        <li>Équipe: {$team->getName()}</li>
                        <li>Taux d'utilisation: <strong style="color: #c62828;">{$percentageUsed}%</strong></li>
                        <li>Montant négatif: <strong style="color: #c62828;">{$remainingAmount}€</strong></li>
                    </ul>
                </div>

                <p>Actions recommandées:</p>
                <ul>
                    <li>Vérifier les dépenses de l'équipe</li>
                    <li>Contacter le gestionnaire de l'équipe</li>
                    <li>Ajuster le budget si nécessaire</li>
                    <li>Valider ou refuser les dépenses en attente</li>
                </ul>

                <p style="margin-top: 30px; color: #999; font-size: 12px;">
                    Ceci est un message automatique. Merci de ne pas y répondre.
                </p>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Build high threshold alert email for admin
     */
    private function buildHighThresholdAdminEmail(Team $team, float $percentageUsed, float $remainingAmount): string
    {
        return <<<HTML
        <html>
        <body style="font-family: Arial, sans-serif; background-color: #f5f5f5;">
            <div style="max-width: 600px; margin: 20px auto; background-color: white; padding: 20px; border-radius: 8px; border-left: 5px solid #ef6c00;">
                <h2 style="color: #ef6c00;">🟠 ALERTE BUDGET - 90% ATTEINT</h2>
                <p>Supervision requise:</p>
                <p>L'équipe <strong>{$team->getName()}</strong> a atteint <strong>90% de son budget alloué</strong>.</p>
                
                <div style="background-color: #fff3e0; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p><strong>Détails:</strong></p>
                    <ul>
                        <li>Équipe: {$team->getName()}</li>
                        <li>Taux d'utilisation: <strong>{$percentageUsed}%</strong></li>
                        <li>Budget restant: <strong>{$remainingAmount}€</strong></li>
                    </ul>
                </div>

                <p>Actions recommandées:</p>
                <ul>
                    <li>Monitorer les futures dépenses</li>
                    <li>Vérifier les dépenses en attente de validation</li>
                    <li>Discuter avec le gestionnaire de l'équipe si nécessaire</li>
                </ul>

                <p style="margin-top: 30px; color: #999; font-size: 12px;">
                    Ceci est un message automatique. Merci de ne pas y répondre.
                </p>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Record alert in database
     */
    private function recordAlert(Budget $budget, string $type, float $percentageUsed, float $remainingAmount): void
    {
        $alert = new BudgetAlert();
        $alert->setBudget($budget);
        $alert->setType($type);
        $alert->setBudgetPercentage($percentageUsed);
        $alert->setRemainingAmount($remainingAmount);
        
        $this->entityManager->persist($alert);
        $this->entityManager->flush();
    }

    /**
     * Get approved managers from a team
     */
    private function getApprovedManagers(Team $team): array
    {
        $approvedManagers = [];
        foreach ($team->getPlayers() as $player) {
            // Check if player has ROLE_MANAGER and is approved
            if (in_array('ROLE_MANAGER', $player->getRoles()) && $player->isApproved() && $player->getEmail()) {
                $approvedManagers[] = $player;
            }
        }
        return $approvedManagers;
    }
}
