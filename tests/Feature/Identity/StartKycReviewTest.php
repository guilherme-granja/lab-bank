<?php

use Database\Factories\CustomerFactory;
use Database\Factories\KycVerificationFactory;
use Illuminate\Support\Facades\Event;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;

beforeEach(function () {
    Event::fake();
});

describe('POST /api/v1/identity/customer/{customerId}/kyc/start-review', function () {
    it('starts kyc review and returns 204', function () {
        $customer = CustomerFactory::new()->create();
        KycVerificationFactory::new()->forCustomer($customer->id)->create();

        $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/start-review")
            ->assertNoContent();
    });

    it('transitions customer kyc_status to processing', function () {
        $customer = CustomerFactory::new()->create();
        KycVerificationFactory::new()->forCustomer($customer->id)->create();

        $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/start-review")
            ->assertNoContent();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'kyc_status' => 'processing',
        ], 'identity');
    });

    it('transitions kyc_verification status to processing', function () {
        $customer = CustomerFactory::new()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->create();

        $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/start-review")
            ->assertNoContent();

        $this->assertDatabaseHas('kyc_verifications', [
            'id' => $verification->id,
            'status' => 'processing',
        ], 'identity');
    });

    it('throws CustomerNotFoundException when customer does not exist', function () {
        $this->withoutExceptionHandling();

        expect(fn () => $this->postJson('/api/v1/identity/customer/non-existent-id/kyc/start-review'))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('throws KycVerificationNotFound when no active verification exists', function () {
        $customer = CustomerFactory::new()->create();

        $this->withoutExceptionHandling();

        expect(fn () => $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/start-review"))
            ->toThrow(KycVerificationNotFound::class);
    });

    it('throws KycVerificationNotFound when only a rejected verification exists', function () {
        $customer = CustomerFactory::new()->create();
        KycVerificationFactory::new()->forCustomer($customer->id)->rejected()->create();

        $this->withoutExceptionHandling();

        expect(fn () => $this->postJson("/api/v1/identity/customer/{$customer->id}/kyc/start-review"))
            ->toThrow(KycVerificationNotFound::class);
    });
});
