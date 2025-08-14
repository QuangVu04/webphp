<?php

namespace app\dto;

class UserSearchContext
{
    public ?string $username = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $role = null;
    public ?string $status = null;
    public ?string $fromDate = null;
    public ?string $toDate = null;
    public int $page = 1;
    public int $limit = 5;

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}   

