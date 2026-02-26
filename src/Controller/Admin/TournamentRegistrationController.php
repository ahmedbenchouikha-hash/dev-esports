<?php

namespace App\Controller\Admin;

use App\Entity\TournamentRegistration;
use App\Repository\TournamentRegistrationRepository;
use App\Service\GeminiRegistrationReviewService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/tournament-registrations', name: 'admin_tournament_registration_')]
#[IsGranted('ROLE_ADMIN')]
class TournamentRegistrationController extends AbstractController
{
    #[Route('/{id}/ai-review', name: 'ai_review', methods: ['GET'])]
    public function aiReview(TournamentRegistration $registration, GeminiRegistrationReviewService $gemini): JsonResponse
    {
        try {
            $result = $gemini->review($registration);

            return $this->json([
                'success' => true,
                'decision' => $result['decision'],
                'reason' => $result['reason'],
                'score' => $result['score'],
                'source' => $result['source'],
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => 'AI review failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(TournamentRegistrationRepository $registrationRepository): Response
    {
        // Get all registrations grouped by status
        $pendingRegistrations = $registrationRepository->findBy(['status' => 'pending'], ['createdAt' => 'DESC']);
        $approvedRegistrations = $registrationRepository->findBy(['status' => 'approved'], ['updatedAt' => 'DESC']);
        $rejectedRegistrations = $registrationRepository->findBy(['status' => 'rejected'], ['updatedAt' => 'DESC']);

        // Count statistics
        $stats = [
            'pending' => count($pendingRegistrations),
            'approved' => count($approvedRegistrations),
            'rejected' => count($rejectedRegistrations),
            'total' => count($pendingRegistrations) + count($approvedRegistrations) + count($rejectedRegistrations)
        ];

        return $this->render('admin/tournament_registrations/list.html.twig', [
            'pendingRegistrations' => $pendingRegistrations,
            'approvedRegistrations' => $approvedRegistrations,
            'rejectedRegistrations' => $rejectedRegistrations,
            'stats' => $stats,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(TournamentRegistration $registration): Response
    {
        return $this->render('admin/tournament_registrations/show.html.twig', [
            'registration' => $registration,
        ]);
    }

    #[Route('/{id}/approve', name: 'approve', methods: ['POST'])]
    public function approve(
        TournamentRegistration $registration, 
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Validate CSRF token
        if (!$this->isCsrfTokenValid('approve' . $registration->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_tournament_registration_show', ['id' => $registration->getId()]);
        }

        // Get admin notes if any
        $adminNotes = $request->request->get('admin_notes', '');
        
        // Update registration status
        $registration->setStatus('approved');
        $registration->setUpdatedAt(new \DateTimeImmutable());
        $registration->setReviewedAt(new \DateTimeImmutable());
        $registration->setReviewedBy($this->getUser());
        $registration->setAdminNotes($adminNotes);

        $entityManager->flush();

        // Log the action
        $this->logAdminAction('approved', $registration);

        $this->addFlash('success', sprintf(
            'Registration for team "%s" has been approved successfully.',
            $registration->getTeam()->getName(),
        ));

        return $this->redirectToRoute('admin_tournament_registration_show', ['id' => $registration->getId()]);
    }

    #[Route('/{id}/reject', name: 'reject', methods: ['POST'])]
    public function reject(
        TournamentRegistration $registration, 
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Validate CSRF token
        if (!$this->isCsrfTokenValid('reject' . $registration->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_tournament_registration_show', ['id' => $registration->getId()]);
        }

        // Get rejection reason (required)
        $rejectionReason = $request->request->get('rejection_reason', '');
        
        if (empty($rejectionReason)) {
            $this->addFlash('error', 'Please provide a reason for rejection.');
            return $this->redirectToRoute('admin_tournament_registration_show', ['id' => $registration->getId()]);
        }

        // Update registration status
        $registration->setStatus('rejected');
        $registration->setUpdatedAt(new \DateTimeImmutable());
        $registration->setReviewedAt(new \DateTimeImmutable());
        $registration->setReviewedBy($this->getUser());
        $registration->setAdminNotes($rejectionReason);

        $entityManager->flush();

        // Log the action
        $this->logAdminAction('rejected', $registration, $rejectionReason);

        $this->addFlash('success', sprintf(
            'Registration for team "%s" has been rejected.',
            $registration->getTeam()->getName(),
        ));

        return $this->redirectToRoute('admin_tournament_registration_show', ['id' => $registration->getId()]);
    }

    #[Route('/bulk-approve', name: 'bulk_approve', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function bulkApprove(
        Request $request,
        TournamentRegistrationRepository $registrationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Get selected registration IDs
        $ids = $request->request->all('registration_ids');
        
        if (empty($ids)) {
            $this->addFlash('warning', 'No registrations selected.');
            return $this->redirectToRoute('admin_tournament_registration_list');
        }

        // Find all pending registrations with the selected IDs
        $registrations = $registrationRepository->findBy(['id' => $ids, 'status' => 'pending']);
        $count = 0;

        foreach ($registrations as $registration) {
            // Update each registration
            $registration->setStatus('approved');
            $registration->setUpdatedAt(new \DateTimeImmutable());
            $registration->setReviewedAt(new \DateTimeImmutable());
            $registration->setReviewedBy($this->getUser());

            $count++;
        }

        $entityManager->flush();

        $this->addFlash('success', sprintf(
            '%d registrations have been approved successfully.',
            $count
        ));

        return $this->redirectToRoute('admin_tournament_registration_list');
    }

    #[Route('/bulk-reject', name: 'bulk_reject', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function bulkReject(
        Request $request,
        TournamentRegistrationRepository $registrationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Get selected registration IDs
        $ids = $request->request->all('registration_ids');
        
        if (empty($ids)) {
            $this->addFlash('warning', 'No registrations selected.');
            return $this->redirectToRoute('admin_tournament_registration_list');
        }

        // Get rejection reason
        $reason = $request->request->get('bulk_rejection_reason', 'Your registration has been reviewed and was not approved at this time.');
        
        // Find all pending registrations with the selected IDs
        $registrations = $registrationRepository->findBy(['id' => $ids, 'status' => 'pending']);
        $count = 0;

        foreach ($registrations as $registration) {
            // Update each registration
            $registration->setStatus('rejected');
            $registration->setUpdatedAt(new \DateTimeImmutable());
            $registration->setReviewedAt(new \DateTimeImmutable());
            $registration->setReviewedBy($this->getUser());
            $registration->setAdminNotes($reason);

            $count++;
        }

        $entityManager->flush();

        $this->addFlash('success', sprintf(
            '%d registrations have been rejected.',
            $count
        ));

        return $this->redirectToRoute('admin_tournament_registration_list');
    }

    #[Route('/export', name: 'export', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function export(TournamentRegistrationRepository $registrationRepository): Response
    {
        // Get all registrations
        $registrations = $registrationRepository->findAll();

        // Create CSV content
        $csv = "ID,Tournament,Team,Player,Status,Submitted,Reviewed\n";
        
        foreach ($registrations as $reg) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s\n",
                $reg->getId(),
                $reg->getTournament()->getName(),
                $reg->getTeam()->getName(),
                $reg->getPlayer()->getEmail(),
                $reg->getStatus(),
                $reg->getCreatedAt()->format('Y-m-d H:i'),
                $reg->getReviewedAt() ? $reg->getReviewedAt()->format('Y-m-d H:i') : 'Not reviewed'
            );
        }

        // Create response with CSV file
        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="tournament_registrations_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    #[Route('/stats', name: 'stats', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function stats(TournamentRegistrationRepository $registrationRepository): Response
    {
        // Get statistics for dashboard
        $stats = [
            'pending' => $registrationRepository->count(['status' => 'pending']),
            'approved' => $registrationRepository->count(['status' => 'approved']),
            'rejected' => $registrationRepository->count(['status' => 'rejected']),
            'total' => $registrationRepository->count([])
        ];

        // Get recent activity
        $recent = $registrationRepository->findBy([], ['updatedAt' => 'DESC'], 10);

        return $this->render('admin/tournament_registrations/stats.html.twig', [
            'stats' => $stats,
            'recent' => $recent,
        ]);
    }

    /**
     * Log admin actions for audit trail
     */
    private function logAdminAction(string $action, TournamentRegistration $registration, ?string $reason = null): void
    {
        try {
            $logMessage = sprintf(
                '[%s] Admin %s %s registration for team "%s" (ID: %d) in tournament "%s". %s',
                (new \DateTime())->format('Y-m-d H:i:s'),
                $this->getUser() ? $this->getUser()->getEmail() : 'Unknown',
                $action,
                $registration->getTeam() ? $registration->getTeam()->getName() : 'Unknown Team',
                $registration->getId(),
                $registration->getTournament() ? $registration->getTournament()->getName() : 'Unknown Tournament',
                $reason ? "Reason: $reason" : ''
            );

            // Log to file
            $logDir = dirname(__DIR__, 3) . '/var/log';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
            
            $logFile = $logDir . '/admin_actions.log';
            file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
        } catch (\Exception $e) {
            // Silently fail - logging shouldn't break the main functionality
        }
    }
}
