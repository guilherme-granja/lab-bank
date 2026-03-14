<?php

namespace Src\Domain\Identity\States\Kyc;

use Src\Domain\Identity\States\KycStatus;

class Rejected extends KycStatus
{
    public static string $name = 'rejected';
}
