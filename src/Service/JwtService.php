<?php

namespace App\Service;

use App\Entity\User;

class JwtService
{
    private string $secret;

    public function __construct(string $jwtSecret)
    {
        $this->secret = $jwtSecret;
    }

    /**
     * Generate a JWT token for a user
     */
    public function generateToken(User $user): string
    {
        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        
        $now = time();
        $expiresAt = $now + (24 * 60 * 60); // 24 hours expiration
        
        $payload = [
            'iat' => $now,
            'exp' => $expiresAt,
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ];
        
        $encodedPayload = $this->base64UrlEncode(json_encode($payload));
        
        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', "$header.$encodedPayload", $this->secret, true)
        );
        
        return "$header.$encodedPayload.$signature";
    }

    /**
     * Validate a JWT token
     */
    public function validateToken(string $token): bool
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        [$header, $payload, $signature] = $parts;
        
        $expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", $this->secret, true)
        );
        
        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }
        
        $decodedPayload = json_decode($this->base64UrlDecode($payload), true);
        
        if ($decodedPayload['exp'] < time()) {
            return false;
        }
        
        return true;
    }

    /**
     * Get the payload from a token
     */
    public function getPayload(string $token): ?array
    {
        if (!$this->validateToken($token)) {
            return null;
        }
        
        $parts = explode('.', $token);
        return json_decode($this->base64UrlDecode($parts[1]), true);
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 4 - strlen($data) % 4));
    }
}
