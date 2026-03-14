<?php

namespace Src\Domain\Identity\States\Kyc;

use Src\Domain\Identity\States\KycStatus;

class Pending extends KycStatus
{
    public static string $name = 'pending';
}
