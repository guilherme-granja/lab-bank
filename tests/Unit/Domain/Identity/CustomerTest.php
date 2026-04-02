<?php

use Database\Factories\CustomerFactory;
use Illuminate\Support\Facades\Event;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Src\Application\Identity\DataObjects\AddressData;
use Src\Application\Identity\DataObjects\RegisterCustomerData;
use Src\Domain\Identity\Events\Customer\CustomerActivatedEvent;
use Src\Domain\Identity\Events\Customer\CustomerBlockedEvent;
use Src\Domain\Identity\Events\Customer\CustomerRegisteredEvent;
use Src\Domain\Identity\Events\Customer\KycApprovedEvent;
use Src\Domain\Identity\Events\Customer\KycRejectedEvent;
use Src\Domain\Identity\Models\Customer;
use Src\Domain\Identity\States\Customer\Active;
use Src\Domain\Identity\States\Customer\Blocked;
use Src\Domain\Identity\States\Customer\PendingKyc;
use Src\Domain\Identity\States\Kyc\Approved;
use Src\Domain\Identity\States\Kyc\Pending;
use Src\Domain\Identity\States\Kyc\Processing;
use Src\Domain\Identity\States\Kyc\Rejected;

beforeEach(function () {
    Event::fake();
});

describe('Customer::register()', function () {
    it('maps all provided attributes onto the new instance', function () {
        $data = new RegisterCustomerData(
            fullName: 'João da Silva',
            cpf: '52998224725',
            email: 'joao@example.com',
            phone: '(11) 98765-4321',
            birthDate: '1990-01-15',
            motherName: 'Maria da Silva',
            nationality: 'BRA',
            address: new AddressData('01310-100', 'Av. Paulista', '1000', null, 'Bela Vista', 'São Paulo', 'SP', 'BRA'),
        );

        $customer = Customer::register($data);

        expect($customer->full_name)->toBe('João da Silva');
        expect($customer->cpf)->toBe('52998224725');
        expect($customer->email)->toBe('joao@example.com');
        expect($customer->phone)->toBe('(11) 98765-4321');
        expect($customer->mother_name)->toBe('Maria da Silva');
        expect($customer->nationality)->toBe('BRA');
    });

    it('normalises a formatted cpf to digits only', function () {
        $data = new RegisterCustomerData(
            fullName: 'João',
            cpf: '529.982.247-25',
            email: 'joao@example.com',
            phone: '(11) 98765-4321',
            birthDate: '1990-01-15',
            motherName: 'Maria',
            nationality: 'BRA',
            address: new AddressData('01310-100', 'Av. Paulista', '1000', null, 'Bela Vista', 'São Paulo', 'SP', 'BRA'),
        );

        expect(Customer::register($data)->cpf)->toBe('52998224725');
    });

    it('assigns a uuid to the id field', function () {
        $data = new RegisterCustomerData(
            fullName: 'João',
            cpf: '52998224725',
            email: 'joao@example.com',
            phone: '(11) 98765-4321',
            birthDate: '1990-01-15',
            motherName: 'Maria',
            nationality: 'BRA',
            address: new AddressData('01310-100', 'Av. Paulista', '1000', null, 'Bela Vista', 'São Paulo', 'SP', 'BRA'),
        );

        expect(Customer::register($data)->id)
            ->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('records a CustomerRegisteredEvent', function () {
        $data = new RegisterCustomerData(
            fullName: 'João',
            cpf: '52998224725',
            email: 'joao@example.com',
            phone: '(11) 98765-4321',
            birthDate: '1990-01-15',
            motherName: 'Maria',
            nationality: 'BRA',
            address: new AddressData('01310-100', 'Av. Paulista', '1000', null, 'Bela Vista', 'São Paulo', 'SP', 'BRA'),
        );

        $customer = Customer::register($data);
        $events = $customer->pullDomainEvents();

        expect($events)->toHaveCount(1);
        expect($events[0])->toBeInstanceOf(CustomerRegisteredEvent::class);
    });
});

describe('Customer::canOperate()', function () {
    it('returns true when kyc is approved and status is active', function () {
        $customer = CustomerFactory::new()->withKycApproved()->create();

        expect($customer->canOperate())->toBeTrue();
    });

    it('returns false when kyc is pending', function () {
        $customer = CustomerFactory::new()->create();

        expect($customer->canOperate())->toBeFalse();
    });

    it('returns false when kyc is approved but customer is blocked', function () {
        $customer = CustomerFactory::new()->withStatusBlocked()->create();

        expect($customer->canOperate())->toBeFalse();
    });

    it('returns false when kyc is processing', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        expect($customer->canOperate())->toBeFalse();
    });
});

describe('Customer::canSubmmitKyc()', function () {
    it('returns true when kyc_status is pending', function () {
        $customer = CustomerFactory::new()->create();

        expect($customer->canSubmmitKyc())->toBeTrue();
    });

    it('returns true when kyc_status is rejected', function () {
        $customer = CustomerFactory::new()->withKycRejected()->create();

        expect($customer->canSubmmitKyc())->toBeTrue();
    });

    it('returns false when kyc_status is approved', function () {
        $customer = CustomerFactory::new()->withKycApproved()->create();

        expect($customer->canSubmmitKyc())->toBeFalse();
    });

    it('returns false when kyc_status is processing', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        expect($customer->canSubmmitKyc())->toBeFalse();
    });
});

