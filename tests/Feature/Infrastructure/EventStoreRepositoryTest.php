<?php

use Database\Factories\AccountFactory;
use Database\Factories\CustomerFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Src\Domain\Accounts\Events\Account\AccountOpenedEvent;
use Src\Domain\Accounts\Models\Account;
use Src\Domain\Identity\Events\Customer\CustomerRegisteredEvent;
use Src\Domain\Identity\Models\Customer;
use Src\Infrastructure\EventStore\EventStoreRepository;
use Src\Shared\Events\DomainEvent;

beforeEach(function () {
    Event::fake();
});

describe('EventStoreRepository', function () {
    it('persists an identity domain event to the identity domain_events table', function () {
        $customer = CustomerFactory::new()->create();
        $domainEvent = new CustomerRegisteredEvent($customer);

        $store = new EventStoreRepository;
        $store->storeAll([$domainEvent]);

        $this->assertDatabaseHas('domain_events', [
            'id' => $domainEvent->eventId,
            'aggregate_type' => 'Customer',
            'aggregate_id' => $customer->id,
        ], 'identity');
    });

    it('persists an account domain event to the accounts domain_events table', function () {
        $account = AccountFactory::new()->create();
        $account->pullDomainEvents();
        $domainEvent = new AccountOpenedEvent($account);

        $store = new EventStoreRepository;
        $store->storeAll([$domainEvent]);

        $this->assertDatabaseHas('domain_events', [
            'id' => $domainEvent->eventId,
            'aggregate_type' => 'Account',
            'aggregate_id' => $account->id,
        ], 'accounts');
    });

    it('auto-increments the aggregate version for subsequent events on the same aggregate', function () {
        $customer = CustomerFactory::new()->create();
        $store = new EventStoreRepository;

        $store->storeAll([new CustomerRegisteredEvent($customer)]);
        $store->storeAll([new CustomerRegisteredEvent($customer)]);

        $versions = DB::connection('identity')
            ->table('domain_events')
            ->where('aggregate_id', $customer->id)
            ->pluck('aggregate_version')
            ->sort()
            ->values()
            ->toArray();

        expect($versions[0])->toBe(1)
            ->and($versions[1])->toBe(2);
    });

    it('throws InvalidArgumentException for an unknown aggregate type', function () {
        $event = new class('some-id', 'UnknownAggregate') extends DomainEvent
        {
            public function toPayload(): array
            {
                return [];
            }
        };

        $store = new EventStoreRepository;

        expect(fn () => $store->storeAll([$event]))
            ->toThrow(InvalidArgumentException::class);
    });

    it('encodes the payload as json in the database', function () {
        $customer = CustomerFactory::new()->create();
        $domainEvent = new CustomerRegisteredEvent($customer);

        $store = new EventStoreRepository;
        $store->storeAll([$domainEvent]);

        $row = DB::connection('identity')
            ->table('domain_events')
            ->where('id', $domainEvent->eventId)
            ->first();

        expect(json_decode($row->payload, true))->toBeArray();
    });
});
