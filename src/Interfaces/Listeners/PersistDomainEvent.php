<?php

namespace Src\Interfaces\Listeners;

use Src\Infrastructure\EventStore\EventStoreRepository;
use Throwable;

readonly class PersistDomainEvent
{
    public function __construct(
        private EventStoreRepository $eventStore,
    ) {}

    public function handle($event): void
    {
        $this->eventStore->storeAll([$event->domainEvent]);
    }
}
