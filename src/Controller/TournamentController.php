<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Tournament;
use App\Entity\TournamentRegistration;
use App\Entity\User;
use App\Form\TournamentRegistrationType;
use App\Form\TournamentType;
use App\Repository\TournamentRepository;
use App\Repository\TournamentRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tournaments', name: 'tournament_')]
class TournamentController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(TournamentRepository $tournamentRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $search = trim((string) $request->query->get('search', ''));
        $status = (string) $request->query->get('status', '');
        $page = max(1, $request->query->getInt('page', 1));

        $queryBuilder = $tournamentRepository->createAdminListQueryBuilder($search, $status, 'startDate', 'DESC');
        $tournaments = $paginator->paginate($queryBuilder, $page, 9);

        $statusCounts = $tournamentRepository->getStatusCountsForFilters($search, $status);

        $chartLabels = array_map(static fn(string $value): string => ucfirst($value), array_keys($statusCounts));
        $chartData = array_values($statusCounts);

        return $this->render('tournament/index.html.twig', [
            'tournaments' => $tournaments,
            'search' => $search,
            'status' => $status,
            'chart_labels' => $chartLabels,
            'chart_data' => $chartData,
        ]);
    }

    #[Route('/upcoming', name: 'upcoming', methods: ['GET'])]
    public function upcoming(TournamentRepository $tournamentRepository): Response
    {
        $tournaments = $tournamentRepository->findUpcoming();

        return $this->render('tournament/upcoming.html.twig', [
            'tournaments' => $tournaments,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, TournamentRepository $tournamentRepository): Response
    {
        $tournament = $tournamentRepository->find($id);

        if (!$tournament) {
            // Check if there are any tournaments at all
            $count = $tournamentRepository->count([]);
            if ($count === 0) {
                $this->addFlash('warning', 'No tournaments exist yet. Please create one first.');
                return $this->redirectToRoute('tournament_index');
            }
            
            throw $this->createNotFoundException(sprintf('Tournament with ID %d not found. Available tournament IDs: please check the database.', $id));
        }

        $userTeam = null;
        $user = $this->getUser();
        if ($user instanceof Player) {
            $userTeam = $user->getTeam();
        }

        return $this->render('tournament/show.html.twig', [
            'tournament' => $tournament,
            'userTeam' => $userTeam,
        ]);
    }

    #[Route('/available', name: 'available', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function availableTournaments(
        TournamentRepository $tournamentRepository,
        TournamentRegistrationRepository $registrationRepository
    ): Response
    {
        $tournaments = $tournamentRepository->findByStatus('pending');
        /** @var User $user */
        $user = $this->getUser();
        
        $team = null;
        // Check if user is a Player instance
        if ($user instanceof Player) {
            $team = $user->getTeam();
        }

        $registrationStatuses = [];
        if ($team) {
            foreach ($tournaments as $tournament) {
                $registration = $registrationRepository->findByTeamAndTournament($team, $tournament);
                $registrationStatuses[$tournament->getId()] = $registration;
            }
        }

        return $this->render('tournament/available.html.twig', [
            'tournaments' => $tournaments,
            'registrationStatuses' => $registrationStatuses,
            'team' => $team,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response
    {
        $tournament = new Tournament();
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tournament);
            $entityManager->flush();
            $this->addFlash('success', 'Tournament created successfully!');
            return $this->redirectToRoute('tournament_show', ['id' => $tournament->getId()]);
        }

        return $this->render('tournament/new.html.twig', [
            'tournament' => $tournament,
            'form' => $form,
        ]);
    }

    #[Route('/create-test', name: 'create_test', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createTestTournament(EntityManagerInterface $entityManager): Response
    {
        $tournament = new Tournament();
        $tournament->setName('World Championship 2025');
        $tournament->setDescription('The biggest esports tournament of the year with teams from around the world competing for $1 million prize pool.');
        $tournament->setStartDate(new \DateTime('2025-02-15 00:00:00'));
        $tournament->setEndDate(new \DateTime('2025-02-28 00:00:00'));
        $tournament->setLocation('Los Angeles');
        $tournament->setStatus('pending');
        $tournament->setPrizePool(1000000);
        
        $entityManager->persist($tournament);
        $entityManager->flush();
        
        $this->addFlash('success', 'Test tournament created with ID: ' . $tournament->getId());
        return $this->redirectToRoute('tournament_show', ['id' => $tournament->getId()]);
    }

    #[Route('/{id}/join', name: 'join_form', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function joinTournamentForm(
        int $id,
        TournamentRepository $tournamentRepository,
        TournamentRegistrationRepository $registrationRepository
    ): Response
    {
        $tournament = $tournamentRepository->find($id);

        if (!$tournament) {
            throw $this->createNotFoundException('Tournament not found');
        }

        if ($tournament->getStatus() !== 'pending') {
            $this->addFlash('warning', 'This tournament is not accepting registrations at the moment.');
            return $this->redirectToRoute('tournament_available');
        }

        /** @var User $user */
        $user = $this->getUser();
        
        // Check if user is a Player
        if (!$user instanceof Player) {
            $this->addFlash('error', 'You must be a player to join a tournament.');
            return $this->redirectToRoute('tournament_available');
        }
        
        $team = $user->getTeam();

        if (!$team) {
            $this->addFlash('error', 'You must be part of a team to join a tournament.');
            return $this->redirectToRoute('tournament_available');
        }

        // Check if already registered
        $existingRegistration = $registrationRepository->findByTeamAndTournament($team, $tournament);
        if ($existingRegistration !== null) {
            $this->addFlash('info', 'Your team is already registered for this tournament.');
            return $this->redirectToRoute('tournament_available');
        }

        $registration = new TournamentRegistration();
        $registration->setTeamName($team->getName());
        $registration->setContactEmail($user->getEmail());
        
        $form = $this->createForm(TournamentRegistrationType::class, $registration);

        return $this->render('tournament/join_form.html.twig', [
            'form' => $form->createView(),
            'tournament' => $tournament,
            'team' => $team,
        ]);
    }

    #[Route('/{id}/join', name: 'join_submit', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function joinTournamentSubmit(
        int $id,
        Request $request,
        TournamentRepository $tournamentRepository,
        TournamentRegistrationRepository $registrationRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $tournament = $tournamentRepository->find($id);

        if (!$tournament) {
            throw $this->createNotFoundException('Tournament not found');
        }

        if ($tournament->getStatus() !== 'pending') {
            $this->addFlash('error', 'This tournament is not accepting registrations at the moment.');
            return $this->redirectToRoute('tournament_available');
        }

        /** @var User $user */
        $user = $this->getUser();
        
        // Check if user is a Player
        if (!$user instanceof Player) {
            $this->addFlash('error', 'You must be a player to join a tournament.');
            return $this->redirectToRoute('tournament_available');
        }
        
        $team = $user->getTeam();

        if (!$team) {
            $this->addFlash('error', 'You must be part of a team to join a tournament.');
            return $this->redirectToRoute('tournament_available');
        }

        // Check if already registered
        $existingRegistration = $registrationRepository->findByTeamAndTournament($team, $tournament);
        if ($existingRegistration !== null) {
            $this->addFlash('info', 'Your team is already registered for this tournament.');
            return $this->redirectToRoute('tournament_available');
        }

        $registration = new TournamentRegistration();
        $registration->setTeamName($team->getName());
        $registration->setContactEmail($user->getEmail());
        
        $form = $this->createForm(TournamentRegistrationType::class, $registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration->setTournament($tournament);
            $registration->setTeam($team);
            $registration->setPlayer($user);
            $registration->setStatus('pending');

            $entityManager->persist($registration);
            $entityManager->flush();

            $this->addFlash('success', 'Your team registration has been submitted! Please wait for admin approval.');

            return $this->redirectToRoute('tournament_show', ['id' => $id]);
        }

        return $this->render('tournament/join_form.html.twig', [
            'form' => $form->createView(),
            'tournament' => $tournament,
            'team' => $team,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        int $id, 
        Request $request, 
        TournamentRepository $tournamentRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $tournament = $tournamentRepository->find($id);

        if (!$tournament) {
            throw $this->createNotFoundException('Tournament not found');
        }

        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Tournament updated successfully!');
            return $this->redirectToRoute('tournament_show', ['id' => $tournament->getId()]);
        }

        return $this->render('tournament/edit.html.twig', [
            'tournament' => $tournament,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        int $id, 
        Request $request, 
        TournamentRepository $tournamentRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $tournament = $tournamentRepository->find($id);

        if (!$tournament) {
            throw $this->createNotFoundException('Tournament not found');
        }

        if ($this->isCsrfTokenValid('delete' . $tournament->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tournament);
            $entityManager->flush();
            $this->addFlash('success', 'Tournament deleted successfully!');
        }

        return $this->redirectToRoute('tournament_index');
    }

    #[Route('/my-registrations', name: 'my_registrations', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myRegistrations(
        TournamentRegistrationRepository $registrationRepository
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $registrations = [];
        
        // Check if user is a Player and has a team
        if ($user instanceof Player) {
            $team = $user->getTeam();
            if ($team) {
                $registrations = $registrationRepository->findByTeam($team);
            }
        }

        return $this->render('tournament/my_registrations.html.twig', [
            'registrations' => $registrations,
        ]);
    }

    #[Route('/{id}/registrations', name: 'registrations', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function tournamentRegistrations(
        int $id,
        TournamentRepository $tournamentRepository,
        TournamentRegistrationRepository $registrationRepository
    ): Response
    {
        $tournament = $tournamentRepository->find($id);

        if (!$tournament) {
            throw $this->createNotFoundException('Tournament not found');
        }

        $registrations = $registrationRepository->findByTournament($tournament);

        return $this->render('tournament/registrations.html.twig', [
            'tournament' => $tournament,
            'registrations' => $registrations,
        ]);
    }

    #[Route('/admin/registrations', name: 'admin_registration_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminRegistrationList(
        TournamentRegistrationRepository $registrationRepository
    ): Response
    {
        $allRegistrations = $registrationRepository->findAll();

        // Group registrations by tournament
        $registrationsByTournament = [];
        foreach ($allRegistrations as $registration) {
            $tournamentId = $registration->getTournament()->getId();
            if (!isset($registrationsByTournament[$tournamentId])) {
                $registrationsByTournament[$tournamentId] = [
                    'tournament' => $registration->getTournament(),
                    'registrations' => []
                ];
            }
            $registrationsByTournament[$tournamentId]['registrations'][] = $registration;
        }

        return $this->render('tournament/admin_registrations.html.twig', [
            'registrationsByTournament' => $registrationsByTournament,
        ]);
    }

    #[Route('/admin/registrations/{id}', name: 'admin_registration_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminRegistrationShow(
        int $id,
        TournamentRegistrationRepository $registrationRepository
    ): Response
    {
        $registration = $registrationRepository->find($id);

        if (!$registration) {
            throw $this->createNotFoundException('Registration not found');
        }

        return $this->render('tournament/admin_registration_show.html.twig', [
            'registration' => $registration,
        ]);
    }

    #[Route('/admin/registrations/{id}/approve', name: 'admin_registration_approve', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminRegistrationApprove(
        int $id,
        Request $request,
        TournamentRegistrationRepository $registrationRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $registration = $registrationRepository->find($id);

        if (!$registration) {
            throw $this->createNotFoundException('Registration not found');
        }

        if (!$this->isCsrfTokenValid('approve' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $registration->setStatus('approved');
        $registration->setReviewedBy($this->getUser());
        $registration->setReviewedAt(new \DateTime());
        
        $adminNotes = $request->request->get('admin_notes', '');
        if ($adminNotes) {
            $registration->setAdminNotes($adminNotes);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Registration approved successfully!');

        return $this->redirectToRoute('tournament_admin_registration_show', ['id' => $registration->getId()]);
    }

    #[Route('/admin/registrations/{id}/reject', name: 'admin_registration_reject', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminRegistrationReject(
        int $id,
        Request $request,
        TournamentRegistrationRepository $registrationRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $registration = $registrationRepository->find($id);

        if (!$registration) {
            throw $this->createNotFoundException('Registration not found');
        }

        if (!$this->isCsrfTokenValid('reject' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $registration->setStatus('rejected');
        $registration->setReviewedBy($this->getUser());
        $registration->setReviewedAt(new \DateTime());
        
        $rejectionReason = $request->request->get('rejection_reason', '');
        if ($rejectionReason) {
            $registration->setAdminNotes($rejectionReason);
        }

        $entityManager->flush();
        $this->addFlash('warning', 'Registration rejected successfully!');

        return $this->redirectToRoute('tournament_admin_registration_show', ['id' => $registration->getId()]);
    }
}
