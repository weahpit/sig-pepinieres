<?php

namespace App\Enum;

enum EtatSanitaire: string
{
    case Sain     = 'Sain';
    case Attaque  = 'Attaque';
    case Malade   = 'Malade';
    case Faible   = 'Faible';
    case Mort     = 'Mort';
}
