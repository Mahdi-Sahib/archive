<?php

namespace App\Enums;

enum DirectionEnum: string
{
    case INBOUND = 'Inbound';
    case OUTBOUND = 'Outbound';
    case INTERNAL = 'Internal';
    case UNDEFINED = 'Undefined';
}