describe('Customer::startKycReview()', function () {
    it('transitions kyc_status from pending to processing', function () {
        $customer = CustomerFactory::new()->create();

        $customer->startKycReview();

        expect($customer->kyc_status)->toBeInstanceOf(Processing::class);
    });

    it('throws CouldNotPerformTransition when kyc_status is already approved', function () {
        $customer = CustomerFactory::new()->withKycApproved()->create();

        expect(fn () => $customer->startKycReview())
            ->toThrow(CouldNotPerformTransition::class);
    });
});

describe('Customer::approveKyc()', function () {
    it('transitions kyc_status from processing to approved', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        $customer->approveKyc();

        expect($customer->kyc_status)->toBeInstanceOf(Approved::class);
    });

    it('records a KycApprovedEvent', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $customer->pullDomainEvents();

        $customer->approveKyc();

        $events = $customer->pullDomainEvents();
        expect($events)->toHaveCount(1);
        expect($events[0])->toBeInstanceOf(KycApprovedEvent::class);
    });

    it('throws CouldNotPerformTransition when kyc_status is not processing', function () {
        $customer = CustomerFactory::new()->create();

        expect(fn () => $customer->approveKyc())
            ->toThrow(CouldNotPerformTransition::class);
    });
});

describe('Customer::rejectKyc()', function () {
    it('transitions kyc_status from processing to rejected', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        $customer->rejectKyc('Documents are not clear');

        expect($customer->kyc_status)->toBeInstanceOf(Rejected::class);
    });

    it('records a KycRejectedEvent', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $customer->pullDomainEvents();

        $customer->rejectKyc('Documents are not clear');

        $events = $customer->pullDomainEvents();
        expect($events)->toHaveCount(1);
        expect($events[0])->toBeInstanceOf(KycRejectedEvent::class);
    });
});

describe('Customer::activateAccount()', function () {
    it('transitions status from pending_kyc to active', function () {
        $customer = CustomerFactory::new()->create();

        $customer->activateAccount();

        expect($customer->status)->toBeInstanceOf(Active::class);
    });

    it('records a CustomerActivatedEvent', function () {
        $customer = CustomerFactory::new()->create();
        $customer->pullDomainEvents();

        $customer->activateAccount();

        $events = $customer->pullDomainEvents();
        expect($events)->toHaveCount(1);
        expect($events[0])->toBeInstanceOf(CustomerActivatedEvent::class);
    });
});

describe('Customer::block()', function () {
    it('transitions status from active to blocked', function () {
        $customer = CustomerFactory::new()->withKycApproved()->create();

        $customer->block('Suspicious activity detected');

        expect($customer->status)->toBeInstanceOf(Blocked::class);
    });

    it('records a CustomerBlockedEvent', function () {
        $customer = CustomerFactory::new()->withKycApproved()->create();
        $customer->pullDomainEvents();

        $customer->block('Suspicious activity detected');

        $events = $customer->pullDomainEvents();
        expect($events)->toHaveCount(1);
        expect($events[0])->toBeInstanceOf(CustomerBlockedEvent::class);
    });

    it('throws CouldNotPerformTransition when status is pending_kyc', function () {
        $customer = CustomerFactory::new()->create();

        expect(fn () => $customer->block('Some reason'))
            ->toThrow(CouldNotPerformTransition::class);
    });
});

describe('Customer default states', function () {
    it('defaults kyc_status to pending', function () {
        $customer = CustomerFactory::new()->create();

        expect($customer->kyc_status)->toBeInstanceOf(Pending::class);
    });

    it('defaults status to pending_kyc', function () {
        $customer = CustomerFactory::new()->create();

        expect($customer->status)->toBeInstanceOf(PendingKyc::class);
    });
});
