<?php

namespace Src\Domain\Identity\Exceptions;

use DomainException;

class KycVerificationNotFound extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: __('domain/identity/validations.exceptions.kyc_verification_not_found')
        );
    }

    public function getErrorCode(): string
    {
        return 'KYC_VERIFICATION_NOT_FOUND';
    }
}
