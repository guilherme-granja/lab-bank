<?php

namespace Src\Domain\Identity\Exceptions;

use DomainException;

class DocumentNotUploaded extends DomainException
{
    public function __construct(string $type)
    {
        parent::__construct(
            message: __('domain/identity/validations.exceptions.document_upload', [
                'document_type' => $type,
            ]),
        );
    }

    public function getErrorCode(): string
    {
        return 'UPLOAD_ERR_DOCUMENT';
    }
}
