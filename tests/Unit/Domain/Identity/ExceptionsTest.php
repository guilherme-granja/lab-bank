<?php

use Database\Factories\CustomerFactory;
use Illuminate\Support\Facades\Event;
use Src\Domain\Identity\Exceptions\CpfAlreadyExistsException;
use Src\Domain\Identity\Exceptions\CustomerCantSubmitKyc;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\DocumentNotUploaded;
use Src\Domain\Identity\Exceptions\EmailAlreadyExistsException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;

beforeEach(function () {
    Event::fake();
});

describe('CpfAlreadyExistsException', function () {
    it('has the correct error code', function () {
        $e = new CpfAlreadyExistsException('52998224725');
        expect($e->getErrorCode())->toBe('CPF_ALREADY_EXISTS');
    });
});

describe('EmailAlreadyExistsException', function () {
    it('has the correct error code', function () {
        $e = new EmailAlreadyExistsException('joao@example.com');
        expect($e->getErrorCode())->toBe('EMAIL_ALREADY_EXISTS');
    });
});

describe('CustomerNotFoundException', function () {
    it('has the correct error code', function () {
        $e = new CustomerNotFoundException;
        expect($e->getErrorCode())->toBe('CUSTOMER_NOT_FOUND');
    });
});

describe('KycVerificationNotFound', function () {
    it('has the correct error code', function () {
        $e = new KycVerificationNotFound;
        expect($e->getErrorCode())->toBe('KYC_VERIFICATION_NOT_FOUND');
    });
});

describe('CustomerCantSubmitKyc', function () {
    it('has the correct error code', function () {
        $customer = CustomerFactory::new()->withKycApproved()->make();
        $e = new CustomerCantSubmitKyc($customer->kyc_status);
        expect($e->getErrorCode())->toBe('CUSTOMER_CANT_SUBMIT_KYC');
    });
});

describe('DocumentNotUploaded', function () {
    it('has the correct error code', function () {
        $e = new DocumentNotUploaded('document_front');
        expect($e->getErrorCode())->toBe('UPLOAD_ERR_DOCUMENT');
    });
});
