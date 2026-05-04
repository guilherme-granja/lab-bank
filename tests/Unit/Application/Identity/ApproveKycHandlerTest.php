<?php

use Database\Factories\CustomerFactory;
use Database\Factories\KycVerificationFactory;
use Illuminate\Support\Facades\Event;
use Src\Application\Identity\DataObjects\ApproveKycData;
use Src\Application\Identity\Handlers\ApproveKycHandler;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;
use Src\Domain\Identity\Models\KycVerification;
use Src\Domain\Identity\States\Customer\Active;
use Src\Domain\Identity\States\Kyc\Approved;

beforeEach(function () {
    Event::fake();

    $this->handler = new ApproveKycHandler;
});

describe('ApproveKycHandler', function () {
    it('approves kyc and activates the customer account', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        ($this->handler)(new ApproveKycData(customerId: $customer->id));

        $customer->refresh();
        $verification->refresh();

        expect($customer->kyc_status)->toBeInstanceOf(Approved::class);
        expect($customer->status)->toBeInstanceOf(Active::class);
        expect($verification->reviewed_at)->not->toBeNull();
    });

    it('throws CustomerNotFoundException when the customer does not exist', function () {
        expect(fn () => ($this->handler)(new ApproveKycData(customerId: '00000000-0000-0000-0000-000000000000')))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('throws KycVerificationNotFound when no active verification exists', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        expect(fn () => ($this->handler)(new ApproveKycData(customerId: $customer->id)))
            ->toThrow(KycVerificationNotFound::class);
    });

    it('does not modify customer when verification is missing', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $kycStatusBefore = (string) $customer->kyc_status;

        expect(fn () => ($this->handler)(new ApproveKycData(customerId: $customer->id)))
            ->toThrow(KycVerificationNotFound::class);

        $customer->refresh();
        expect((string) $customer->kyc_status)->toBe($kycStatusBefore);
    });

    it('does not persist verification approval when verification missing', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        expect(fn () => ($this->handler)(new ApproveKycData(customerId: $customer->id)))
            ->toThrow(KycVerificationNotFound::class);

        expect(KycVerification::where('customer_id', $customer->id)->exists())->toBeFalse();
    });
});
