<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Team;
use App\Entity\TeamInvitation;
use App\Form\TeamType;
use App\Repository\PlayerRepository;
use App\Repository\TeamInvitationRepository;
use App\Repository\ManagerRequestRepository;
use App\Repository\TeamRepository;
use App\Service\PlayerRecommendationService;
use App\Service\TeamInvitationEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/teams', name: 'team_')]
class TeamController extends AbstractController
{
    private PlayerRepository $playerRepository;
    private TeamInvitationRepository $invitationRepository;
    private PlayerRecommendationService $playerRecommendationService;
    private TeamInvitationEmailService $teamInvitationEmailService;

    public function __construct(
        PlayerRepository $playerRepository,
        TeamInvitationRepository $invitationRepository,
        PlayerRecommendationService $playerRecommendationService,
        TeamInvitationEmailService $teamInvitationEmailService
    ) {
        $this->playerRepository = $playerRepository;
        $this->invitationRepository = $invitationRepository;
        $this->playerRecommendationService = $playerRecommendationService;
        $this->teamInvitationEmailService = $teamInvitationEmailService;
    }
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository, Request $request, EntityManagerInterface $em): Response
    {
        $this->syncLegacyEquipeToTeam($em);

        $search = $request->query->get('search', '');
        $game = $request->query->get('game', '');
        $level = $request->query->get('level', '');
        $status = $request->query->get('status', '');
        $country = $request->query->get('country', '');
        $sortBy = $request->query->get('sort', 'name');
        $direction = $request->query->get('direction', 'ASC');

        // Get current user
        $user = $this->getUser();
        $currentPlayer = ($user instanceof Player) ? $user : null;

        $teams = [];
        
        if ($this->isGranted('ROLE_ADMIN')) {
            // Admin sees all teams
            if ($search || $game || $level || $status || $country) {
                $teams = $teamRepository->searchAdvanced(
                    $search, $game, $level, 
                    $status ?: 'approuvé', 
                    $country, $sortBy, $direction
                );
            } else {
                $teams = $teamRepository->findAllOrdered($sortBy, $direction);
            }
        } elseif ($this->isGranted('ROLE_MANAGER') && $currentPlayer) {
            // Managers see ONLY their own teams (teams they are members of)
            // They can also see approved teams they are not members of
            $allTeams = $teamRepository->findAllOrdered($sortBy, $direction);
            
            foreach ($allTeams as $team) {
                $isMember = $team->getPlayers()->contains($currentPlayer);
                $isApproved = $team->getStatut() === 'approuvé';
                
                // Manager sees: teams they're in OR approved teams
                if ($isMember || $isApproved) {
                    $teams[] = $team;
                }
            }
            
            // Apply search filter after
            if ($search) {
                $search = strtolower(trim($search));
                $teams = array_filter($teams, function($team) use ($search) {
                    return strpos(strtolower($team->getName()), $search) !== false ||
                           strpos(strtolower($team->getCountry() ?? ''), $search) !== false;
                });
            }
        } else {
            // Regular players see only approved teams
            if ($search || $game || $level || $country) {
                $teams = $teamRepository->searchAdvanced(
                    $search, $game, $level, 
                    'approuvé',  // Force approved status
                    $country, $sortBy, $direction
                );
            } else {
                $teams = $teamRepository->findByStatus('approuvé');
            }
        }

        $totalTeams = $teamRepository->count([]);
        $pendingCount = $teamRepository->countByStatus('en attente');
        $approvedCount = $teamRepository->countByStatus('approuvé');
        $refusedCount = $teamRepository->countByStatus('refusé');

        $availableGames = ['LoL', 'CS:GO', 'Dota 2', 'FIFA'];
        $availableLevels = ['Débutant', 'Intermédiaire', 'Pro'];

        return $this->render('team/index.html.twig', [
            'teams' => $teams,
            'search' => $search,
            'game' => $game,
            'level' => $level,
            'status' => $status,
            'country' => $country,
            'sortBy' => $sortBy,
            'direction' => $direction,
            'totalTeams' => $totalTeams,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'refusedCount' => $refusedCount,
            'availableGames' => $availableGames,
            'availableLevels' => $availableLevels,
        ]);
    }

    #[Route('/my-teams', name: 'my_teams', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myTeams(TeamRepository $teamRepository): Response
    {
        $user = $this->getUser();
        
        // Ensure user is a Player
        if (!$user instanceof Player) {
            $this->addFlash('error', 'You must be a player to view this page.');
            return $this->redirectToRoute('home');
        }

        // Get only teams that the current player belongs to
        $teams = $user->getTeams()->toArray();

        // Sort teams by name
        usort($teams, function($a, $b) {
            return strcmp($a->getName(), $b->getName());
        });

        return $this->render('team/my_teams.html.twig', [
            'teams' => $teams,
            'totalTeams' => count($teams),
        ]);
    }
    
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, ManagerRequestRepository $managerRequestRepo): Response
    {
        $team = new Team();

        // If the current player previously submitted a manager request with a team name,
        // pre-fill the Team name in the creation form to save them time.
        $creator = $this->getUser();
        if ($creator instanceof Player) {
            $requests = $managerRequestRepo->findByPlayer($creator);
            if (count($requests) > 0) {
                $latest = $requests[0];
                $name = $latest->getTeamName();
                if (!empty($name)) {
                    $team->setName($name);
                }
            }
        }

        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        // Get all available players
        $allPlayers = $this->playerRepository->findAll();

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    // Handle logo upload
                    $file = $form->get('logo')->getData();
                    if ($file) {
                        try {
                            $uploadDir = (string) $this->getParameter('logos_directory');
                            if (!is_dir($uploadDir)) {
                                @mkdir($uploadDir, 0777, true);
                            }

                            $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg';
                            $filename = uniqid('logo_', true) . '.' . strtolower($extension);
                            $file->move($uploadDir, $filename);
                            $team->setLogo($filename);
                        } catch (\Throwable $uploadException) {
                            $this->addFlash('warning', '⚠️ Team created without logo (upload failed).');
                            \error_log('Team logo upload error: ' . $uploadException->getMessage());
                        }
                    }

                    // Add creator (current user) to team
                    $creator = $this->getUser();
                    if ($creator instanceof Player) {
                        $team->addPlayer($creator);
                        $team->setCreator($creator);
                    }

                    // Set team status and save
                    $team->setStatut('en attente');
                    $em->persist($team);
                    $em->flush();

                    $this->addFlash('success', '✅ Team created successfully! Your team is pending admin approval.');

                    return $this->redirectToRoute('team_show', [
                        'id' => $team->getId(),
                        'reco_mode' => 'choose',
                        'post_create' => 1,
                    ]);

                } catch (\Exception $e) {
                    $errorMsg = 'Error creating team: ' . $e->getMessage();
                    if (!$this->isDebug()) {
                        $errorMsg = 'An error occurred while creating your team. Please try again.';
                    }
                    $this->addFlash('error', '❌ ' . $errorMsg);
                    \error_log('Team creation error: ' . $e->getTraceAsString());
                }
            } else {
                // Form submission failed validation
                $errors = $form->getErrors(true);
                foreach ($errors as $error) {
                    $origin = $error->getOrigin();
                    $fieldName = $origin ? $origin->getName() : 'form';
                    $this->addFlash('warning', sprintf('Validation error (%s): %s', $fieldName, $error->getMessage()));
                }
            }
        }

        return $this->render('team/new.html.twig', [
            'form' => $form->createView(),
            'available_players' => $allPlayers,
            'team_created' => false,
            'suggested_players' => [],
            'created_team' => null,
        ]);
    }

    private function isDebug(): bool
    {
        return $this->getParameter('kernel.debug');
    }

    private function syncLegacyEquipeToTeam(EntityManagerInterface $em): void
    {
        $connection = $em->getConnection();

        try {
            $legacyExists = (int) $connection->fetchOne("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'equipe'");
            if ($legacyExists === 0) {
                return;
            }

            $connection->executeStatement(
                "INSERT INTO team (name, country, description, created_at, updated_at, logo, jeu, niveau, couleur_equipe, statut, date_validation, score, detailed_description)
                 SELECT e.nom, NULL, e.description, COALESCE(e.date_creation, NOW()), NOW(), e.logo, e.jeu, e.niveau, e.couleur_equipe, COALESCE(e.statut, 'en attente'), e.date_validation, COALESCE(e.score, 0), NULL
                 FROM equipe e
                 WHERE NOT EXISTS (SELECT 1 FROM team t WHERE t.name = e.nom)"
            );
        } catch (\Throwable) {
            // Keep teams page functional even if legacy sync fails
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Request $request, int $id, TeamRepository $teamRepository): Response
    {
        $team = $teamRepository->find($id);

        if (!$team) {
            throw $this->createNotFoundException('Team not found');
        }

        // Get current user
        $user = $this->getUser();
        $currentPlayer = ($user instanceof Player) ? $user : null;
        
        // Access control:
        // - Admin can always access
        // - Members of the team can access
        // - If team is not approved, only admin or team members can access
        // - If team is approved, anyone can access
        if (!$this->isGranted('ROLE_ADMIN')) {
            $isMember = $currentPlayer && $team->getPlayers()->contains($currentPlayer);
            if (!$isMember && $team->getStatut() !== 'approuvé') {
                throw $this->createAccessDeniedException('You cannot view this team. Only team members or admins can view pending teams.');
            }
        }

        $availablePlayers = [];
        $pendingInvitations = [];
        $aiRecommendations = [];
        $perfectTeamPlan = null;
        $recoMode = (string) $request->query->get('reco_mode', 'choose');
        if (!in_array($recoMode, ['choose', 'manual', 'auto'], true)) {
            $recoMode = 'choose';
        }
        
        $isTeamMember = $currentPlayer && $team->getPlayers()->contains($currentPlayer);
        $isTeamCreator = $currentPlayer && $team->getCreator() && $team->getCreator()->getId() === $currentPlayer->getId();
        $hasOpenSlots = $team->getPlayers()->count() < 5;
        $canManageRecruitment = $this->isGranted('ROLE_MANAGER')
            && ($isTeamMember || $isTeamCreator || $team->getCreator() === null);

        if ($canManageRecruitment && $hasOpenSlots) {
            // Get all players except current team members
            $allPlayers = $this->playerRepository->findAll();
            $teamPlayerIds = $team->getPlayers()->map(fn($p) => $p->getId())->toArray();
            $availablePlayers = array_filter($allPlayers, fn($p) => !in_array($p->getId(), $teamPlayerIds));

            // Get pending invitations
            $pendingInvitations = $this->invitationRepository->findTeamInvitations($team, 'pending');
        }

        if ($canManageRecruitment && $hasOpenSlots) {
            if ($recoMode === 'manual') {
                $aiRecommendations = $this->playerRecommendationService->recommendForTeam($team, 5);
            } elseif ($recoMode === 'auto') {
                $perfectTeamPlan = $this->playerRecommendationService->recommendPerfectTeamPlan($team, 5);
            }
        }

        return $this->render('team/show.html.twig', [
            'team' => $team,
            'available_players' => $availablePlayers,
            'pending_invitations' => $pendingInvitations,
            'ai_recommendations' => $aiRecommendations,
            'perfect_team_plan' => $perfectTeamPlan,
            'reco_mode' => $recoMode,
            'can_manage_recruitment' => $canManageRecruitment,
            'has_open_slots' => $hasOpenSlots,
        ]);
    }

    #[Route('/invite', name: 'invite', methods: ['POST'])]
    public function invite(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('team_invite', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('team_show', ['id' => $request->request->get('team_id')]);
        }

        $teamId = $request->request->get('team_id');
        $playerId = $request->request->get('player_id');
        $recoMode = (string) $request->request->get('reco_mode', 'manual');
        if (!in_array($recoMode, ['choose', 'manual', 'auto'], true)) {
            $recoMode = 'manual';
        }

        $team = $em->getRepository(Team::class)->find($teamId);
        $player = $em->getRepository(Player::class)->find($playerId);
        $manager = $this->getUser();

        if (!$manager instanceof Player) {
            $this->addFlash('error', 'Only a manager player can send invitations');
            return $this->redirectToRoute('team_show', ['id' => $teamId]);
        }

        if (!$team || !$player) {
            $this->addFlash('error', 'Team or player not found');
            return $this->redirectToRoute('team_show', ['id' => $teamId]);
        }

        if (!$this->isGranted('ROLE_MANAGER')) {
            $this->addFlash('error', 'Only managers can send invitations');
            return $this->redirectToRoute('team_show', ['id' => $teamId]);
        }

        $isTeamMember = $team->getPlayers()->contains($manager);
        $isTeamCreator = $team->getCreator() && $team->getCreator()->getId() === $manager->getId();
        $canManageRecruitment = $isTeamMember || $isTeamCreator || $team->getCreator() === null;

        if (!$canManageRecruitment) {
            $this->addFlash('error', 'You can only invite players for teams you manage.');
            return $this->redirectToRoute('team_show', ['id' => $teamId]);
        }

        if ($team->getStatut() !== 'approuvé') {
            $this->addFlash('error', 'Can only invite players to approved teams');
            return $this->redirectToRoute('team_show', ['id' => $teamId]);
        }

        if ($team->getPlayers()->count() >= 5) {
            $this->addFlash('error', '❌ Team is full (maximum 5 players).');
            return $this->redirectToRoute('team_show', ['id' => $teamId]);
        }

        // Check if already invited
        $existingInvitation = $em->getRepository(TeamInvitation::class)->findByTeamAndPlayer($team, $player);
        if ($existingInvitation && $existingInvitation->getStatus() === 'pending') {
            $this->addFlash('warning', 'This player has already been invited');
            return $this->redirectToRoute('team_show', ['id' => $teamId]);
        }

        // Create new invitation
        $invitation = new TeamInvitation();
        $invitation->setTeam($team);
        $invitation->setPlayer($player);
        $invitation->setStatus('pending');
        $invitation->setType('invitation');
        $invitation->setCreatedAt(new \DateTime());

        $em->persist($invitation);
        $em->flush();

        $dashboardUrl = $this->generateUrl('player_dashboard', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $emailSent = $this->teamInvitationEmailService->sendInvitationEmail($player, $team, $manager, $dashboardUrl);

        if (!$emailSent) {
            $this->addFlash('warning', 'Invitation created, but email delivery failed. Check MAILER_DSN/BREVO configuration.');
        }

        $this->addFlash('success', '✅ Invitation sent to ' . $player->getNickname() . '!');
        return $this->redirectToRoute('team_show', [
            'id' => $teamId,
            'reco_mode' => $recoMode,
            'invite_success' => 1,
            'invitee' => $player->getNickname(),
        ]);
    }

    #[Route('/invitation/{id}/accept', name: 'invitation_accept', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function acceptInvitation(int $id, Request $request, EntityManagerInterface $em, TeamInvitationRepository $invitationRepo): Response
    {
        $invitation = $invitationRepo->find($id);
        $player = $this->getUser();

        if (!$this->isCsrfTokenValid('team_invitation_' . $id, (string) $request->request->get('_token'))) {
            $this->addFlash('error', '❌ Invalid CSRF token');
            return $this->redirectToRoute('player_dashboard');
        }

        if (!$invitation) {
            $this->addFlash('error', '❌ Invitation not found');
            return $this->redirectToRoute('player_dashboard');
        }

        // Verify that this invitation is for the current player
        if ($invitation->getPlayer()->getId() !== $player->getId()) {
            $this->addFlash('error', '❌ You cannot respond to this invitation');
            return $this->redirectToRoute('player_dashboard');
        }

        // Check if invitation is still pending
        if ($invitation->getStatus() !== 'pending') {
            $this->addFlash('warning', '⚠️ This invitation has already been responded to');
            return $this->redirectToRoute('player_dashboard');
        }

        try {
            $team = $invitation->getTeam();

            // Check if team is full
            if ($team->getPlayers()->count() >= 5) {
                $this->addFlash('error', '❌ This team is full (maximum 5 players). You could not join.');
                $invitation->setStatus('rejected');
                $invitation->setRespondedAt(new \DateTime());
                $em->flush();
                return $this->redirectToRoute('player_dashboard');
            }

            // Add player to team
            if (!$team->getPlayers()->contains($player)) {
                $team->addPlayer($player);
            }

            // Update invitation status
            $invitation->setStatus('accepted');
            $invitation->setRespondedAt(new \DateTime());
            
            $em->flush();

            $this->addFlash('success', '✅ You have accepted the invitation and joined ' . $team->getName() . '!');
        } catch (\Exception $e) {
            $this->addFlash('error', '❌ An error occurred: ' . $e->getMessage());
        }

        return $this->redirectToRoute('player_dashboard');
    }

    #[Route('/invitation/{id}/reject', name: 'invitation_reject', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function rejectInvitation(int $id, Request $request, EntityManagerInterface $em, TeamInvitationRepository $invitationRepo): Response
    {
        $invitation = $invitationRepo->find($id);
        /** @var \App\Entity\Player $player */
        $player = $this->getUser();

        if (!$this->isCsrfTokenValid('team_invitation_' . $id, (string) $request->request->get('_token'))) {
            $this->addFlash('error', '❌ Invalid CSRF token');
            return $this->redirectToRoute('player_dashboard');
        }

        if (!$invitation) {
            $this->addFlash('error', '❌ Invitation not found');
            return $this->redirectToRoute('player_dashboard');
        }

        // Verify that this invitation is for the current player
        if ($invitation->getPlayer()->getId() !== $player->getId()) {
            $this->addFlash('error', '❌ You cannot respond to this invitation');
            return $this->redirectToRoute('player_dashboard');
        }

        // Check if invitation is still pending
        if ($invitation->getStatus() !== 'pending') {
            $this->addFlash('warning', '⚠️ This invitation has already been responded to');
            return $this->redirectToRoute('player_dashboard');
        }

        try {
            $invitation->setStatus('rejected');
            $invitation->setRespondedAt(new \DateTime());
            $em->flush();

            $this->addFlash('success', '✅ You have declined the invitation');
        } catch (\Exception $e) {
            $this->addFlash('error', '❌ An error occurred: ' . $e->getMessage());
        }

        return $this->redirectToRoute('player_dashboard');
    }

    #[Route('/{id}/request-join', name: 'request_join', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function requestJoin(Request $request, Team $team, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('join' . $team->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', '❌ Invalid CSRF token');
            return $this->redirectToRoute('team_show', ['id' => $team->getId()]);
        }

        $player = $this->getUser();
        if (!$player instanceof Player) {
            $this->addFlash('error', 'You must be a player to request joining a team.');
            return $this->redirectToRoute('team_show', ['id' => $team->getId()]);
        }

        if ($team->getPlayers()->contains($player)) {
            $this->addFlash('warning', '⚠️ You are already a member of this team.');
            return $this->redirectToRoute('team_show', ['id' => $team->getId()]);
        }

        if ($team->getPlayers()->count() >= 5) {
            $this->addFlash('error', '❌ This team is full (maximum 5 players).');
            return $this->redirectToRoute('team_show', ['id' => $team->getId()]);
        }

        $existing = $this->invitationRepository->findByTeamAndPlayer($team, $player);
        if ($existing && $existing->getStatus() === 'pending') {
            $this->addFlash('warning', '⚠️ You already have a pending request/invitation for this team.');
            return $this->redirectToRoute('team_show', ['id' => $team->getId()]);
        }

        $requestJoin = new TeamInvitation();
        $requestJoin->setTeam($team);
        $requestJoin->setPlayer($player);
        $requestJoin->setStatus('pending');
        $requestJoin->setType('request');
        $requestJoin->setCreatedAt(new \DateTime());

        $em->persist($requestJoin);
        $em->flush();

        $this->addFlash('success', '✅ Join request sent to team managers.');
        return $this->redirectToRoute('team_show', ['id' => $team->getId()]);
    }

    #[Route('/manager/invitation-requests', name: 'manager_invitation_requests', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGER')]
    public function managerInvitationRequests(TeamInvitationRepository $invitationRepo): Response
    {
        $manager = $this->getUser();
        if (!$manager instanceof Player) {
            $this->addFlash('error', 'Only managers can access this page.');
            return $this->redirectToRoute('player_dashboard');
        }

        $pendingRequests = $invitationRepo->findPendingRequestsForManager($manager);

        return $this->render('team/invitation_requests.html.twig', [
            'pendingRequests' => $pendingRequests,
        ]);
    }

    #[Route('/manager/invitation/{id}/accept-request', name: 'request_accept', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function acceptRequest(int $id, Request $request, EntityManagerInterface $em, TeamInvitationRepository $invitationRepo): Response
    {
        $invitation = $invitationRepo->find($id);
        $manager = $this->getUser();

        if (!$invitation || !$manager instanceof Player) {
            $this->addFlash('error', '❌ Request not found');
            return $this->redirectToRoute('team_manager_invitation_requests');
        }

        if (!$this->isCsrfTokenValid('team_request_' . $invitation->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', '❌ Invalid CSRF token');
            return $this->redirectToRoute('team_manager_invitation_requests');
        }

        $team = $invitation->getTeam();
        if (!$team->getPlayers()->contains($manager)) {
            $this->addFlash('error', '❌ You cannot manage requests for this team.');
            return $this->redirectToRoute('team_manager_invitation_requests');
        }

        if ($invitation->getStatus() !== 'pending' || $invitation->getType() !== 'request') {
            $this->addFlash('warning', '⚠️ This request has already been processed.');
            return $this->redirectToRoute('team_manager_invitation_requests');
        }

        if ($team->getPlayers()->count() >= 5) {
            $this->addFlash('error', '❌ This team is full (maximum 5 players).');
            return $this->redirectToRoute('team_manager_invitation_requests');
        }

        $player = $invitation->getPlayer();
        if (!$team->getPlayers()->contains($player)) {
            $team->addPlayer($player);
        }

        $invitation->setStatus('accepted');
        $invitation->setRespondedAt(new \DateTime());
        $em->flush();

        $this->addFlash('success', '✅ Request accepted. Player added to team.');
        return $this->redirectToRoute('team_manager_invitation_requests');
    }

    #[Route('/manager/invitation/{id}/reject-request', name: 'request_reject', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function rejectRequest(int $id, Request $request, EntityManagerInterface $em, TeamInvitationRepository $invitationRepo): Response
    {
        $invitation = $invitationRepo->find($id);
        $manager = $this->getUser();

        if (!$invitation || !$manager instanceof Player) {
            $this->addFlash('error', '❌ Request not found');
            return $this->redirectToRoute('team_manager_invitation_requests');
        }

        if (!$this->isCsrfTokenValid('team_request_' . $invitation->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', '❌ Invalid CSRF token');
            return $this->redirectToRoute('team_manager_invitation_requests');
        }

        $team = $invitation->getTeam();
        if (!$team->getPlayers()->contains($manager)) {
            $this->addFlash('error', '❌ You cannot manage requests for this team.');
            return $this->redirectToRoute('team_manager_invitation_requests');
        }

        if ($invitation->getStatus() !== 'pending' || $invitation->getType() !== 'request') {
            $this->addFlash('warning', '⚠️ This request has already been processed.');
            return $this->redirectToRoute('team_manager_invitation_requests');
        }

        $invitation->setStatus('rejected');
        $invitation->setRespondedAt(new \DateTime());
        $em->flush();

        $this->addFlash('success', '✅ Request rejected.');
        return $this->redirectToRoute('team_manager_invitation_requests');
    }

    #[Route('/manager/sent-invitations', name: 'manager_sent_invitations', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGER')]
    public function managerSentInvitations(TeamInvitationRepository $invitationRepo): Response
    {
        $manager = $this->getUser();
        if (!$manager instanceof Player) {
            $this->addFlash('error', 'Only managers can access this page.');
            return $this->redirectToRoute('player_dashboard');
        }

        $sentInvitations = $invitationRepo->findSentInvitationsForManager($manager);

        return $this->render('team/sent_invitations.html.twig', [
            'sentInvitations' => $sentInvitations,
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Team $team, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        // Check if manager is a member of the team or is admin
        if (!$this->isGranted('ROLE_ADMIN')) {
            $manager = $this->getUser();
            if (!$team->getPlayers()->contains($manager)) {
                throw $this->createAccessDeniedException('You can only edit teams you are a member of');
            }
        }

        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // STEP 1: Validate using Symfony's form validation
            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', '❌ ' . $error->getMessage());
                }
                return $this->render('team/edit.html.twig', [
                    'form' => $form->createView(),
                    'team' => $team
                ]);
            }

            // STEP 2: Custom server-side validation
            $validationErrors = [];

            // REQUIRED: Team Name
            $name = $team->getName();
            if ($name === null || $name === '') {
                $validationErrors[] = '❌ Team name is REQUIRED and cannot be empty';
            } else {
                $name = trim($name);
                if ($name === '') {
                    $validationErrors[] = '❌ Team name is REQUIRED - Cannot be only spaces';
                } elseif (strlen($name) < 2) {
                    $validationErrors[] = '❌ Team name must be at least 2 characters long';
                } elseif (strlen($name) > 255) {
                    $validationErrors[] = '❌ Team name must not exceed 255 characters';
                } elseif (!preg_match('/^[a-zA-Z0-9éèêëàâäæîïôõöœùûüçñ\s\-\.]+$/i', $name)) {
                    $validationErrors[] = '❌ Team name contains invalid characters';
                }
            }

            // OPTIONAL: Country validation
            if (!empty($team->getCountry())) {
                $country = trim($team->getCountry());
                if (strlen($country) > 255) {
                    $validationErrors[] = '❌ Country must not exceed 255 characters';
                }
            }

            // OPTIONAL: Description validation
            if (!empty($team->getDescription())) {
                $desc = trim($team->getDescription());
                if (strlen($desc) > 1000) {
                    $validationErrors[] = '❌ Description must not exceed 1000 characters';
                }
            }

            // OPTIONAL: Detailed Description validation
            if (!empty($team->getDetailedDescription())) {
                $detailedDesc = trim($team->getDetailedDescription());
                if (strlen($detailedDesc) > 2000) {
                    $validationErrors[] = '❌ Detailed description must not exceed 2000 characters';
                }
            }

            // OPTIONAL: Color validation
            if (!empty($team->getCouleurEquipe())) {
                $color = trim($team->getCouleurEquipe());
                if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/i', $color)) {
                    $validationErrors[] = '❌ Invalid color format. Use hex format like #FF0000 or #FFF';
                }
            }

            // OPTIONAL: Game validation
            if (!empty($team->getJeu())) {
                $validGames = ['LoL', 'CS:GO', 'Dota 2', 'FIFA'];
                if (!in_array($team->getJeu(), $validGames)) {
                    $validationErrors[] = '❌ Invalid game. Choose from: LoL, CS:GO, Dota 2, FIFA';
                }
            }

            // OPTIONAL: Level validation
            if (!empty($team->getNiveau())) {
                $validLevels = ['Débutant', 'Intermédiaire', 'Pro'];
                if (!in_array($team->getNiveau(), $validLevels)) {
                    $validationErrors[] = '❌ Invalid level. Choose from: Débutant, Intermédiaire, Pro';
                }
            }

            // OPTIONAL: Captain ID validation
            if ($team->getCaptainId() !== null) {
                if (!is_int($team->getCaptainId()) || $team->getCaptainId() <= 0) {
                    $validationErrors[] = '❌ Captain ID must be a positive number';
                }
            }

            if (count($validationErrors) > 0) {
                foreach ($validationErrors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('team/edit.html.twig', [
                    'form' => $form->createView(),
                    'team' => $team
                ]);
            }

            try {
                // STEP 3: Handle logo upload
                $file = $form->get('logo')->getData();
                if ($file) {
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!in_array($file->getMimeType(), $allowedMimes)) {
                        $this->addFlash('error', '❌ Invalid image type. Only JPG, PNG, GIF are allowed.');
                        return $this->render('team/edit.html.twig', [
                            'form' => $form->createView(),
                            'team' => $team
                        ]);
                    }

                    if ($file->getSize() > 1048576) {
                        $this->addFlash('error', '❌ File size must not exceed 1MB.');
                        return $this->render('team/edit.html.twig', [
                            'form' => $form->createView(),
                            'team' => $team
                        ]);
                    }

                    $filename = uniqid('logo_') . '.' . $file->guessExtension();
                    $file->move($this->getParameter('logos_directory'), $filename);
                    $team->setLogo($filename);
                }

                // STEP 4: Handle members
                $membersString = $form->get('membres')->getData();
                if ($membersString !== null && $membersString !== '') {
                    $membersString = trim($membersString);
                    if (!empty($membersString)) {
                        $membersArray = array_filter(array_map('trim', explode(',', $membersString)));
                        
                        if (count($membersArray) > 50) {
                            $this->addFlash('error', '❌ Team cannot have more than 50 members.');
                            return $this->render('team/edit.html.twig', [
                                'form' => $form->createView(),
                                'team' => $team
                            ]);
                        }

                        foreach ($membersArray as $member) {
                            if (strlen($member) < 2 || strlen($member) > 100) {
                                $this->addFlash('error', '❌ Member name must be between 2 and 100 characters.');
                                return $this->render('team/edit.html.twig', [
                                    'form' => $form->createView(),
                                    'team' => $team
                                ]);
                            }
                        }
                        $team->setMembres($membresArray);
                    } else {
                        $team->setMembres([]);
                    }
                } else {
                    $team->setMembres([]);
                }

                // STEP 5: Final validation with Symfony Validator
                $errors = $validator->validate($team);
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $this->addFlash('error', '❌ ' . $error->getPropertyPath() . ': ' . $error->getMessage());
                    }
                    return $this->render('team/edit.html.twig', [
                        'form' => $form->createView(),
                        'team' => $team
                    ]);
                }

                // STEP 6: Save to database
                $team->setUpdatedAt(new \DateTime());
                $em->flush();

                $this->addFlash('success', '✅ Team updated successfully!');
                return $this->redirectToRoute('team_index');
                
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred while updating the team: ' . $e->getMessage());
                return $this->render('team/edit.html.twig', [
                    'form' => $form->createView(),
                    'team' => $team
                ]);
            }
        }

        return $this->render('team/edit.html.twig', [
            'form' => $form->createView(),
            'team' => $team
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Team $team, EntityManagerInterface $em): Response
    {
        // Check if manager is a member of the team or is admin
        if (!$this->isGranted('ROLE_ADMIN')) {
            $manager = $this->getUser();
            if (!$team->getPlayers()->contains($manager)) {
                throw $this->createAccessDeniedException('You can only delete teams you are a member of');
            }
        }

        if ($this->isCsrfTokenValid('delete' . $team->getId(), $request->request->get('_token'))) {
            try {
                $em->remove($team);
                $em->flush();
                $this->addFlash('success', '✅ Team deleted successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred while deleting the team: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', '❌ Invalid CSRF token');
        }
        return $this->redirectToRoute('team_index');
    }

    #[Route('/{id}/approve', name: 'approve', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function approve(Request $request, Team $team, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('approve' . $team->getId(), $request->request->get('_token'))) {
            try {
                $team->setStatut('approuvé');
                $team->setDateValidation(new \DateTime());
                $em->flush();
                $this->addFlash('success', '✅ Team approved!');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', '❌ Invalid CSRF token');
        }
        return $this->redirectToRoute('team_index');
    }

    #[Route('/{id}/join', name: 'join', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function join(Request $request, Team $team, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('join' . $team->getId(), $request->request->get('_token'))) {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();

            try {
                // Check if team is approved (can only join approved teams)
                if ($team->getStatut() !== 'approuvé') {
                    $this->addFlash('error', '❌ You can only join teams that have been approved by an admin.');
                    return $this->redirectToRoute('team_index');
                }

                // Check if team is full (max 5 members via TeamMember)
                if ($team->getMembers()->count() >= 5) {
                    $this->addFlash('error', '❌ This team is full (maximum 5 members).');
                    return $this->redirectToRoute('team_index');
                }

                // Check if user is already in the team
                $isMember = false;
                foreach ($team->getMembers() as $member) {
                    if ($member->getUser()->getId() === $user->getId()) {
                        $isMember = true;
                        break;
                    }
                }
                
                if ($isMember) {
                    $this->addFlash('warning', '⚠️ You are already a member of this team.');
                    return $this->redirectToRoute('team_index');
                }

                // Add user to team via TeamMember
                $teamMember = new \App\Entity\TeamMember();
                $teamMember->setTeam($team);
                $teamMember->setUser($user);
                
                $em->persist($teamMember);
                $em->flush();

                $this->addFlash('success', '✅ You have successfully joined ' . $team->getName() . '!');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred while joining the team: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', '❌ Invalid CSRF token');
        }
        return $this->redirectToRoute('team_index');
    }

    #[Route('/{id}/refuse', name: 'refuse', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function refuse(Request $request, Team $team, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('refuse' . $team->getId(), $request->request->get('_token'))) {
            try {
                $team->setStatut('refusé');
                $team->setDateValidation(new \DateTime());
                $em->flush();
                $this->addFlash('success', '✅ Team refused!');
            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', '❌ Invalid CSRF token');
        }
        return $this->redirectToRoute('team_index');
    }
}
