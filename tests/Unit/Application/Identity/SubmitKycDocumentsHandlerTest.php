<?php

use Database\Factories\CustomerFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Src\Application\Identity\DataObjects\KycDocumentStorageData;
use Src\Application\Identity\DataObjects\SubmitKycDocumentsData;
use Src\Application\Identity\Handlers\SubmitKycDocumentsHandler;
use Src\Domain\Identity\Enums\Kyc\DocumentTypeEnum;
use Src\Domain\Identity\Exceptions\CustomerCantSubmitKyc;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Models\KycVerification;
use Src\Infrastructure\Storage\KycDocumentStorage;

use function Pest\Laravel\mock;

beforeEach(function () {
    Event::fake();

    $this->kycDocumentStorage = mock(KycDocumentStorage::class);

    $this->handler = new SubmitKycDocumentsHandler($this->kycDocumentStorage);

    $this->makeData = fn (string $customerId) => new SubmitKycDocumentsData(
        customerId: $customerId,
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

        $this->kycDocumentStorage->shouldReceive('uploadKycDocuments')->once()->andReturn(KycDocumentStorageData::from([
            'document_front_url' => 'kyc/id/front.jpg',
            'document_back_url' => 'kyc/id/back.jpg',
            'document_selfie_url' => 'kyc/id/selfie.jpg',
        ]));

        ($this->handler)(($this->makeData)($customer->id));

        expect(KycVerification::where('customer_id', $customer->id)->exists())->toBeTrue();
    });

    it('throws CustomerNotFoundException when the customer does not exist', function () {
        expect(fn () => ($this->handler)(($this->makeData)('00000000-0000-0000-0000-000000000000')))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('does not upload documents when the customer does not exist', function () {
        $this->kycDocumentStorage->shouldNotReceive('uploadKycDocuments');

        expect(fn () => ($this->handler)(($this->makeData)('00000000-0000-0000-0000-000000000000')))
            ->toThrow(CustomerNotFoundException::class);
    });

    it('does not persist a verification when the customer does not exist', function () {
        expect(fn () => ($this->handler)(($this->makeData)('00000000-0000-0000-0000-000000000000')))
            ->toThrow(CustomerNotFoundException::class);

        expect(KycVerification::count())->toBe(0);
    });

    it('throws CustomerCantSubmitKyc when the customer kyc is already approved', function () {
        $customer = CustomerFactory::new()->withKycApproved()->create();
        $this->kycDocumentStorage->shouldNotReceive('uploadKycDocuments');

        expect(fn () => ($this->handler)(($this->makeData)($customer->id)))
            ->toThrow(CustomerCantSubmitKyc::class);
    });

    it('throws CustomerCantSubmitKyc when the customer kyc is processing', function () {
        $customer = CustomerFactory::new()->withKycProcessing()->create();
        $this->kycDocumentStorage->shouldNotReceive('uploadKycDocuments');

        expect(fn () => ($this->handler)(($this->makeData)($customer->id)))
            ->toThrow(CustomerCantSubmitKyc::class);
    });
});
