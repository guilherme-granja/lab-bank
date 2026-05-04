<?php

use Database\Factories\CustomerFactory;
use Database\Factories\KycVerificationFactory;
use Illuminate\Support\Facades\Event;
use Src\Application\Identity\DataObjects\StartKycReviewData;
use Src\Application\Identity\Handlers\StartKycReviewHandler;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;
use Src\Domain\Identity\States\Kyc\Processing as KycProcessing;
use Src\Domain\Identity\States\KycVerification\Processing as VerificationProcessing;

beforeEach(function () {
    Event::fake();

    $this->handler = new StartKycReviewHandler;
});

describe('StartKycReviewHandler', function () {
    it('transitions customer kyc_status and verification status to processing', function () {
        $customer = CustomerFactory::new()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->create();

        ($this->handler)(new StartKycReviewData(customerId: $customer->id));

        $customer->refresh();
        $verification->refresh();

        expect($customer->kyc_status)->toBeInstanceOf(KycProcessing::class);
        expect($verification->status)->toBeInstanceOf(VerificationProcessing::class);
    });

    it('throws CustomerNotFoundException when the customer does not exist', function () {
        expect(fn () => ($this->handler)(new StartKycReviewData(customerId: '00000000-0000-0000-0000-000000000000')))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('throws KycVerificationNotFound when no active verification exists', function () {
        $customer = CustomerFactory::new()->create();

        expect(fn () => ($this->handler)(new StartKycReviewData(customerId: $customer->id)))
            ->toThrow(KycVerificationNotFound::class);
    });

    it('does not transition when verification missing', function () {
        $customer = CustomerFactory::new()->create();
        $kycStatusBefore = (string) $customer->kyc_status;

        expect(fn () => ($this->handler)(new StartKycReviewData(customerId: $customer->id)))
            ->toThrow(KycVerificationNotFound::class);

        $customer->refresh();
        expect((string) $customer->kyc_status)->toBe($kycStatusBefore);
    });
});
