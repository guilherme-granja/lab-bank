<?php

namespace Src\Domain\Accounts\Exceptions;

use DomainException;

class CustomerInAccountAlreadyExistsException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: __('domain/accounts/validations.exceptions.customer_already_exists')
        );
    }

    public function getErrorCode(): string
    {
        return 'CLIENT_ALREADY_EXISTS';
    }
}
