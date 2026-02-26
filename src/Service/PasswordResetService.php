<?php

namespace App\Service;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Repository\PasswordResetTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

class PasswordResetService
{
    public function __construct(
        private readonly PasswordResetTokenRepository $tokenRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerService $mailerService,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoggerInterface $logger,
        private readonly int $tokenExpirationHours = 1
    ) {}

    /**
     * Generate and send a password reset token to the user
     */
    public function generateAndSendResetToken(User $user): bool
    {
        try {
            // Invalidate any existing tokens for this user
            $this->invalidateExistingTokens($user);

            // Generate secure token
            $token = bin2hex(random_bytes(32));

            // Create and persist token entity
            $tokenEntity = new PasswordResetToken();
            $tokenEntity->setUser($user)
                ->setToken($token)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setExpiresAt(new \DateTimeImmutable("+{$this->tokenExpirationHours} hour"))
                ->setIsUsed(false);

            $this->entityManager->persist($tokenEntity);
            $this->entityManager->flush();

            // Generate reset URL
            $resetUrl = $this->urlGenerator->generate(
                'app_reset_password',
                ['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            // Send email
            $sent = $this->mailerService->sendPasswordResetEmail(
                $user->getEmail(),
                $user->getUsername(),
                $resetUrl
            );

            if ($sent) {
                $this->logger->info("Password reset token generated and email sent for user: {$user->getEmail()}");
            }

            return $sent;
        } catch (\Exception $e) {
            $this->logger->error("Error generating password reset token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate a reset token
     */
    public function validateToken(string $token): ?PasswordResetToken
    {
        try {
            $tokenEntity = $this->tokenRepository->findOneBy(['token' => $token]);

            if (!$tokenEntity) {
                $this->logger->warning("Password reset token not found: {$token}");
                return null;
            }

            // Check if already used
            if ($tokenEntity->isIsUsed()) {
                $this->logger->warning("Password reset token already used: {$token}");
                return null;
            }

            // Check if expired
            if (new \DateTimeImmutable() > $tokenEntity->getExpiresAt()) {
                $this->logger->warning("Password reset token expired: {$token}");
                return null;
            }

            return $tokenEntity;
        } catch (\Exception $e) {
            $this->logger->error("Error validating password reset token: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Invalidate a token after use
     */
    public function invalidateToken(PasswordResetToken $tokenEntity): bool
    {
        try {
            $tokenEntity->setIsUsed(true);
            $this->entityManager->flush();

            $this->logger->info("Password reset token invalidated for user: {$tokenEntity->getUser()->getEmail()}");
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Error invalidating password reset token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidate all existing tokens for a user
     */
    private function invalidateExistingTokens(User $user): void
    {
        try {
            $existingTokens = $this->tokenRepository->findBy(['user' => $user, 'isUsed' => false]);

            foreach ($existingTokens as $token) {
                $token->setIsUsed(true);
            }

            if (!empty($existingTokens)) {
                $this->entityManager->flush();
            }
        } catch (\Exception $e) {
            $this->logger->error("Error invalidating existing tokens: " . $e->getMessage());
        }
    }

    /**
     * Clean up expired tokens (maintenance task)
     */
    public function cleanupExpiredTokens(): int
    {
        try {
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $queryBuilder->delete(PasswordResetToken::class, 'p')
                ->where('p.expiresAt < :now')
                ->setParameter('now', new \DateTimeImmutable())
                ->getQuery()
                ->execute();

            $this->logger->info("Expired password reset tokens cleaned up");
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Error cleaning up expired tokens: " . $e->getMessage());
            return false;
        }
    }
}
