<?php
namespace app\service;

use app\dto\Pagination;
use app\models\User;
use app\dto\UserSearchContext;


interface UserServiceInterface
{
    public function createUser(array $data): User;

    public function getAllUsers(int $page, int $limit): ?Pagination;

    public function searchUsers(UserSearchContext $context): ?Pagination;

    public function updateUser(int $id, array $data): ?User;

    public function deleteUser(int $id): bool;
}
?>