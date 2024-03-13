<?php

namespace App\Enums;

enum DocumentConfidentialityLevelEnum: string
{
    case PUBLIC = 'Public';
    case PRIVATE = 'Private';
    case CONFIDENTIAL = 'Confidential';
}

