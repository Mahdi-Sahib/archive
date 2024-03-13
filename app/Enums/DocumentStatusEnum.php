<?php

namespace App\Enums;

enum DocumentStatusEnum: string
{
    case PENDING = 'Pending';
    case VERIFIED = 'Verified';
    case UNDER_REVIEW = 'Under Review';
    case REJECTED = 'Rejected';
    case EXPIRED = 'Expired';
    case ARCHIVED = 'Archived';
}
