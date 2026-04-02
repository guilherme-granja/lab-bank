<?php

use Src\Shared\Events\DomainEvent;
use Src\Shared\Traits\AggregateRoot;

beforeEach(function () {
    $this->aggregate = new class
    {
        use AggregateRoot;
    };
});

describe('AggregateRoot trait', function () {
    it('starts with no recorded events', function () {
        expect($this->aggregate->pullDomainEvents())->toBeEmpty();
    });

    it('starts with aggregate version zero', function () {
        expect($this->aggregate->getAggregateVersion())->toBe(0);
    });

    it('records a domain event', function () {
        $event = makeFakeDomainEvent();

        invokeSut($this->aggregate, 'recordEvent', $event);

        expect($this->aggregate->pullDomainEvents())->toHaveCount(1);
    });

    it('increments aggregate version for each recorded event', function () {
        invokeSut($this->aggregate, 'recordEvent', makeFakeDomainEvent());
        invokeSut($this->aggregate, 'recordEvent', makeFakeDomainEvent());

        expect($this->aggregate->getAggregateVersion())->toBe(2);
    });

    it('sets version on each recorded event sequentially', function () {
        $first = makeFakeDomainEvent();
        $second = makeFakeDomainEvent();

        invokeSut($this->aggregate, 'recordEvent', $first);
        invokeSut($this->aggregate, 'recordEvent', $second);

        $this->aggregate->pullDomainEvents();

        expect($first->getVersion())->toBe(1);
        expect($second->getVersion())->toBe(2);
    });

    it('clears events after pullDomainEvents', function () {
        invokeSut($this->aggregate, 'recordEvent', makeFakeDomainEvent());

        $this->aggregate->pullDomainEvents();

        expect($this->aggregate->pullDomainEvents())->toBeEmpty();
    });

    it('returns all recorded events in order', function () {
        $first = makeFakeDomainEvent('first');
        $second = makeFakeDomainEvent('second');

        invokeSut($this->aggregate, 'recordEvent', $first);
        invokeSut($this->aggregate, 'recordEvent', $second);

        $events = $this->aggregate->pullDomainEvents();

        expect($events)->toHaveCount(2);
        expect($events[0]->aggregateId)->toBe('first');
        expect($events[1]->aggregateId)->toBe('second');
    });
});

function makeFakeDomainEvent(string $aggregateId = 'aggregate-id-1'): DomainEvent
{
    return new class($aggregateId, 'FakeAggregate') extends DomainEvent
    {
        public function toPayload(): array
        {
            return ['fake' => true];
        }
    };
}

function invokeSut(object $object, string $method, mixed ...$args): mixed
{
    $reflection = new ReflectionMethod($object, $method);
    $reflection->setAccessible(true);

    return $reflection->invoke($object, ...$args);
}
