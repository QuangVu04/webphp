<?php

namespace app\repository;

use app\models\User;
use app\dto\UserSearchContext;

interface UserRepository
{
    public function create(User $user): User;
    public function findById(int $id): ?User;
    public function findAll(int $page, int $limit): array;
    public function findByEmail(string $email): ?User;
    public function findByUsername(string $username): ?User; 
    public function searchUsers(UserSearchContext $context): array;
    public function update(User $user): User;
    public function delete(int $id): bool;

}


?>