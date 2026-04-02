<?php

use Database\Factories\CustomerFactory;
use Database\Factories\KycVerificationFactory;
use Illuminate\Support\Facades\Event;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;
use Src\Interfaces\Events\Identity\CustomerWasActivated;
use Src\Interfaces\Events\Identity\KycWasApproved;

beforeEach(function () {
    Event::fake();
});

describe('POST /api/v1/identity/customer/{customerId}/kyc/approve', function () {
    it('approves kyc and returns 204', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/approve")
            ->assertNoContent();
    });

    it('transitions customer kyc_status to approved and status to active', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/approve")
            ->assertNoContent();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'kyc_status' => 'approved',
            'status' => 'active',
        ], 'identity');
    });

    it('transitions kyc_verification to approved and sets reviewed_at', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/approve")
            ->assertNoContent();

        $this->assertDatabaseHas('kyc_verifications', [
            'id' => $verification->id,
            'status' => 'approved',
        ], 'identity');

        expect($verification->fresh()->reviewed_at)->not->toBeNull();
    });

    it('dispatches KycWasApproved and CustomerWasActivated events', function () {
        Event::fake([KycWasApproved::class, CustomerWasActivated::class]);

        $customer = CustomerFactory::new()->withKycProcessing()->create();
        KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/approve")
            ->assertNoContent();

        Event::assertDispatched(KycWasApproved::class);
        Event::assertDispatched(CustomerWasActivated::class);
    });

    it('throws CustomerNotFoundException when customer does not exist', function () {
        $this->withoutExceptionHandling();

        expect(fn () => $this->postJson('/api/v1/identity/customer/non-existent-id/kyc/approve'))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('throws KycVerificationNotFound when no active verification exists', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        $this->withoutExceptionHandling();

        expect(fn () => $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/approve"))
            ->toThrow(KycVerificationNotFound::class);
    });
});
