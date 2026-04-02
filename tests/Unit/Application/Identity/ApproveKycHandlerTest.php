<?php

use Database\Factories\CustomerFactory;
use Database\Factories\KycVerificationFactory;
use Illuminate\Support\Facades\Event;
use Src\Application\Identity\DataObjects\ApproveKycData;
use Src\Application\Identity\Handlers\ApproveKycHandler;
use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Domain\Identity\Contracts\KycVerificationRepositoryContract;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;
use Src\Domain\Identity\Models\Customer;
use Src\Domain\Identity\Models\KycVerification;
use Src\Domain\Identity\States\Customer\Active;
use Src\Domain\Identity\States\Kyc\Approved;

use function Pest\Laravel\mock;

beforeEach(function () {
    Event::fake();

    $this->customerRepository = mock(CustomerRepositoryContract::class);
    $this->kycVerificationRepository = mock(KycVerificationRepositoryContract::class);

    $this->handler = new ApproveKycHandler(
        $this->customerRepository,
        $this->kycVerificationRepository,
    );
});

describe('ApproveKycHandler', function () {
    it('approves kyc and activates the customer account', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $this->customerRepository->shouldReceive('findById')->once()->andReturn($customer);
        $this->kycVerificationRepository->shouldReceive('findActiveByCustomerId')->once()->andReturn($verification);
        $this->customerRepository->shouldReceive('save')->once()->with(Mockery::type(Customer::class));
        $this->kycVerificationRepository->shouldReceive('save')->once()->with(Mockery::type(KycVerification::class));

        ($this->handler)(new ApproveKycData(customerId: $customer->id));

        expect($customer->kyc_status)->toBeInstanceOf(Approved::class);
        expect($customer->status)->toBeInstanceOf(Active::class);
        expect($verification->reviewed_at)->not->toBeNull();
    });

    it('throws CustomerNotFoundException when the customer does not exist', function () {
        $this->customerRepository->shouldReceive('findById')->once()->andReturn(null);

        expect(fn () => ($this->handler)(new ApproveKycData(customerId: 'non-existent-id')))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('throws KycVerificationNotFound when no active verification exists', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        $this->customerRepository->shouldReceive('findById')->once()->andReturn($customer);
        $this->kycVerificationRepository->shouldReceive('findActiveByCustomerId')->once()->andReturn(null);

        expect(fn () => ($this->handler)(new ApproveKycData(customerId: $customer->id)))
            ->toThrow(KycVerificationNotFound::class);
    });

    it('does not save when the customer does not exist', function () {
        $this->customerRepository->shouldReceive('findById')->once()->andReturn(null);
        $this->customerRepository->shouldNotReceive('save');
        $this->kycVerificationRepository->shouldNotReceive('save');

        expect(fn () => ($this->handler)(new ApproveKycData(customerId: 'non-existent-id')))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('does not save when the kyc verification is not found', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        $this->customerRepository->shouldReceive('findById')->once()->andReturn($customer);
        $this->kycVerificationRepository->shouldReceive('findActiveByCustomerId')->once()->andReturn(null);
        $this->customerRepository->shouldNotReceive('save');
        $this->kycVerificationRepository->shouldNotReceive('save');

        expect(fn () => ($this->handler)(new ApproveKycData(customerId: $customer->id)))
            ->toThrow(KycVerificationNotFound::class);
    });
});
