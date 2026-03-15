<?php

namespace Src\Infrastructure\EventStore;

use Illuminate\Support\Facades\DB;
use Src\Shared\Events\DomainEvent;
use Throwable;

class EventStoreRepository
{
    /**
     * @throws Throwable
     */
    public function storeAll(array $events): void
    {
        DB::connection('identity')
            ->table('domain_events')
            ->insert(
                collect($events)->map(static fn (DomainEvent $domainEvent) => [
                    'id' => $domainEvent->eventId,
                    'aggregate_type' => $domainEvent->aggregateType,
                    'aggregate_id' => $domainEvent->aggregateId,
                    'aggregate_version' => $domainEvent->getVersion(),
                    'event_type' => $domainEvent->eventType,
                    'payload' => json_encode($domainEvent->toPayload(), JSON_THROW_ON_ERROR),
                    'metadata' => json_encode($domainEvent->metadata, JSON_THROW_ON_ERROR),
                    'occurred_at' => $domainEvent->occurredAt,
                ])->toArray()
            );
    }
}
