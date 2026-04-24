<?php

use Database\Factories\CustomerFactory;
use Illuminate\Support\Facades\Event;
use Src\Application\Identity\DataObjects\AddressData;
use Src\Application\Identity\DataObjects\RegisterCustomerData;
use Src\Domain\Identity\Models\Customer;
use Src\Interfaces\Events\Identity\CustomerWasActivated;
use Src\Interfaces\Events\Identity\CustomerWasBlocked;
use Src\Interfaces\Events\Identity\CustomerWasRegistered;
use Src\Interfaces\Events\Identity\KycWasApproved;
use Src\Interfaces\Events\Identity\KycWasRejected;

/**
 * These tests verify that the CustomerObserver correctly maps domain events
 * to interface events when a Customer model is saved.
 *
 * We must create customers via Customer::register() (not the factory) to ensure
 * domain events are recorded on the model before it is persisted.
 */
function makeRegisterData(array $overrides = []): RegisterCustomerData
{
    static $cpfs = ['52998224725', '11144477735', '00000000191', '01234567890'];
    static $idx = 0;

    return new RegisterCustomerData(
        fullName: $overrides['fullName'] ?? 'João da Silva',
        cpf: $overrides['cpf'] ?? $cpfs[$idx++ % count($cpfs)],
        email: $overrides['email'] ?? uniqid('test', true).'@example.com',
        phone: '(11) 98765-4321',
        birthDate: '1990-01-15',
        motherName: 'Maria da Silva',
        nationality: 'BRA',
        address: new AddressData('01310-100', 'Av. Paulista', '1000', null, 'Bela Vista', 'São Paulo', 'SP', 'BRA'),
    );
}

describe('CustomerObserver', function () {
    it('dispatches CustomerWasRegistered when a new customer is saved', function () {
        Event::fake([CustomerWasRegistered::class]);

        $customer = Customer::register(makeRegisterData());
        $customer->save();

        Event::assertDispatched(CustomerWasRegistered::class);
    });

    it('dispatches CustomerWasActivated when customer status transitions to active', function () {
        Event::fake([CustomerWasActivated::class]);

        $customer = CustomerFactory::new()->create();
        $customer->pullDomainEvents();

        $customer->activateAccount();
        $customer->save();

        Event::assertDispatched(CustomerWasActivated::class);
    });

    it('dispatches KycWasApproved when kyc is approved', function () {
        Event::fake([KycWasApproved::class]);

        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $customer->pullDomainEvents();

        $customer->approveKyc();
        $customer->save();

        Event::assertDispatched(KycWasApproved::class);
    });

    it('dispatches KycWasRejected when kyc is rejected', function () {
        Event::fake([KycWasRejected::class]);

        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $customer->pullDomainEvents();

        $customer->rejectKyc('Documents unclear');
        $customer->save();

        Event::assertDispatched(KycWasRejected::class);
    });

    it('dispatches CustomerWasBlocked when customer is blocked', function () {
        Event::fake([CustomerWasBlocked::class]);

        $customer = CustomerFactory::new()->withKycApproved()->create();
        $customer->pullDomainEvents();

        $customer->block('Suspicious activity');
        $customer->save();

        Event::assertDispatched(CustomerWasBlocked::class);
    });

    it('dispatches no business events when customer is saved with no domain events', function () {
        $customer = CustomerFactory::new()->create();
        $customer->pullDomainEvents();

        Event::fake([
            CustomerWasRegistered::class,
            CustomerWasActivated::class,
            CustomerWasBlocked::class,
            KycWasApproved::class,
            KycWasRejected::class,
        ]);

        $customer->full_name = 'Updated Name';
        $customer->save();

        Event::assertNotDispatched(CustomerWasRegistered::class);
        Event::assertNotDispatched(CustomerWasActivated::class);
        Event::assertNotDispatched(CustomerWasBlocked::class);
        Event::assertNotDispatched(KycWasApproved::class);
        Event::assertNotDispatched(KycWasRejected::class);
    });
});
