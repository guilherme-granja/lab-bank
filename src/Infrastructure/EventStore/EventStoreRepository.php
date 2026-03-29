<?php

namespace Src\Infrastructure\EventStore;

use Illuminate\Support\Facades\DB;
use Src\Shared\Events\DomainEvent;
use Throwable;

class EventStoreRepository
{
    private array $aggregateVersions = [];

    public function storeAll(array $events): void
    {
        $this->setAggregateVersions($events);

        DB::connection('identity')
            ->table('domain_events')
            ->insert(
                collect($events)->map(function (DomainEvent $domainEvent) {
                    return $this->mountDomainEventData($domainEvent);
                })->toArray()
            );
    }

    private function setAggregateVersions(array $events): void
    {
        $aggregateIds = collect($events)
            ->pluck('aggregateId')
            ->unique()
            ->values()
            ->toArray();

        $this->aggregateVersions = DB::connection('identity')
            ->table('domain_events')
            ->whereIn('aggregate_id', $aggregateIds)
            ->groupBy('aggregate_id')
            ->pluck(DB::raw('MAX(aggregate_version)'), 'aggregate_id')
            ->toArray();
    }

    private function mountDomainEventData(DomainEvent $domainEvent): array
    {
        $current = $this->aggregateVersions[$domainEvent->aggregateId] ?? 0;
        $next = $current + 1;
        $this->aggregateVersions[$domainEvent->aggregateId] = $next;

        return [
            'id' => $domainEvent->eventId,
            'aggregate_type' => $domainEvent->aggregateType,
            'aggregate_id' => $domainEvent->aggregateId,
            'aggregate_version' => $next,
            'event_type' => $domainEvent->eventType,
            'payload' => json_encode($domainEvent->toPayload(), JSON_THROW_ON_ERROR),
            'metadata' => json_encode($domainEvent->metadata, JSON_THROW_ON_ERROR),
            'occurred_at' => $domainEvent->occurredAt,
        ];
    }
}
