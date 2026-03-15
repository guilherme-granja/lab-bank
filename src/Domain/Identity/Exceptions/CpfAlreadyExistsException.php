<?php

namespace Src\Domain\Identity\Exceptions;

use DomainException;

class CpfAlreadyExistsException extends DomainException
{
    public function __construct(string $cpf)
    {
        parent::__construct(
            message: __('validations.exceptions.cpf_already_exists', [
                'cpf' => $cpf,
            ])
        );
    }

    public function getErrorCode(): string
    {
        return 'CPF_ALREADY_EXISTS';
    }
}
