<?php

namespace App\Enum;

enum CausePerte: string
{
    case Mortalite    = 'Mortalite';
    case Dessechement = 'Dessechement';
    case Insectes     = 'Insectes';
    case Champignons  = 'Champignons';
    case Autres       = 'Autres';
}
