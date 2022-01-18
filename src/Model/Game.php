<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model;

use DateTime;

final class Game
{
    private string $id;
    private string $title;
    private float $normalPrice;
    private string $thumb;
    private bool $wishlisted;

    public function __construct(
        string $gameID,
        string $title,
        float $normalPrice,
        string $thumbnail,
        bool $wishlisted
    ) {
        $this->id = $gameID;
        $this->title = $title;
        $this->normalPrice = $normalPrice;
        $this->thumb = $thumbnail;
        $this->wishlisted = $wishlisted;
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

    public function title(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function normalPrice(): float
    {
        return $this->normalPrice;
    }

    public function thumb(): string
    {
        return $this->thumb;
    }

    public function wishlisted(): bool
    {
        return $this->wishlisted;
    }
}