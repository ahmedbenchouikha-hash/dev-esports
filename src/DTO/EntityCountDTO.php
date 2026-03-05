<?php

namespace App\DTO;

class EntityCountDTO
{
    public function __construct(
        public readonly int $count,
    ) {}
}
