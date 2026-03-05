<?php

namespace App\DTO;

class GameStatusCountDTO
{
    public function __construct(
        public readonly string $status,
        public readonly int $count,
    ) {}
}
