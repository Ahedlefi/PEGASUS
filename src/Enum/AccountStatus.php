<?php

namespace App\Enum;

enum AccountStatus: string
{
    case ACTIVE = 'ACTIVE';
    case PENDING = 'PENDING';
    case BANNED = 'BANNED';
}
