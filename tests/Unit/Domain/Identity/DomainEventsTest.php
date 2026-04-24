<?php

use Database\Factories\CustomerFactory;
use Illuminate\Support\Facades\Event;
use Src\Domain\Identity\Events\Customer\CustomerActivatedEvent;
use Src\Domain\Identity\Events\Customer\CustomerBlockedEvent;
use Src\Domain\Identity\Events\Customer\CustomerRegisteredEvent;
use Src\Domain\Identity\Events\Customer\KycApprovedEvent;
use Src\Domain\Identity\Events\Customer\KycRejectedEvent;

beforeEach(function () {
    Event::fake();
});

describe('CustomerRegisteredEvent', function () {
    it('toPayload returns the expected fields', function () {
        $customer = CustomerFactory::new()->create();
        $event = new CustomerRegisteredEvent($customer);

        $payload = $event->toPayload();

        expect($payload)->toHaveKeys(['full_name', 'cpf', 'email', 'phone', 'birth_date', 'kyc_status', 'status']);
        expect($payload['full_name'])->toBe($customer->full_name);
        expect($payload['cpf'])->toBe($customer->cpf);
        expect($payload['email'])->toBe($customer->email);
    });

    it('aggregateId is the customer id', function () {
        $customer = CustomerFactory::new()->create();
        $event = new CustomerRegisteredEvent($customer);

        expect($event->aggregateId)->toBe($customer->id);
    });

    it('aggregateType is Customer', function () {
        $customer = CustomerFactory::new()->create();
        $event = new CustomerRegisteredEvent($customer);

        expect($event->aggregateType)->toBe('Customer');
    });
});

describe('CustomerActivatedEvent', function () {
    it('toPayload returns the expected fields', function () {
        $customer = CustomerFactory::new()->create();
        $event = new CustomerActivatedEvent($customer);

        $payload = $event->toPayload();

        expect($payload)->toHaveKeys(['full_name', 'cpf', 'email', 'phone', 'birth_date', 'kyc_status', 'status']);
        expect($payload['email'])->toBe($customer->email);
    });
});

describe('CustomerBlockedEvent', function () {
    it('toPayload returns reason, kyc_status and status', function () {
        $customer = CustomerFactory::new()->withKycApproved()->create();
        $event = new CustomerBlockedEvent($customer, 'Suspicious activity');

        $payload = $event->toPayload();

        expect($payload)->toHaveKeys(['reason', 'kyc_status', 'status']);
        expect($payload['reason'])->toBe('Suspicious activity');
    });
});

describe('KycApprovedEvent', function () {
    it('toPayload returns email and approved_at', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $event = new KycApprovedEvent($customer);

        $payload = $event->toPayload();

        expect($payload)->toHaveKeys(['email', 'approved_at']);
        expect($payload['email'])->toBe($customer->email);
    });
});

describe('KycRejectedEvent', function () {
    it('toPayload returns email and reason', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $event = new KycRejectedEvent($customer, 'Photo is unclear');

        $payload = $event->toPayload();

        expect($payload)->toHaveKeys(['email', 'reason']);
        expect($payload['reason'])->toBe('Photo is unclear');
    });
});
