<?php

namespace Src\Interfaces\Listeners\Accounts;

use Src\Interfaces\Events\Identity\KycWasApproved;
use Src\Interfaces\Jobs\Accounts\OpenAccountJob;

class OpenAccountOnKycApprovedListener
{
    public function handle(KycWasApproved $event): void
    {
        OpenAccountJob::dispatch($event->domainEvent->aggregateId);
    }
}
