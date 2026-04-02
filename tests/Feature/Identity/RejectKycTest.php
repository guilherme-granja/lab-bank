<?php

use Database\Factories\CustomerFactory;
use Database\Factories\KycVerificationFactory;
use Illuminate\Support\Facades\Event;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;
use Src\Interfaces\Events\Identity\KycWasRejected;

beforeEach(function () {
    Event::fake();
});

describe('POST /api/v1/identity/customer/{customerId}/kyc/reject', function () {
    it('rejects kyc and returns 204', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/reject", [
            'reason' => 'Document photo is blurry',
        ])->assertNoContent();
    });

    it('transitions customer kyc_status to rejected', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/reject", [
            'reason' => 'Document photo is blurry',
        ])->assertNoContent();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'kyc_status' => 'rejected',
        ], 'identity');
    });

    it('transitions kyc_verification to rejected with reason and reviewed_at', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/reject", [
            'reason' => 'Document photo is blurry',
        ])->assertNoContent();

        $this->assertDatabaseHas('kyc_verifications', [
            'id' => $verification->id,
            'status' => 'rejected',
            'rejection_reason' => 'Document photo is blurry',
        ], 'identity');

        expect($verification->fresh()->reviewed_at)->not->toBeNull();
    });

    it('dispatches KycWasRejected event', function () {
        Event::fake([KycWasRejected::class]);

        $customer = CustomerFactory::new()->withKycProcessing()->create();
        KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/reject", [
            'reason' => 'Document photo is blurry',
        ])->assertNoContent();

        Event::assertDispatched(KycWasRejected::class);
    });

    it('returns 422 when reason is missing', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/reject", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reason']);
    });

    it('throws CustomerNotFoundException when customer does not exist', function () {
        $this->withoutExceptionHandling();

        expect(fn () => $this->postJson('/api/v1/identity/customer/non-existent-id/kyc/reject', [
            'reason' => 'Some reason',
        ]))->toThrow(CustomerNotFoundException::class);
    });

    it('throws KycVerificationNotFound when no verification exists', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        $this->withoutExceptionHandling();

        expect(fn () => $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/reject", [
            'reason' => 'Some reason',
        ]))->toThrow(KycVerificationNotFound::class);
    });
});
