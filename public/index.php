<?php

require __DIR__ . '/../app/models/BaseEntity.php';  
require __DIR__ . '/../app/models/User.php';
require __DIR__ . '/../app/repository/UserRepository.php'; 
require __DIR__ . '/../app/repository/Impl/UserRepositoryImpl.php';
require __DIR__ . '/../app/service/UserServiceInterface.php';
require __DIR__ . '/../app/service/UserService.php';
require __DIR__ . '/../app/controllers/UserController.php';
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../app/core/Router.php';
require __DIR__ . '/../app/dto/UserResponse.php';
require __DIR__ . '/../app/dto/Pagination.php';
require __DIR__ . '/../app/dto/UserSearchContext.php';


$router = new \app\core\Router();
$userRepo = new \app\repository\UserRepositoryImpl($mysqli);
$userService = new \app\service\UserService($userRepo);
$userController = new \app\controllers\UserController($userService);

header('Content-Type: application/json');

$router->add('GET', '/users', [$userController, 'list']);
$router->add('POST', '/users/search', [$userController, 'search']);
$router->add('POST', '/users', [$userController, 'create']);
$router->add('PUT', '/users/{id}', [$userController, 'update']);
$router->add('DELETE', '/users/{id}', [$userController, 'delete']);

// Dispatch
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);