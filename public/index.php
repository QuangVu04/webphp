<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

use app\core\Router;
use app\service\UserService;
use app\service\AuthService;
use app\controllers\UserController;
use app\controllers\AuthController;
use app\repository\Impl\UserRepositoryImpl;
use app\middleware\AuthMiddleWare;
use app\utils\JwtUtil;

$router = new Router();
$jwt = new JwtUtil();
$userRepo = new UserRepositoryImpl($mysqli);
$authMiddleware = new AuthMiddleWare($jwt);
$authService = new AuthService($userRepo, $jwt);
$authController = new AuthController($authService);
$userService = new UserService($userRepo);
$userController = new UserController($userService);

header('Content-Type: application/json');

$router->add('GET', '/users', [$userController, 'list']);
$router->add('POST', '/users/search', [$userController, 'search']);
$router->add('POST', '/users', [$userController, 'create']);
$router->add('PUT', '/users/{id}', [$userController, 'update']);
$router->add('DELETE', '/users/{id}', [$userController, 'delete']);

$router->add('POST', '/api/auth/login', function () use ($authController) {
    $authController->login();
});

$router->add('POST', '/api/auth/register', function () use ($authController) {
    $authController->register();
});

$router->add('POST', '/api/auth/refresh', function () use ($authController) {
    $authController->refresh();
});

// Protected routes (require authentication)
$router->add('POST', '/api/auth/logout', function () use ($authController, $authMiddleware) {
    if ($authMiddleware->handle()) {
        $authController->logout();
    }
});

$router->add('GET', '/api/auth/me', function () use ($authController, $authMiddleware) {
    if ($authMiddleware->handle()) {
        $authController->me();
    }
});

$router->add('GET', '/api/users', function () use ($userController, $authMiddleware) {
    if ($authMiddleware->handle()) {
        $userController->list();
    }
});

// Dispatch
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
