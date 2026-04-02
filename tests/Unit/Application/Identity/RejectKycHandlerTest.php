<?php

use Database\Factories\CustomerFactory;
use Database\Factories\KycVerificationFactory;
use Illuminate\Support\Facades\Event;
use Src\Application\Identity\DataObjects\RejectKycData;
use Src\Application\Identity\Handlers\RejectKycHandler;
use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Domain\Identity\Contracts\KycVerificationRepositoryContract;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;
use Src\Domain\Identity\Models\Customer;
use Src\Domain\Identity\Models\KycVerification;
use Src\Domain\Identity\States\Kyc\Rejected;

use function Pest\Laravel\mock;

beforeEach(function () {
    Event::fake();

    $this->customerRepository = mock(CustomerRepositoryContract::class);
    $this->kycVerificationRepository = mock(KycVerificationRepositoryContract::class);

    $this->handler = new RejectKycHandler(
        $this->customerRepository,
        $this->kycVerificationRepository,
    );
});

describe('RejectKycHandler', function () {
    it('rejects kyc on both customer and verification with the given reason', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $this->customerRepository->shouldReceive('findById')->once()->andReturn($customer);
        $this->kycVerificationRepository->shouldReceive('findByCustomerId')->once()->andReturn($verification);
        $this->customerRepository->shouldReceive('save')->once()->with(Mockery::type(Customer::class));
        $this->kycVerificationRepository->shouldReceive('save')->once()->with(Mockery::type(KycVerification::class));

        ($this->handler)(new RejectKycData(customerId: $customer->id, reason: 'Documents are unclear'));

        expect($customer->kyc_status)->toBeInstanceOf(Rejected::class);
        expect($verification->rejection_reason)->toBe('Documents are unclear');
        expect($verification->reviewed_at)->not->toBeNull();
    });

    it('throws CustomerNotFoundException when the customer does not exist', function () {
        $this->customerRepository->shouldReceive('findById')->once()->andReturn(null);

        expect(fn () => ($this->handler)(new RejectKycData(customerId: 'non-existent-id', reason: 'Some reason')))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('throws KycVerificationNotFound when no verification exists', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        $this->customerRepository->shouldReceive('findById')->once()->andReturn($customer);
        $this->kycVerificationRepository->shouldReceive('findByCustomerId')->once()->andReturn(null);

        expect(fn () => ($this->handler)(new RejectKycData(customerId: $customer->id, reason: 'Some reason')))
            ->toThrow(KycVerificationNotFound::class);
    });

    it('does not save when the customer does not exist', function () {
        $this->customerRepository->shouldReceive('findById')->once()->andReturn(null);
        $this->customerRepository->shouldNotReceive('save');
        $this->kycVerificationRepository->shouldNotReceive('save');

        expect(fn () => ($this->handler)(new RejectKycData(customerId: 'non-existent-id', reason: 'Some reason')))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('does not save when the verification is not found', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        $this->customerRepository->shouldReceive('findById')->once()->andReturn($customer);
        $this->kycVerificationRepository->shouldReceive('findByCustomerId')->once()->andReturn(null);
        $this->customerRepository->shouldNotReceive('save');
        $this->kycVerificationRepository->shouldNotReceive('save');

        expect(fn () => ($this->handler)(new RejectKycData(customerId: $customer->id, reason: 'Some reason')))
            ->toThrow(KycVerificationNotFound::class);
    });

    it('uses findByCustomerId not findActiveByCustomerId', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $this->customerRepository->shouldReceive('findById')->once()->andReturn($customer);
        $this->kycVerificationRepository->shouldReceive('findByCustomerId')->once()->andReturn($verification);
        $this->kycVerificationRepository->shouldNotReceive('findActiveByCustomerId');
        $this->customerRepository->shouldReceive('save')->once();
        $this->kycVerificationRepository->shouldReceive('save')->once();

        ($this->handler)(new RejectKycData(customerId: $customer->id, reason: 'Some reason'));
    });
});
