<?php

namespace Src\Shared\Traits;

use Src\Shared\Events\DomainEvent;

trait AggregateRoot
{
    private array $domainEvents = [];
    private int $aggregateVersion = 0;

    protected function recordEvent(DomainEvent $domainEvent): void
    {
        $this->aggregateVersion++;
        $domainEvent->setVersion($this->aggregateVersion);
        $this->domainEvents[] = $domainEvent;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    public function getAggregateVersion(): int
    {
        return $this->aggregateVersion;
    }
}
