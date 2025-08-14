<?php

namespace app\dto;

use app\dto\UserResponse;

class Pagination
{
    private int $page;
    private int $limit;
    private int $totalItems;
    private int $totalPages;
    private array $data;


    public function __construct(int $page, int $limit, int $totalItems, array $data)
    {
        $this->page = $page;
        $this->limit = $limit;
        $this->totalItems = $totalItems;
        $this->totalPages = $limit > 0 ? (int) ceil($totalItems / $limit) : 0;
        $this->data = $data;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;
        return $this;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function setTotalItems(int $totalItems): self
    {
        $this->totalItems = $totalItems;
        return $this;
    }

    public function setTotalPages(int $totalPages): self
    {
        $this->totalPages = $totalPages;
        return $this;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'limit' => $this->limit,
            'total_items' => $this->totalItems,
            'total_pages' => $this->totalPages,
            'data' => $this->data
        ];
    }
}
