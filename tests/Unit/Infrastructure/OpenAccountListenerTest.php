<?php

use Database\Factories\CustomerFactory;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Src\Domain\Identity\Events\Customer\KycApprovedEvent;
use Src\Interfaces\Events\Identity\KycWasApproved;
use Src\Interfaces\Jobs\Accounts\OpenAccountJob;
use Src\Interfaces\Listeners\Accounts\OpenAccountOnKycApprovedListener;

describe('OpenAccountOnKycApprovedListener', function () {
    it('dispatches OpenAccountJob with the customer id from the domain event', function () {
        Event::fake();
        Bus::fake([OpenAccountJob::class]);

        $customer = CustomerFactory::new()->withKycApproved()->create();
        $domainEvent = new KycApprovedEvent($customer);

        $listener = new OpenAccountOnKycApprovedListener;
        $listener->handle(new KycWasApproved($customer, $domainEvent));

        Bus::assertDispatched(OpenAccountJob::class, function (OpenAccountJob $job) use ($customer) {
            return $job->customerId === $customer->id;
        });
    });
});
