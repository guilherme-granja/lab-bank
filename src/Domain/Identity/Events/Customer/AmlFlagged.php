<?php

namespace Src\Domain\Identity\Events\Customer;

use Src\Shared\Events\DomainEvent;

class AmlFlagged extends DomainEvent
{
    public function toPayload(): array
    {
        // TODO: Implement toPayload() method.
    }
}
