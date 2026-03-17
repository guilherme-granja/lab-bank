<?php

namespace Src\Domain\Identity\States\Customer;

use Src\Domain\Identity\States\Status;

class Blocked extends Status
{
    public static string $name = 'blocked';
}
