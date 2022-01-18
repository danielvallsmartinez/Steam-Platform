<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model;

use DateTime;

final class Search
{
    private int $id;
    private string $userId;
    private string $search;
    private DateTime $createdAt;

    public function __construct(
        string $userId,
        string $search,
        DateTime $createdAt,
    ) {
        $this->userId = $userId;
        $this->search = $search;
        $this->createdAt = $createdAt;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function search(): string
    {
        return $this->search;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }
}