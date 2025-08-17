<?php
namespace app\controllers;

use app\service\AuthServiceInterface;
use app\dto\LoginRequest;
use app\dto\RegisterRequest;

class AuthController
{
    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function login()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input['email']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }

        $loginRequest = new LoginRequest();
        $loginRequest->email = $input['email'];
        $loginRequest->password = $input['password'];

        $result = $this->authService->login($loginRequest);

        if (!$result) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function register()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input['username']) || empty($input['email']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Username, email and password are required']);
            return;
        }

        $registerRequest = new RegisterRequest();
        $registerRequest->username = $input['username'];
        $registerRequest->email = $input['email'];
        $registerRequest->password = $input['password'];
        $registerRequest->fullName = $input['full_name'] ?? '';
        $registerRequest->phoneNumber = $input['phone_number'] ?? '';

        try {
            $result = $this->authService->register($registerRequest);
            http_response_code(201);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function logout()
    {
        $token = $this->getBearerToken();
        if (!$token) {
            http_response_code(400);
            echo json_encode(['error' => 'Token is required']);
            return;
        }

        $this->authService->logout($token);
        echo json_encode(['message' => 'Logged out successfully']);
    }

    public function refresh()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $refreshToken = $input['refresh_token'] ?? null;

        if (!$refreshToken) {
            http_response_code(400);
            echo json_encode(['error' => 'Refresh token is required']);
            return;
        }

        try {
            $result = $this->authService->refreshToken($refreshToken);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function me()
    {
        $token = $this->getBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token is required']);
            return;
        }

        try {
            $user = $this->authService->getCurrentUser($token);
            echo json_encode($user);
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
        }
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
}