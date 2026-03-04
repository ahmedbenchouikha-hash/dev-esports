<?php

namespace App\Enum;

enum StatutPunition: string
{
    case ACTIF = 'ACTIF';
    case INACTIF = 'INACTIF';
    case EXPIRE = 'EXPIRE';

    public static function choices(): array
    {
        return [
            'Actif' => self::ACTIF->value,
            'Inactif' => self::INACTIF->value,
            'Expiré' => self::EXPIRE->value,
        ];
    }
}
