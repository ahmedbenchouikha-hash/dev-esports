<?php

namespace App\Enum;

enum StatutPunition: string
{
    case ACTIF = 'ACTIF';
    case INACTIF = 'INACTIF';
    case EXPIRE = 'EXPIRE';
}
