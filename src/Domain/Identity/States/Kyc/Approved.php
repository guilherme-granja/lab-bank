<?php

namespace Src\Domain\Identity\States\Kyc;

use Src\Domain\Identity\States\KycStatus;

class Approved extends KycStatus
{
    public static string $name = 'approved';
}
