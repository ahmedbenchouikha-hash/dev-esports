<?php

namespace App\Enum;

enum StatutPunition: string
{
    case BANNED_FROM_THIS_TOURNAMENT = 'banned from this tournament';
    case BANNED_FROM_THIS_MATCH = 'banned from this match';
    case BANNED_FROM_THIS_GAME = 'banned from this game';

    public static function choices(): array
    {
        return [
            'Banned from this tournament' => self::BANNED_FROM_THIS_TOURNAMENT->value,
            'Banned from this match' => self::BANNED_FROM_THIS_MATCH->value,
            'Banned from this game' => self::BANNED_FROM_THIS_GAME->value,
        ];
    }

    public static function values(): array
    {
        return array_map(static fn(self $case): string => $case->value, self::cases());
    }
}
