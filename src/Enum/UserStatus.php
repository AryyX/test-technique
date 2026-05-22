<?php

namespace App\Enum;

enum UserStatus: string
{
    case Pending = 'pending';
    case Validated = 'validated';
    case Active = 'active';
}
