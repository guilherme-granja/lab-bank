<?php

namespace Src\Domain\Accounts\Enums;

use Src\Shared\Traits\Enum\ToArray;

enum AccountTypeEnum: string
{
    use ToArray;

    case Checking = 'checking';
    case Savings = 'savings';
}
