<?php

namespace Src\Domain\Identity\Enums\Kyc;

use Src\Shared\Traits\Enum\ToArray;

enum DocumentTypeEnum: string
{
    use ToArray;

    case Cpf = 'cpf';
    case Rg = 'rg';
    case Cnh = 'cnh';
    case Passport = 'passport';
}
