<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Team;
use App\Entity\TeamChatMessage;
use App\Repository\TeamChatMessageRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/team-chat', name: 'team_chat_')]
#[IsGranted('ROLE_USER')]
class TeamChatController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $player = $this->getCurrentPlayer();
        if (!$player) {
            throw $this->createAccessDeniedException('Only players can access team chat.');
        }

        $teams = $player->getTeams()->toArray();
        if (empty($teams)) {
            $this->addFlash('warning', 'You are not in a team yet. Join a team to access chat.');
            return $this->redirectToRoute('team_index');
        }

        usort($teams, fn (Team $a, Team $b) => strcmp((string) $a->getName(), (string) $b->getName()));

        return $this->redirectToRoute('team_chat_room', ['id' => $teams[0]->getId()]);
    }

    #[Route('/{id}', name: 'room', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function room(Team $team, TeamChatMessageRepository $chatRepository): Response
    {
        $player = $this->getCurrentPlayer();
        if (!$player || !$team->getPlayers()->contains($player)) {
            throw $this->createAccessDeniedException('You can only access chat for your teams.');
        }

        $teams = $player->getTeams()->toArray();
        usort($teams, fn (Team $a, Team $b) => strcmp((string) $a->getName(), (string) $b->getName()));

        $messages = $chatRepository->findRecentForTeam($team, 50);
        $messages = array_reverse($messages);

        return $this->render('team/chat.html.twig', [
            'teams' => $teams,
            'current_team' => $team,
            'messages' => $messages,
        ]);
    }

    #[Route('/{id}/messages', name: 'messages', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function messages(Request $request, Team $team, TeamChatMessageRepository $chatRepository): JsonResponse
    {
        $player = $this->getCurrentPlayer();
        if (!$player || !$team->getPlayers()->contains($player)) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $afterId = (int) $request->query->get('after', 0);

        $messages = $afterId > 0
            ? $chatRepository->findAfterIdForTeam($team, $afterId)
            : array_reverse($chatRepository->findRecentForTeam($team, 50));

        $payload = array_map(function (TeamChatMessage $message) use ($player) {
            return [
                'id' => $message->getId(),
                'sender' => $message->getSender()?->getNickname() ?? 'Player',
                'senderId' => $message->getSender()?->getId(),
                'content' => $message->getContent(),
                'createdAt' => $message->getCreatedAt()?->format('H:i'),
                'isMine' => $message->getSender()?->getId() === $player->getId(),
            ];
        }, $messages);

        return $this->json(['messages' => $payload]);
    }

    #[Route('/{id}/send', name: 'send', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function send(Request $request, Team $team, EntityManagerInterface $entityManager): JsonResponse
    {
        $player = $this->getCurrentPlayer();
        if (!$player || !$team->getPlayers()->contains($player)) {
            return $this->json(['error' => 'Access denied'], 403);
        }

        $content = trim((string) $request->request->get('content', ''));
        if ($content === '') {
            return $this->json(['error' => 'Message cannot be empty'], 422);
        }

        if (mb_strlen($content) > 1000) {
            return $this->json(['error' => 'Message too long'], 422);
        }

        $message = new TeamChatMessage();
        $message->setTeam($team);
        $message->setSender($player);
        $message->setContent($content);

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => [
                'id' => $message->getId(),
                'sender' => $player->getNickname() ?? 'Player',
                'senderId' => $player->getId(),
                'content' => $message->getContent(),
                'createdAt' => $message->getCreatedAt()?->format('H:i'),
                'isMine' => true,
            ],
        ]);
    }

    private function getCurrentPlayer(): ?Player
    {
        $user = $this->getUser();

        return $user instanceof Player ? $user : null;
    }
}
