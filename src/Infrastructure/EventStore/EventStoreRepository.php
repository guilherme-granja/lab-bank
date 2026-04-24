<?php

namespace Src\Infrastructure\EventStore;

use Illuminate\Support\Facades\DB;
use Src\Domain\Accounts\Models\Account;
use Src\Domain\Identity\Models\Customer;
use Src\Shared\Events\DomainEvent;

class EventStoreRepository
{
    private array $aggregateVersions = [];

    private string $connection;

    public function storeAll(array $events): void
    {
        $this->resolveConnection($events[0]->aggregateType);

        $this->setAggregateVersions($events);

        DB::connection($this->connection)
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

        $this->aggregateVersions = DB::connection($this->connection)
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

    private function resolveConnection(string $aggregateType): void
    {
        $this->connection = match ($aggregateType) {
            class_basename(Customer::class) => 'identity',
            class_basename(Account::class) => 'accounts',
            default => throw new \InvalidArgumentException("Unknown aggregate type: {$aggregateType}"),
        };
    }
}
