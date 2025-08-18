<?php

namespace app\repository\Impl;

use app\repository\UserRepository;
use app\dto\UserResponse;
use app\models\User;
use app\dto\UserSearchContext;
use app\enums\UserStatus;


class UserRepositoryImpl implements UserRepository
{
    private \mysqli $mysqli;

    public function __construct(\mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function create(User $user): User
    {
        $stmt = $this->mysqli->prepare("INSERT INTO users (username, email, password, full_name, avatar, phone_number, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param(
            "sssssss",
            $user->getUsername(),
            $user->getEmail(),
            $user->getPassword(),
            $user->getFullName(),
            $user->getAvatar(),
            $user->getPhoneNumber(),
            $user->getStatus()
        );
        $stmt->execute();
        $user->setId($this->mysqli->insert_id);
        return $user;
    }



    public function findAll(int $page, int $limit): array
    {

        $offset = ($page - 1) * $limit;
        $result = $this->mysqli->query("SELECT n.id,n.username, n.email, n.full_name, n.phone_number, n.status, r.role_name, n.created_at,
                                        COUNT(*) OVER() as totalItems
                                        FROM users n
                                        LEFT JOIN user_roles ur ON n.id = ur.user_id
                                        LEFT JOIN roles r ON ur.role_id = r.role_id
                                        LIMIT $limit OFFSET $offset");
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $user = new UserResponse();
            $totalItems = (int) $row['totalItems'];
            $user->setId($row['id']);
            $user->setUsername($row['username']);
            $user->setEmail($row['email']);
            $user->setFullName($row['full_name']);
            $user->setPhoneNumber($row['phone_number']);
            $user->setStatus($row['status']);
            $user->setRole($row['role_name']);
            $user->setCreatedAt(new \DateTime($row['created_at']));

            $users[] = $user->toArray();
        }
        $totalPages = ceil($totalItems / $limit);
        return [
            'data' => $users,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages
        ];
    }

    public function searchUsers(UserSearchContext $context): array
    {
        $sql = "SELECT n.username, n.email, n.full_name, n.phone_number, n.status, r.role_name,
                   n.created_at, COUNT(*) OVER() as totalItems
            FROM users n
            LEFT JOIN user_roles ur ON n.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.role_id
            WHERE 1=1";

        $params = [];
        $types = "";

        // Tạo điều kiện tìm kiếm linh hoạt
        if (!empty($context->username)) {
            $sql .= " AND n.username LIKE ?";
            $params[] = "%" . $context->username . "%";
            $types .= "s";
        }
        if (!empty($context->email)) {
            $sql .= " AND n.email LIKE ?";
            $params[] = "%" . $context->email . "%";
            $types .= "s";
        }
        if (!empty($context->phone)) {
            $sql .= " AND n.phone_number LIKE ?";
            $params[] = "%" . $context->phone . "%";
            $types .= "s";
        }
        if (!empty($context->role)) {
            $sql .= " AND r.role_name = ?";
            $params[] = $context->role;
            $types .= "s";
        }
        if (!empty($context->status)) {
            $sql .= " AND n.status = ?";
            $params[] = $context->status;
            $types .= "s";
        }
        if (!empty($context->fromDate)) {
            $sql .= " AND n.created_at >= ?";
            $params[] = $context->fromDate;
            $types .= "s";
        }
        if (!empty($context->toDate)) {
            $sql .= " AND n.created_at <= ?";
            $params[] = $context->toDate;
            $types .= "s";
        }

        // Pagination
        $offset = ($context->page - 1) * $context->limit;
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $context->limit;
        $params[] = $offset;
        $types .= "ii";

        // Chuẩn bị statement
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();



        $result = $stmt->get_result();
        $users = [];
        $totalItems = 0;

        while ($row = $result->fetch_assoc()) {
            $totalItems = (int)$row['totalItems'];
            $user = new UserResponse();
            $user->setUsername($row['username']);
            $user->setEmail($row['email']);
            $user->setFullName($row['full_name']);
            $user->setPhoneNumber($row['phone_number']);
            $user->setStatus($row['status']);
            $user->setRole($row['role_name']);
            $user->setCreatedAt(new \DateTime($row['created_at']));
            $users[] = $user->toArray();
        }


        $totalPages = ceil($totalItems / $context->limit);

        return [
            'data' => $users,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages
        ];
    }


    public function update(User $user): User
    {
        $stmt = $this->mysqli->prepare("UPDATE users SET username = ?, email = ?, password = ?, full_name = ?, avatar = ?, phone_number = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $status = $user->getStatus()?->value;

        $stmt->bind_param(
            "sssssssi",
            $user->getUsername(),
            $user->getEmail(),
            $user->getPassword(),
            $user->getFullName(),
            $user->getAvatar(),
            $user->getPhoneNumber(),
            $status,
            $user->getId()
        );
        $stmt->execute();
        return $user;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->mysqli->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->mysqli->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return null;
        }
        $row = $result->fetch_assoc();
        $user = new User();
        $user->setId($row['id']);
        $user->setUsername($row['username']);
        $user->setEmail($row['email']);
        $user->setPassword($row['password']);
        $user->setFullName($row['full_name']);
        $user->setAvatar($row['avatar']);
        $user->setPhoneNumber($row['phone_number']);
        $user->setStatus(UserStatus::from($row['status']));
        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->mysqli->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        $row = $result->fetch_assoc();
        $user = new User();
        $user->setId($row['id']);
        $user->setUsername($row['username']);
        $user->setEmail($row['email']);
        $user->setPassword($row['password']);
        $user->setFullName($row['full_name']);
        $user->setAvatar($row['avatar']);
        $user->setPhoneNumber($row['phone_number']);
        // $user->setStatus(UserStatus::from($row['status']));

        return $user;
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->mysqli->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        $row = $result->fetch_assoc();
        $user = new User();
        $user->setId($row['id']);
        $user->setUsername($row['username']);
        $user->setEmail($row['email']);
        $user->setPassword($row['password']);
        $user->setFullName($row['full_name']);
        $user->setAvatar($row['avatar']);
        $user->setPhoneNumber($row['phone_number']);
        $user->setStatus(UserStatus::from($row['status']));

        return $user;
    }
}
