<?php

namespace app\dto;

class RegisterRequest
{
    public string $username;
    public string $email;
    public string $password;
    public string $fullName = '';
    public string $phoneNumber = '';
}
