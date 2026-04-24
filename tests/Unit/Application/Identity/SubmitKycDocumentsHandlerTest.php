<?php

use Database\Factories\CustomerFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Src\Application\Identity\DataObjects\SubmitKycDocumentsData;
use Src\Application\Identity\Handlers\SubmitKycDocumentsHandler;
use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Domain\Identity\Contracts\KycVerificationRepositoryContract;
use Src\Domain\Identity\Enums\Kyc\DocumentTypeEnum;
use Src\Domain\Identity\Exceptions\CustomerCantSubmitKyc;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Models\KycVerification;
use Src\Infrastructure\Storage\KycDocumentStorage;

use function Pest\Laravel\mock;

beforeEach(function () {
    Event::fake();

    $this->customerRepository = mock(CustomerRepositoryContract::class);
    $this->kycVerificationRepository = mock(KycVerificationRepositoryContract::class);
    $this->kycDocumentStorage = mock(KycDocumentStorage::class);

    $this->handler = new SubmitKycDocumentsHandler(
        $this->customerRepository,
        $this->kycVerificationRepository,
        $this->kycDocumentStorage,
    );

    $this->data = new SubmitKycDocumentsData(
        customerId: 'some-customer-id',
        documentType: DocumentTypeEnum::Cnh,
        documentNumber: '12345678901',
        documentFront: UploadedFile::fake()->create('front.jpg', 100, 'image/jpeg'),
        documentBack: UploadedFile::fake()->create('back.jpg', 100, 'image/jpeg'),
        selfie: UploadedFile::fake()->create('selfie.jpg', 100, 'image/jpeg'),
    );
});

describe('SubmitKycDocumentsHandler', function () {
    it('uploads documents and persists a kyc verification on success', function () {
        $customer = CustomerFactory::new()->create();

        $this->customerRepository->shouldReceive('findById')->once()->andReturn($customer);
        $this->kycDocumentStorage->shouldReceive('uploadKycDocuments')->once()->andReturn([
            'document_front_url' => 'kyc/id/front.jpg',
            'document_back_url' => 'kyc/id/back.jpg',
            'document_selfie_url' => 'kyc/id/selfie.jpg',
        ]);
        $this->kycVerificationRepository->shouldReceive('save')->once()->with(Mockery::type(KycVerification::class));

        ($this->handler)($this->data);
    });

    it('throws CustomerNotFoundException when the customer does not exist', function () {
        $this->customerRepository->shouldReceive('findById')->once()->andReturn(null);

        expect(fn () => ($this->handler)($this->data))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('does not upload documents when the customer does not exist', function () {
        $this->customerRepository->shouldReceive('findById')->once()->andReturn(null);
        $this->kycDocumentStorage->shouldNotReceive('uploadKycDocuments');

        expect(fn () => ($this->handler)($this->data))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('does not persist a verification when the customer does not exist', function () {
        $this->customerRepository->shouldReceive('findById')->once()->andReturn(null);
        $this->kycVerificationRepository->shouldNotReceive('save');

        expect(fn () => ($this->handler)($this->data))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('throws CustomerCantSubmitKyc when the customer kyc is already approved', function () {
        $customer = CustomerFactory::new()->withKycApproved()->create();

        $this->customerRepository->shouldReceive('findById')->once()->andReturn($customer);
        $this->kycDocumentStorage->shouldNotReceive('uploadKycDocuments');
        $this->kycVerificationRepository->shouldNotReceive('save');

        expect(fn () => ($this->handler)($this->data))
            ->toThrow(CustomerCantSubmitKyc::class);
    });

    it('throws CustomerCantSubmitKyc when the customer kyc is processing', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();

        $this->customerRepository->shouldReceive('findById')->once()->andReturn($customer);
        $this->kycDocumentStorage->shouldNotReceive('uploadKycDocuments');

        expect(fn () => ($this->handler)($this->data))
            ->toThrow(CustomerCantSubmitKyc::class);
    });
});
