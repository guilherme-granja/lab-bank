<?php

namespace Src\Domain\Accounts\Exceptions;

use DomainException;

class AccountNotActiveException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: __('domain/accounts/validations.exceptions.account_not_active')
        );
    }

    public function getErrorCode(): string
    {
        return 'ACCOUNT_NOT_ACTIVE';
    }
}
