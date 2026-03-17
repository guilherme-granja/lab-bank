<?php

namespace Src\Domain\Identity\States\KycVerification;

use Src\Domain\Identity\States\KycVerification;

class Rejected extends KycVerification
{
    public static string $name = 'rejected';
}
