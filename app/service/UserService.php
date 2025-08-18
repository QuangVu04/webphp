<?php

namespace app\service;

use app\dto\Pagination;
use app\models\User;
use app\repository\UserRepository;
use app\dto\UserSearchContext;
use app\enums\UserStatus;

class UserService implements UserServiceInterface
{
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function createUser(array $data): User
    {

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setPassword($data['password']);
        $user->setFullName($data['full_name'] ?? '');
        $user->setAvatar($data['avatar'] ?? '');
        $user->setPhoneNumber($data['phone_number'] ?? '');
        $user->setStatus($data['status'] ?? 'active');

        return $this->userRepo->create($user);
    }

    public function getAllUsers(int $page, int $limit): ?Pagination
    {
        $result = $this->userRepo->findAll($page, $limit);
        return new Pagination(
            $page,
            $limit,
            $result['totalItems'],
            $result['data']
        );
    }

    public function searchUsers(UserSearchContext $context): ?Pagination
    {
        $result = $this->userRepo->searchUsers($context);
        return new Pagination(
            $context->getPage(),
            $context->getLimit(),
            $result['totalItems'],
            $result['data']
        );
    }


    public function updateUser(int $id, array $data): ?User
    {
        $user = $this->userRepo->findById($id);
        if (!$user) {
            return null;
        }

        $user->setUsername($data['username'] ?? $user->getUsername());
        $user->setEmail($data['email'] ?? $user->getEmail());

        if (!empty($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));
        }

        $user->setFullName($data['full_name'] ?? $user->getFullName());
        $user->setAvatar($data['avatar'] ?? $user->getAvatar());
        $user->setPhoneNumber($data['phone_number'] ?? $user->getPhoneNumber());
        if (!empty($data['status']) && in_array($data['status'], array_column(UserStatus::cases(), 'value'))) {
            $user->setStatus(UserStatus::from($data['status']));
        }
        return $this->userRepo->update($user);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepo->delete($id);
    }
}
