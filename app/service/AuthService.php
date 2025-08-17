<?php

namespace app\service;

use app\dto\LoginRequest;
use app\dto\RegisterRequest;
use app\dto\AuthResponse;
use app\models\User;
use app\repository\UserRepository;
use app\utils\JwtUtil;
use UserStatus;

class AuthService implements AuthServiceInterface
{
    private UserRepository $userRepository;
    private JwtUtil $jwtUtil;

    public function __construct(UserRepository $userRepository, JwtUtil $jwtUtil)
    {
        $this->userRepository = $userRepository;
        $this->jwtUtil = $jwtUtil;
    }

    public function login(LoginRequest $request): ?array
    {
        $user = $this->userRepository->findByEmail($request->email);

        if (!$user || !password_verify($request->password, $user->getPassword())) {
            return null;
        }

        // if ($user->getStatus() !== UserStatus::ACTIVE) {
        //     throw new \Exception('Account is not active');
        // }

        $accessToken = $this->jwtUtil->generateAccessToken($user);
        $refreshToken = $this->jwtUtil->generateRefreshToken($user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600, // 1 hour
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'full_name' => $user->getFullName(),
                'status' => $user->getStatus()->value
            ]
        ];
    }

    public function register(RegisterRequest $request): array
    {
        // Check if email already exists
        if ($this->userRepository->findByEmail($request->email)) {
            throw new \Exception('Email already exists');
        }

        // Check if username already exists
        if ($this->userRepository->findByUsername($request->username)) {
            throw new \Exception('Username already exists');
        }

        $user = new User();
        $user->setUsername($request->username);
        $user->setEmail($request->email);
        $user->setPassword(password_hash($request->password, PASSWORD_DEFAULT));
        $user->setFullName($request->fullName);
        $user->setPhoneNumber($request->phoneNumber);
        // $user->setStatus(UserStatus::ACTIVE);

        $createdUser = $this->userRepository->create($user);

        $accessToken = $this->jwtUtil->generateAccessToken($createdUser);
        $refreshToken = $this->jwtUtil->generateRefreshToken($createdUser);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'user' => [
                'id' => $createdUser->getId(),
                'username' => $createdUser->getUsername(),
                'email' => $createdUser->getEmail(),
                'full_name' => $createdUser->getFullName(),
                'status' => $createdUser->getStatus()->value
            ]
        ];
    }

    public function logout(string $token): void
    {
        // Add token to blacklist (implement token blacklist in Redis or database)
        $this->jwtUtil->blacklistToken($token);
    }

    public function refreshToken(string $refreshToken): array
    {
        $payload = $this->jwtUtil->validateRefreshToken($refreshToken);
        if (!$payload) {
            throw new \Exception('Invalid refresh token');
        }

        $user = $this->userRepository->findById($payload['user_id']);
        if (!$user) {
            throw new \Exception('User not found');
        }

        $newAccessToken = $this->jwtUtil->generateAccessToken($user);
        $newRefreshToken = $this->jwtUtil->generateRefreshToken($user);

        return [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ];
    }

    public function getCurrentUser(string $token): array
    {
        $payload = $this->jwtUtil->validateAccessToken($token);
        if (!$payload) {
            throw new \Exception('Invalid token');
        }

        $user = $this->userRepository->findById($payload['user_id']);
        if (!$user) {
            throw new \Exception('User not found');
        }

        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'full_name' => $user->getFullName(),
            'phone_number' => $user->getPhoneNumber(),
            'status' => $user->getStatus()->value
        ];
    }
}
