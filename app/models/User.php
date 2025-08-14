<?php

namespace app\models;

use UserStatus;

class User extends BaseEntity {
    protected ?string $username = null;
    protected ?string $email = null;
    protected ?string $password = null;
    protected ?string $full_name = null;
    protected ?string $avatar = null;
    protected ?string $phone_number = null;
    protected ?UserStatus $status  = null;


    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->full_name;
    }
    public function setFullName(string $full_name): self
    {
        $this->full_name = $full_name;
        return $this;
    }
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }
    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;
        return $this;
    }
    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }
    public function setPhoneNumber(string $phone_number): self
    {
        $this->phone_number = $phone_number;
        return $this;
    }
    public function getStatus(): ?UserStatus
    {
        return $this->status;
    }
    public function setStatus(UserStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function toArray(): array
{
    return [
        'id' => $this->getId(),
        'username' => $this->getUsername(),
        'email' => $this->getEmail(),
        'full_name' => $this->getFullName(),
        'avatar' => $this->getAvatar(),
        'phone_number' => $this->getPhoneNumber(),
        'status' => $this->getStatus(),
    ];
}
}


?>