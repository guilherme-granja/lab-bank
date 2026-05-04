<?php

use Database\Factories\CustomerFactory;
use Database\Factories\KycVerificationFactory;
use Illuminate\Support\Facades\Event;
use Src\Application\Identity\DataObjects\RejectKycData;
use Src\Application\Identity\Handlers\RejectKycHandler;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;
use Src\Domain\Identity\States\Kyc\Rejected;

beforeEach(function () {
    Event::fake();

    $this->handler = new RejectKycHandler;
});

describe('RejectKycHandler', function () {
    it('rejects kyc on both customer and verification with the given reason', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        ($this->handler)(new RejectKycData(customerId: $customer->id, reason: 'Documents are unclear'));

        $customer->refresh();
        $verification->refresh();

        expect($customer->kyc_status)->toBeInstanceOf(Rejected::class);
        expect($verification->rejection_reason)->toBe('Documents are unclear');
        expect($verification->reviewed_at)->not->toBeNull();
    });

    it('throws CustomerNotFoundException when the customer does not exist', function () {
        expect(fn () => ($this->handler)(new RejectKycData(customerId: '00000000-0000-0000-0000-000000000000', reason: 'Some reason')))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('throws KycVerificationNotFound when no verification exists', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        expect(fn () => ($this->handler)(new RejectKycData(customerId: $customer->id, reason: 'Some reason')))
            ->toThrow(KycVerificationNotFound::class);
    });

});
