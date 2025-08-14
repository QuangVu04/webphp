<?php

namespace app\controllers;

use app\service\UserService;
use app\dto\UserSearchContext;
use app\service\UserServiceInterface;

class UserController
{
    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function list()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        $users = $this->userService->getAllUsers($page, $limit);
        header('Content-Type: application/json');
        echo json_encode($users->toArray());
    }

    public function search()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $context = new UserSearchContext();
        $context->username = $input['username'] ?? null;
        $context->email = $input['email'] ?? null;
        $context->phone = $input['phone'] ?? null;
        $context->role = $input['role'] ?? null;
        $context->status = $input['status'] ?? null;
        $context->fromDate = $input['fromDate'] ?? null;
        $context->toDate = $input['toDate'] ?? null;
        $context->page = isset($input['page']) ? (int)$input['page'] : 1;
        $context->limit = isset($input['limit']) ? (int)$input['limit'] : 5;

        $result = $this->userService->searchUsers($context);

        header('Content-Type: application/json');
        echo json_encode($result->toArray());
    }

    public function create()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $user = $this->userService->createUser($data);
        http_response_code(201);
        echo json_encode($user);
    }

    public function update(int $id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $user = $this->userService->updateUser($id, $data);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }

        echo json_encode($user);
    }

    public function delete(int $id)
    {
        $success = $this->userService->deleteUser($id);

        if (!$success) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found or could not be deleted']);
            return;
        }

        http_response_code(204);
    }
}
