<?php

namespace App\DTO;

class TournamentMatchCountDTO
{
    public function __construct(
        public readonly string $tournamentName,
        public readonly int $matchCount,
    ) {}
}
