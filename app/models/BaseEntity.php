<?php

namespace app\models;

class BaseEntity
{
    protected ?int $id;
    protected ?\DateTime $created_at;
    protected ?\DateTime $updated_at;


    public function __construct()
    {
        $this->id = null;
        $this->created_at = new \DateTime();
        $this->updated_at = null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updated_at = $updatedAt;
        return $this;
    }

    public function touch(): self
    {
        $this->updated_at = new \DateTime();
        return $this;
    }



}


?>