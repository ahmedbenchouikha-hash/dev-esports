<?php

namespace App\Enum;

enum ReclamationStatus: string
{
    case EN_COURS = 'EN_COURS';
    case RESOLU = 'RESOLU';
    case REJETE = 'REJETE';
    
}
