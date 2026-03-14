<?php

namespace Src\Domain\Identity\States\Kyc;

use Src\Domain\Identity\States\KycStatus;

class Processing extends KycStatus
{
    public static string $name = 'processing';
}
