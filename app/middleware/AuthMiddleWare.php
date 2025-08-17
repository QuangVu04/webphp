<?php

namespace app\middleware;

use app\utils\JwtUtil;

class AuthMiddleWare
{
    private JwtUtil $jwtUtil;

    public function __construct()
    {
        $this->jwtUtil = new JwtUtil();
    }

    public function handle(): bool
    {
        $token = $this->getBearerToken();

        if (!$token) {
            $this->unauthorizedResponse('Token is required');
            return false;
        }

        $payload = $this->jwtUtil->validateAccessToken($token);

        if (!$payload) {
            $this->unauthorizedResponse('Invalid or expired token');
            return false;
        }

        // Store user info in global variable or request context
        $_SESSION['current_user'] = $payload;
        return true;
    }

    private function getBearerToken(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function unauthorizedResponse(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }
}
