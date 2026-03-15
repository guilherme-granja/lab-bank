<?php

namespace Src\Domain\Identity\States\Customer;

use Src\Domain\Identity\States\Status;

class PendingKyc extends Status
{
    public static string $name = 'pending_kyc';
}
