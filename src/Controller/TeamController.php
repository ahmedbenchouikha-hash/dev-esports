<?php

namespace App\Controller;

<<<<<<< HEAD
use App\Entity\Player;
use App\Entity\Team;
use App\Entity\TeamInvitation;
use App\Form\TeamType;
use App\Repository\PlayerRepository;
use App\Repository\TeamInvitationRepository;
=======
use App\Entity\Team;
use App\Entity\TeamMember;
use App\Entity\User;
use App\Form\CreateTeamType;
>>>>>>> module-user
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
<<<<<<< HEAD
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/teams', name: 'team_')]
class TeamController extends AbstractController
{
    private PlayerRepository $playerRepository;
    private TeamInvitationRepository $invitationRepository;

    public function __construct(
        PlayerRepository $playerRepository,
        TeamInvitationRepository $invitationRepository
    ) {
        $this->playerRepository = $playerRepository;
        $this->invitationRepository = $invitationRepository;
    }
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository, Request $request): Response
    {
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
    
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $team = new Team();
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
                        $filename = uniqid('logo_') . '.' . $file->guessExtension();
                        $file->move($this->getParameter('logos_directory'), $filename);
                        $team->setLogo($filename);
                    }

                    // Add creator (current user) to team
                    $creator = $this->getUser();
                    if ($creator instanceof Player) {
                        $team->addPlayer($creator);
                    }

                    // Add selected players to team
                    $selectedPlayerIds = $request->request->all()['team_players'] ?? [];
                    if (!empty($selectedPlayerIds)) {
                        foreach ($selectedPlayerIds as $playerId) {
                            $player = $this->playerRepository->find((int)$playerId);
                            if ($player) {
                                $team->addPlayer($player);
                            }
                        }
                    }

                    // Set team status and save
                    $team->setStatut('en attente');
                    $em->persist($team);
                    $em->flush();

                    $this->addFlash('success', '✅ Team created successfully! Your team is pending admin approval.');
                    return $this->redirectToRoute('team_index');

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
                    $this->addFlash('warning', 'Validation error: ' . $error->getMessage());
                }
            }
        }

        return $this->render('team/new.html.twig', [
            'form' => $form->createView(),
            'available_players' => $allPlayers,
        ]);
    }

    private function isDebug(): bool
    {
        return $this->getParameter('kernel.debug');
    }

    #[Route('/leaderboard', name: 'leaderboard', methods: ['GET'])]
    public function leaderboard(TeamRepository $teamRepository, Request $request): Response
    {
        $limit = max(1, min(100, (int) $request->query->get('limit', 20)));
        $teams = $teamRepository->findTopByScore($limit);

        return $this->render('team/leaderboard.html.twig', [
            'teams' => $teams,
            'limit' => $limit,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, TeamRepository $teamRepository): Response
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
        
        // Get available players for invitation (only if team is approved and user is a team member who is a manager)
        if ($team->getStatut() === 'approuvé' && $this->isGranted('ROLE_MANAGER')) {
            $isMember = $currentPlayer && $team->getPlayers()->contains($currentPlayer);
            if ($isMember) {
                // Get all players except current team members
                $allPlayers = $this->playerRepository->findAll();
                $teamPlayerIds = $team->getPlayers()->map(fn($p) => $p->getId())->toArray();
                $availablePlayers = array_filter($allPlayers, fn($p) => !in_array($p->getId(), $teamPlayerIds));
                
                // Get pending invitations
                $pendingInvitations = $this->invitationRepository->findTeamInvitations($team, 'pending');
            }
        }

        return $this->render('team/show.html.twig', [
            'team' => $team,
            'available_players' => $availablePlayers,
            'pending_invitations' => $pendingInvitations,
        ]);
    }

    #[Route('/invite', name: 'invite', methods: ['POST'])]
    public function invite(Request $request, EntityManagerInterface $em): Response
    {
        $teamId = $request->request->get('team_id');
        $playerId = $request->request->get('player_id');

        $team = $em->getRepository(Team::class)->find($teamId);
        $player = $em->getRepository(Player::class)->find($playerId);
        $manager = $this->getUser();

        if (!$team || !$player) {
            $this->addFlash('error', 'Team or player not found');
            return $this->redirectToRoute('team_show', ['id' => $teamId]);
        }

        // Check if manager is a member of the team
        if (!$team->getPlayers()->contains($manager)) {
            $this->addFlash('error', 'You can only invite players to teams you are a member of');
            return $this->redirectToRoute('team_show', ['id' => $teamId]);
        }

        if ($team->getStatut() !== 'approuvé') {
            $this->addFlash('error', 'Can only invite players to approved teams');
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
        $invitation->setCreatedAt(new \DateTime());

        $em->persist($invitation);
        $em->flush();

        $this->addFlash('success', '✅ Invitation sent to ' . $player->getNickname() . '!');
        return $this->redirectToRoute('team_show', ['id' => $teamId]);
    }

    #[Route('/invitation/{id}/accept', name: 'invitation_accept', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function acceptInvitation(int $id, EntityManagerInterface $em, TeamInvitationRepository $invitationRepo): Response
    {
        $invitation = $invitationRepo->find($id);
        $player = $this->getUser();

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
    public function rejectInvitation(int $id, EntityManagerInterface $em, TeamInvitationRepository $invitationRepo): Response
    {
        $invitation = $invitationRepo->find($id);
        $player = $this->getUser();

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
            $player = $this->getUser();

            try {
                // Check if team is approved (can only join approved teams)
                if ($team->getStatut() !== 'approuvé') {
                    $this->addFlash('error', '❌ You can only join teams that have been approved by an admin.');
                    return $this->redirectToRoute('team_index');
                }

                // Check if team is full
                if ($team->getPlayers()->count() >= 5) {
                    $this->addFlash('error', '❌ This team is full (maximum 5 players).');
                    return $this->redirectToRoute('team_index');
                }

                // Check if player is already in the team
                if ($team->getPlayers()->contains($player)) {
                    $this->addFlash('warning', '⚠️ You are already a member of this team.');
                    return $this->redirectToRoute('team_index');
                }

                // Add player to team
                $team->addPlayer($player);
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
=======
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/team')]
#[IsGranted('ROLE_USER')]
class TeamController extends AbstractController
{
    private EntityManagerInterface $em;
    private TeamRepository $teamRepository;

    public function __construct(EntityManagerInterface $em, TeamRepository $teamRepository)
    {
        $this->em = $em;
        $this->teamRepository = $teamRepository;
    }

    #[Route('/', name: 'team_index')]
    public function index(): Response
    {
        $user = $this->getUser();
        $userTeams = $this->em->getRepository(Team::class)->findBy(['creator' => $user]);
        $joinedTeams = $this->em->getRepository(TeamMember::class)->findBy(['user' => $user]);

        return $this->render('team/index.html.twig', [
            'userTeams' => $userTeams,
            'joinedTeams' => $joinedTeams,
        ]);
    }

    #[Route('/create', name: 'team_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $form = $this->createForm(CreateTeamType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $team = new Team();
            $team->setName($form->get('name')->getData());
            $team->setCreator($this->getUser());

            // Add creator as first member
            $member = new TeamMember();
            $member->setTeam($team);
            $member->setUser($this->getUser());
            $team->addMember($member);

            $this->em->persist($team);
            $this->em->persist($member);
            $this->em->flush();

            $this->addFlash('success', 'Team created successfully!');

            return $this->redirectToRoute('team_edit', ['id' => $team->getId()]);
        }

        return $this->render('team/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'team_edit', methods: ['GET', 'POST'])]
    public function edit(Team $team, Request $request): Response
    {
        if ($team->getCreator() !== $this->getUser()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        if ($request->isMethod('POST')) {
            $usernames = $request->request->all('player_username');
            $usernames = array_filter($usernames); // Remove empty values

            foreach ($usernames as $username) {
                $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);

                if (!$user) {
                    $this->addFlash('error', "User '$username' not found.");
                    continue;
                }

                // Check if user is already in team
                $existingMember = $this->em->getRepository(TeamMember::class)->findOneBy([
                    'team' => $team,
                    'user' => $user,
                ]);

                if ($existingMember) {
                    $this->addFlash('warning', "$username is already in this team.");
                    continue;
                }

                // Check if team is full
                if ($team->isFull()) {
                    $this->addFlash('error', 'Team is full (maximum 5 members).');
                    break;
                }

                $member = new TeamMember();
                $member->setTeam($team);
                $member->setUser($user);
                $team->addMember($member);
                $this->em->persist($member);
            }

            $this->em->flush();
            $this->addFlash('success', 'Players added to team!');

            return $this->redirectToRoute('team_show', ['id' => $team->getId()]);
        }

        return $this->render('team/edit.html.twig', [
            'team' => $team,
        ]);
    }

    #[Route('/{id}', name: 'team_show')]
    public function show(Team $team): Response
    {
        return $this->render('team/show.html.twig', [
            'team' => $team,
        ]);
    }

    #[Route('/join/list', name: 'team_join_list')]
    public function joinList(): Response
    {
        $teams = $this->teamRepository->findTeamsWithAvailableSlots();
        $user = $this->getUser();

        // Filter out teams the user is already in
        $availableTeams = array_filter($teams, function (Team $team) use ($user) {
            return !$this->em->getRepository(TeamMember::class)->findOneBy([
                'team' => $team,
                'user' => $user,
            ]);
        });

        return $this->render('team/join_list.html.twig', [
            'teams' => $availableTeams,
        ]);
    }

    #[Route('/{id}/join', name: 'team_join', methods: ['POST'])]
    public function join(Team $team, Request $request): Response
    {
        $user = $this->getUser();

        // Check if user is already in team
        $existingMember = $this->em->getRepository(TeamMember::class)->findOneBy([
            'team' => $team,
            'user' => $user,
        ]);

        if ($existingMember) {
            $this->addFlash('error', 'You are already in this team.');
            return $this->redirectToRoute('team_join_list');
        }

        // Check if team is full
        if ($team->isFull()) {
            $this->addFlash('error', 'This team is full.');
            return $this->redirectToRoute('team_join_list');
        }

        // CSRF token validation
        if (!$this->isCsrfTokenValid('join-team-' . $team->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid token.');
            return $this->redirectToRoute('team_join_list');
        }

        $member = new TeamMember();
        $member->setTeam($team);
        $member->setUser($user);
        $team->addMember($member);

        $this->em->persist($member);
        $this->em->flush();

        $this->addFlash('success', 'You joined ' . $team->getName() . '!');

        return $this->redirectToRoute('team_show', ['id' => $team->getId()]);
    }

    #[Route('/{id}/leave', name: 'team_leave', methods: ['POST'])]
    public function leave(Team $team, Request $request): Response
    {
        $user = $this->getUser();

        // Can't leave if you're the creator
        if ($team->getCreator() === $user) {
            $this->addFlash('error', 'You cannot leave your own team. Delete the team instead.');
            return $this->redirectToRoute('team_show', ['id' => $team->getId()]);
        }

        $member = $this->em->getRepository(TeamMember::class)->findOneBy([
            'team' => $team,
            'user' => $user,
        ]);

        if ($member) {
            $this->em->remove($member);
            $this->em->flush();
            $this->addFlash('success', 'You left the team.');
        }

        return $this->redirectToRoute('team_index');
    }

    #[Route('/{id}/removeMember/{memberId}', name: 'team_remove_member', methods: ['POST'])]
    public function removeMember(Team $team, int $memberId, Request $request): Response
    {
        if ($team->getCreator() !== $this->getUser()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $member = $this->em->getRepository(TeamMember::class)->find($memberId);

        if ($member && $member->getTeam() === $team) {
            $this->em->remove($member);
            $this->em->flush();
            $this->addFlash('success', 'Member removed from team.');
        }

        return $this->redirectToRoute('team_edit', ['id' => $team->getId()]);
>>>>>>> module-user
    }
}
