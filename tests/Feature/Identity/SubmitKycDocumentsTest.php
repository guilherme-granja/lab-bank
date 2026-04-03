<?php

use Database\Factories\CustomerFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;

beforeEach(function () {
    Storage::fake('local');
    Event::fake();
});

function kycDocumentsPayload(array $overrides = []): array
{
    return array_merge([
        'document_type' => 'cnh',
        'document_number' => '12345678901',
        'document_front' => UploadedFile::fake()->create('front.jpg', 100, 'image/jpeg'),
        'document_back' => UploadedFile::fake()->create('back.jpg', 100, 'image/jpeg'),
        'selfie' => UploadedFile::fake()->create('selfie.jpg', 100, 'image/jpeg'),
    ], $overrides);
}

describe('POST /api/v1/identity/customer/{customerId}/kyc/documents', function () {
    it('submits kyc documents successfully and returns 204', function () {
        $customer = CustomerFactory::new()->create();

        $this->postJson(
            "/api/v1/identity/customer/{$customer->id}/kyc/documents",
            kycDocumentsPayload(),
        )->assertNoContent();
    });

    it('submits documents without document_back and returns 204', function () {
        $customer = CustomerFactory::new()->create();

        $this->postJson(
            "/api/v1/identity/customer/{$customer->id}/kyc/documents",
            kycDocumentsPayload(['document_back' => null]),
        )->assertNoContent();
    });

    it('stores uploaded files in storage', function () {
        $customer = CustomerFactory::new()->create();

        $this->postJson(
            "/api/v1/identity/customer/{$customer->id}/kyc/documents",
            kycDocumentsPayload(),
        )->assertNoContent();

        $files = Storage::files("kyc/{$customer->id}");

        expect($files)->not->toBeEmpty();
    });

    it('persists a kyc_verification record to the database', function () {
        $customer = CustomerFactory::new()->create();

        $this->postJson(
            "/api/v1/identity/customer/{$customer->id}/kyc/documents",
            kycDocumentsPayload(['document_type' => 'passport']),
        )->assertNoContent();

        $this->assertDatabaseHas('kyc_verifications', [
            'customer_id' => $customer->id,
            'document_type' => 'passport',
            'status' => 'pending',
        ], 'identity');
    });

    it('throws CustomerNotFoundException when customer does not exist', function () {
        $this->withoutExceptionHandling();

        expect(fn () => $this->postJson(
            '/api/v1/identity/customer/non-existent-id/kyc/documents',
            kycDocumentsPayload(),
        ))->toThrow(CustomerNotFoundException::class);
    });

    it('returns 422 when document_type is invalid', function () {
        $customer = CustomerFactory::new()->create();

        $this->postJson(
            "/api/v1/identity/customer/{$customer->id}/kyc/documents",
            kycDocumentsPayload(['document_type' => 'invalid_type']),
        )->assertUnprocessable()
            ->assertJsonValidationErrors(['document_type']);
    });

    it('returns 422 when document_front is missing', function () {
        $customer = CustomerFactory::new()->create();

        $payload = kycDocumentsPayload();
        unset($payload['document_front']);

        $this->postJson(
            "/api/v1/identity/customer/{$customer->id}/kyc/documents",
            $payload,
        )->assertUnprocessable()
            ->assertJsonValidationErrors(['document_front']);
    });

    it('returns 422 when selfie is missing', function () {
        $customer = CustomerFactory::new()->create();

        $payload = kycDocumentsPayload();
        unset($payload['selfie']);

        $this->postJson(
            "/api/v1/identity/customer/{$customer->id}/kyc/documents",
            $payload,
        )->assertUnprocessable()
            ->assertJsonValidationErrors(['selfie']);
    });

    it('returns 422 when selfie is a pdf', function () {
        $customer = CustomerFactory::new()->create();

        $this->postJson(
            "/api/v1/identity/customer/{$customer->id}/kyc/documents",
            kycDocumentsPayload([
                'selfie' => UploadedFile::fake()->create('selfie.pdf', 100, 'application/pdf'),
            ]),
        )->assertUnprocessable()
            ->assertJsonValidationErrors(['selfie']);
    });
});
