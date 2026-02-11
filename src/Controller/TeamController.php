<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\TeamMember;
use App\Entity\User;
use App\Form\CreateTeamType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    }
}
