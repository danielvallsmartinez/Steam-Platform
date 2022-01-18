<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model;

use DateTime;

final class User
{
    private int $id;
    private string $email;
    private string $password;
    private string $username;
    private string $birthday;
    private string $phone;
    private DateTime $createdAt;

    public function __construct(
        string $email,
        string $password,
        string $username,
        string $birthday,
        string $phone,
        DateTime $createdAt
    ) {
        $this->email = $email;
        $this->password = $password;
        $this->username = $username;
        $this->birthday = $birthday;
        $this->phone = $phone;
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

    public function email(): string
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function birthday(): string
    {
        return $this->birthday;
    }
}