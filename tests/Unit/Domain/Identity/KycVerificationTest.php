<?php

use Database\Factories\CustomerFactory;
use Database\Factories\KycVerificationFactory;
use Illuminate\Support\Facades\Event;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Src\Domain\Identity\Enums\Kyc\DocumentTypeEnum;
use Src\Domain\Identity\Models\KycVerification;
use Src\Domain\Identity\States\KycVerification\Approved;
use Src\Domain\Identity\States\KycVerification\Pending;
use Src\Domain\Identity\States\KycVerification\Processing;
use Src\Domain\Identity\States\KycVerification\Rejected;

beforeEach(function () {
    Event::fake();
});

describe('KycVerification::register()', function () {
    it('maps all provided fields onto the new instance', function () {
        $customer = CustomerFactory::new()->create();

        $paths = [
            'document_front_url' => 'kyc/customer/front.jpg',
            'document_back_url' => 'kyc/customer/back.jpg',
            'document_selfie_url' => 'kyc/customer/selfie.jpg',
        ];

        $verification = KycVerification::register(
            paths: $paths,
            customerId: $customer->id,
            documentType: DocumentTypeEnum::Cnh,
            documentNumber: '12345678901',
        );

        expect($verification->customer_id)->toBe($customer->id);
        expect($verification->document_type)->toBe(DocumentTypeEnum::Cnh);
        expect($verification->document_number)->toBe('12345678901');
        expect($verification->document_front_url)->toBe('kyc/customer/front.jpg');
        expect($verification->document_back_url)->toBe('kyc/customer/back.jpg');
        expect($verification->selfie_url)->toBe('kyc/customer/selfie.jpg');
    });

    it('accepts a null document_back_url', function () {
        $customer = CustomerFactory::new()->create();

        $verification = KycVerification::register(
            paths: [
                'document_front_url' => 'kyc/customer/front.jpg',
                'document_back_url' => null,
                'document_selfie_url' => 'kyc/customer/selfie.jpg',
            ],
            customerId: $customer->id,
            documentType: DocumentTypeEnum::Cpf,
            documentNumber: '52998224725',
        );

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
