<?php

namespace Src\Domain\Identity\Exceptions;

use DomainException;

class CustomerNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: __('domain/identity/validations.exceptions.customer_not_found')
        );
    }

    public function getErrorCode(): string
    {
        return 'CUSTOMER_NOT_FOUND';
    }
}
