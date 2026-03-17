<?php

namespace Src\Domain\Identity\States\Customer;

use Src\Domain\Identity\States\Status;

class Closed extends Status
{
    public static string $name = 'closed';
}
