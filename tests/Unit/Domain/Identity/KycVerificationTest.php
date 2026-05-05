<?php

use Database\Factories\CustomerFactory;
use Database\Factories\KycVerificationFactory;
use Illuminate\Support\Facades\Event;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Src\Application\Identity\DataObjects\KycDocumentStorageData;
use Src\Domain\Identity\Enums\Kyc\DocumentTypeEnum;
use Src\Domain\Identity\Models\KycVerification;
use Src\Domain\Identity\States\KycVerification\Approved;
use Src\Domain\Identity\States\KycVerification\Pending;
use Src\Domain\Identity\States\KycVerification\Processing;
use Src\Domain\Identity\States\KycVerification\Rejected;

beforeEach(function () {
    Event::fake();
});

describe('KycVerification::create()', function () {
    it('maps all provided fields onto the new instance', function () {
        $customer = CustomerFactory::new()->create();

        $paths = KycDocumentStorageData::from([
            'document_front_url' => 'kyc/customer/front.jpg',
            'document_back_url' => 'kyc/customer/back.jpg',
            'document_selfie_url' => 'kyc/customer/selfie.jpg',
        ]);

        $verification = $customer->kycVerifications()->create([
            'document_type' => DocumentTypeEnum::Cnh,
            'document_number' => '12345678901',
            'document_front_url' => $paths->documentFrontUrl,
            'document_back_url' => $paths->documentBackUrl,
            'selfie_url' => $paths->documentSelfieUrl,
        ]);

        expect($verification->customer_id)->toBe($customer->id)
            ->and($verification->document_type)->toBe(DocumentTypeEnum::Cnh)
            ->and($verification->document_number)->toBe('12345678901')
            ->and($verification->document_front_url)->toBe('kyc/customer/front.jpg')
            ->and($verification->document_back_url)->toBe('kyc/customer/back.jpg')
            ->and($verification->selfie_url)->toBe('kyc/customer/selfie.jpg');
    });

    it('accepts a null document_back_url', function () {
        $customer = CustomerFactory::new()->create();

        $paths = KycDocumentStorageData::from([
            'document_front_url' => 'kyc/customer/front.jpg',
            'document_selfie_url' => 'kyc/customer/selfie.jpg',
        ]);

        $verification = $customer->kycVerifications()->create([
            'document_type' => DocumentTypeEnum::Cnh,
            'document_number' => '12345678901',
            'document_front_url' => $paths->documentFrontUrl,
            'selfie_url' => $paths->documentSelfieUrl,
        ]);

        expect($verification->document_back_url)->toBeNull();
    });

    it('defaults status to pending when persisted', function () {
        $customer = CustomerFactory::new()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->create();

        expect($verification->status)->toBeInstanceOf(Pending::class);
    });
});

describe('KycVerification::startReview()', function () {
    it('transitions status from pending to processing', function () {
        $customer = CustomerFactory::new()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->create();

        $verification->startReview();

        expect($verification->status)->toBeInstanceOf(Processing::class);
    });

    it('throws CouldNotPerformTransition when status is already approved', function () {
        $customer = CustomerFactory::new()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->approved()->create();

        expect(fn () => $verification->startReview())
            ->toThrow(CouldNotPerformTransition::class);
    });
});

describe('KycVerification::approve()', function () {
    it('transitions status from processing to approved', function () {
        $customer = CustomerFactory::new()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $verification->approve();

        expect($verification->status)->toBeInstanceOf(Approved::class);
    });

    it('sets reviewed_at on approval', function () {
        $customer = CustomerFactory::new()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        expect($verification->reviewed_at)->toBeNull();

        $verification->approve();

        expect($verification->reviewed_at)->not->toBeNull();
    });

    it('throws CouldNotPerformTransition when status is pending', function () {
        $customer = CustomerFactory::new()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->create();

        expect(fn () => $verification->approve())
            ->toThrow(CouldNotPerformTransition::class);
    });
});

describe('KycVerification::reject()', function () {
    it('transitions status from processing to rejected', function () {
        $customer = CustomerFactory::new()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $verification->reject('Photo is unclear');

        expect($verification->status)->toBeInstanceOf(Rejected::class);
    });

    it('sets reviewed_at on rejection', function () {
        $customer = CustomerFactory::new()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $verification->reject('Photo is unclear');

        expect($verification->reviewed_at)->not->toBeNull();
    });

    it('stores the rejection reason', function () {
        $customer = CustomerFactory::new()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->processing()->create();

        $verification->reject('Photo is unclear');

        expect($verification->rejection_reason)->toBe('Photo is unclear');
    });

    it('throws CouldNotPerformTransition when status is pending', function () {
        $customer = CustomerFactory::new()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->create();

        expect(fn () => $verification->reject('Some reason'))
            ->toThrow(CouldNotPerformTransition::class);
    });
});

describe('KycVerification relationships', function () {
    it('belongs to a customer', function () {
        $customer = CustomerFactory::new()->create();
        $verification = KycVerificationFactory::new()->forCustomer($customer->id)->create();

        expect($verification->customer->id)->toBe($customer->id);
    });
});
