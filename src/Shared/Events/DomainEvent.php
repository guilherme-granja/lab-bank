<?php

namespace Src\Shared\Events;

use Illuminate\Support\Str;

abstract class DomainEvent
{
    public readonly string $eventId;

    public readonly string $occurredAt;

    public readonly string $eventType;

    public readonly ?array $metadata;

    private int $version = 0;

    public function __construct(
        public readonly string $aggregateId,
        public readonly string $aggregateType,
    ) {
        $this->eventId = Str::uuid()->toString();
        $this->occurredAt = now()->toIso8601String();
        $this->eventType = $this->resolveEventType();
        $this->metadata = null;
    }

    private function resolveEventType(): string
    {
        return str(class_basename(static::class))
            ->snake()
            ->replace('_', '.')
            ->toString();
    }

    abstract public function toPayload(): array;

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }
}
