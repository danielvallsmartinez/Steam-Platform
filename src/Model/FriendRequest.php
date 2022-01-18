<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model;

use DateTime;

final class FriendRequest
{
    private string $id;
    private string $username;
    private DateTime $created_at;

    public function __construct(
        string $id,
        string $username,
        DateTime $created_at,
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->created_at = $created_at;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function created_At(): DateTime
    {
        return $this->created_at;
    }
}