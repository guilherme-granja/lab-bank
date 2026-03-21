<?php

namespace Src\Domain\Identity\Exceptions;

use DomainException;
use Src\Domain\Identity\States\KycStatus;

class CustomerCantSubmitKyc extends DomainException
{
    public function __construct(KycStatus $kycStatus)
    {
        parent::__construct(
            message: __('domain/identity/validations.exceptions.customer_cant_submit', [
                'status' => $kycStatus->getValue(),
            ]),
        );
    }

    public function getErrorCode(): string
    {
        return 'CUSTOMER_CANT_SUBMIT_KYC';
    }
}
