<?php

use Database\Factories\CustomerFactory;
use Illuminate\Support\Facades\Event;
use Src\Domain\Identity\Events\Customer\CustomerRegisteredEvent;
use Src\Infrastructure\EventStore\EventStoreRepository;
use Src\Interfaces\Events\Identity\CustomerWasRegistered;
use Src\Interfaces\Listeners\PersistDomainEvent;

use function Pest\Laravel\mock;

describe('PersistDomainEvent listener', function () {
    it('stores the domain event from the interface event', function () {
        Event::fake();

        $customer = CustomerFactory::new()->create();
        $domainEvent = new CustomerRegisteredEvent($customer);

        $store = mock(EventStoreRepository::class);
        $store->shouldReceive('storeAll')
            ->once()
            ->with([$domainEvent]);

        $listener = new PersistDomainEvent($store);
        $listener->handle(new CustomerWasRegistered($customer, $domainEvent));
    });
});
