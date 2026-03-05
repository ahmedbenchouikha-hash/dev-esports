<?php

namespace App\DTO;

class TeamWinsDTO
{
    public function __construct(
        public readonly string $name,
        public readonly int $wins,
    ) {}
}
