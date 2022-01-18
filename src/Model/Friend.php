<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model;

use DateTime;

final class Friend
{
    private string $id;
    private string $username;
    private string $accept_date;

    public function __construct(
        string $id,
        string $username,
        string $accept_date,
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->accept_date = $accept_date;

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

    public function accept_Date(): string
    {
        return $this->accept_date;
    }
}