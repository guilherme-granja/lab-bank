<?php

use Database\Factories\CustomerFactory;
use Database\Factories\KycVerificationFactory;
use Illuminate\Support\Facades\Event;
use Src\Application\Identity\DataObjects\StartKycReviewData;
use Src\Application\Identity\Handlers\StartKycReviewHandler;
use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Domain\Identity\Contracts\KycVerificationRepositoryContract;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;
use Src\Domain\Identity\Models\Customer;
use Src\Domain\Identity\Models\KycVerification;
use Src\Domain\Identity\States\Kyc\Processing as KycProcessing;
use Src\Domain\Identity\States\KycVerification\Processing as VerificationProcessing;

use function Pest\Laravel\mock;

beforeEach(function () {
    Event::fake();

    $this->customerRepository = mock(CustomerRepositoryContract::class);
    $this->kycVerificationRepository = mock(KycVerificationRepositoryContract::class);

    $this->handler = new StartKycReviewHandler(
        $this->customerRepository,
        $this->kycVerificationRepository,
    );
});

describe('StartKycReviewHandler', function () {
    it('transitions customer kyc_status and verification status to processing', function () {
        $customer = CustomerFactory::new()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->create();

        $this->customerRepository->shouldReceive('findById')->once()->andReturn($customer);
        $this->kycVerificationRepository->shouldReceive('findActiveByCustomerId')->once()->andReturn($verification);
        $this->customerRepository->shouldReceive('save')->once()->with(Mockery::type(Customer::class));
        $this->kycVerificationRepository->shouldReceive('save')->once()->with(Mockery::type(KycVerification::class));

        ($this->handler)(new StartKycReviewData(customerId: $customer->id));

        expect($customer->kyc_status)->toBeInstanceOf(KycProcessing::class);
        expect($verification->status)->toBeInstanceOf(VerificationProcessing::class);
    });

    it('throws CustomerNotFoundException when the customer does not exist', function () {
        $this->customerRepository->shouldReceive('findById')->once()->andReturn(null);

        expect(fn () => ($this->handler)(new StartKycReviewData(customerId: 'non-existent-id')))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('throws KycVerificationNotFound when no active verification exists', function () {
        $customer = CustomerFactory::new()->create();

        $this->customerRepository->shouldReceive('findById')->once()->andReturn($customer);
        $this->kycVerificationRepository->shouldReceive('findActiveByCustomerId')->once()->andReturn(null);

        expect(fn () => ($this->handler)(new StartKycReviewData(customerId: $customer->id)))
            ->toThrow(KycVerificationNotFound::class);
    });

    it('does not save when the customer does not exist', function () {
        $this->customerRepository->shouldReceive('findById')->once()->andReturn(null);
        $this->customerRepository->shouldNotReceive('save');
        $this->kycVerificationRepository->shouldNotReceive('save');

        expect(fn () => ($this->handler)(new StartKycReviewData(customerId: 'non-existent-id')))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('does not save when no active verification exists', function () {
        $customer = CustomerFactory::new()->create();

        $this->customerRepository->shouldReceive('findById')->once()->andReturn($customer);
        $this->kycVerificationRepository->shouldReceive('findActiveByCustomerId')->once()->andReturn(null);
        $this->customerRepository->shouldNotReceive('save');
        $this->kycVerificationRepository->shouldNotReceive('save');

        expect(fn () => ($this->handler)(new StartKycReviewData(customerId: $customer->id)))
            ->toThrow(KycVerificationNotFound::class);
    });
});
