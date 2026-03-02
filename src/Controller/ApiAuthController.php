<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiAuthController extends AbstractController
{
    private JwtService $jwtService;
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(JwtService $jwtService, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->jwtService = $jwtService;
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse([
                'error' => 'Missing email or password'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse([
                'error' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->jwtService->generateToken($user);

        return new JsonResponse([
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ]
        ]);
    }

    #[Route('/validate-token', name: 'api_validate_token', methods: ['POST'])]
    public function validateToken(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['token'])) {
            return new JsonResponse([
                'error' => 'Missing token'
            ], Response::HTTP_BAD_REQUEST);
        }

        $isValid = $this->jwtService->validateToken($data['token']);

        if (!$isValid) {
            return new JsonResponse([
                'error' => 'Invalid or expired token'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $this->jwtService->getPayload($data['token']);

        return new JsonResponse([
            'valid' => true,
            'payload' => $payload
        ]);
    }

    #[Route('/me', name: 'api_me', methods: ['GET'])]
    public function getCurrentUser(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse([
                'error' => 'Missing or invalid Authorization header'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = substr($authHeader, 7);

        if (!$this->jwtService->validateToken($token)) {
            return new JsonResponse([
                'error' => 'Invalid or expired token'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $this->jwtService->getPayload($token);
        $user = $this->em->getRepository(User::class)->find($payload['id']);

        if (!$user) {
            return new JsonResponse([
                'error' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }
}
