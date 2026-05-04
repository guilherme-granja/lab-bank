<?php

namespace Src\Domain\Accounts\Exceptions;

use DomainException;

class AccountNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: __('domain/accounts/validations.exceptions.account_not_found')
        );
    }

    public function getErrorCode(): string
    {
        return 'ACCOUNT_NOT_FOUND';
    }
}
