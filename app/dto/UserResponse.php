<?php
    namespace app\dto;

use DateTime;

    class UserResponse
    {
        private ?int $id;
        private string $username;
        private string $email;
        private string $fullName;
        private string $phoneNumber;
        private ?string $status;
        private string $role;
        private ?DateTime $createdAt;

        public function __construct()
        {
            $this->id = null;
            $this->username = '';
            $this->email = '';
            $this->fullName = '';
            $this->phoneNumber = '';
            $this->status = null;
            $this->role = '';
            $this->createdAt = new DateTime();
        }

        public function setId(int $id): void {
            $this->id = $id;
        }

        public function setUsername(string $username): void
        {
            $this->username = $username;
        }
        public function setEmail(string $email): void
        {
            $this->email = $email;
        }

        public function setPhoneNumber(string $phoneNumber): void
        {
            $this->phoneNumber = $phoneNumber;
        }

        public function setFullName(string $fullName): void
        {
            $this->fullName = $fullName;
        }
        public function setStatus(?string $status): void
        {
            $this->status = $status;
        }
        public function setRole(?string $role): void
        {
            $this->role = $role ?? 'NO ROLE';
        }

        public function setCreatedAt(DateTime $createdAt): void
        {
            $this->createdAt = $createdAt;
        }

        public function toArray(): array
        {
            return [
                'id' => $this->id,
                'username' => $this->username,
                'email' => $this->email,
                'full_name' => $this->fullName,
                'phone_number' => $this->phoneNumber,
                'status' => $this->status,
                'role' => $this->role,
                'created_at' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null
            ];
        }
    }

?>