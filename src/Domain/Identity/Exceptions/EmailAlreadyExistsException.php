<?php

namespace Src\Domain\Identity\Exceptions;

use DomainException;

class EmailAlreadyExistsException extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(
            message: __('domain/identity/validations.exceptions.email_already_exists', [
                'email' => $email,
            ])
        );
    }

    public function getErrorCode(): string
    {
        return 'EMAIL_ALREADY_EXISTS';
    }
}
