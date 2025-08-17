<?php

namespace app\utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use app\models\User;

class JwtUtil
{
    private string $secretKey;
    private string $refreshSecretKey;
    private int $accessTokenExpiry = 3600;
    private int $refreshTokenExpiry = 604800;
    private array $blacklistedTokens = []; // In production, use Redis or database

    public function __construct()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->secretKey = $_ENV['JWT_SECRET_KEY'];
        $this->refreshSecretKey = $_ENV['JWT_REFRESH_SECRET_KEY'];
    }

    public function generateAccessToken(User $user): string
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + $this->accessTokenExpiry,
            'user_id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'type' => 'access'
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function generateRefreshToken(User $user): string
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + $this->refreshTokenExpiry,
            'user_id' => $user->getId(),
            'type' => 'refresh'
        ];

        return JWT::encode($payload, $this->refreshSecretKey, 'HS256');
    }

    public function validateAccessToken(string $token): ?array
    {
        try {
            if ($this->isBlacklisted($token)) {
                return null;
            }

            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            $payload = (array) $decoded;

            if ($payload['type'] !== 'access') {
                return null;
            }

            return $payload;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function validateRefreshToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->refreshSecretKey, 'HS256'));
            $payload = (array) $decoded;

            if ($payload['type'] !== 'refresh') {
                return null;
            }

            return $payload;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function blacklistToken(string $token): void
    {
        $this->blacklistedTokens[] = $token;
    }

    private function isBlacklisted(string $token): bool
    {
        return in_array($token, $this->blacklistedTokens);
    }
}
