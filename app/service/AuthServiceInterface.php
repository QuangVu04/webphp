<?php
namespace app\service;

use app\dto\LoginRequest;
use app\dto\RegisterRequest;

interface AuthServiceInterface
{
    public function login(LoginRequest $request): ?array;
    public function register(RegisterRequest $request): array;
    public function logout(string $token): void;
    public function refreshToken(string $refreshToken): array;
    public function getCurrentUser(string $token): array;
}

?>