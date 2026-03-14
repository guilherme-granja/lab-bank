<?php

namespace Src\Domain\Identity\ValueObjects;

use Src\Shared\ValueObjects\ValueObject;

class Cpf extends ValueObject
{
    public function __construct(
        protected string $cpf,
    ) {
        $this->cpf = str($this->cpf)
            ->replaceMatches('/\D/', '')
            ->toString();
    }

    public function equals(ValueObject $other): bool
    {
        return $this->cpf === $other->cpf;
    }

    public function toString(): string
    {
        return str($this->cpf)
            ->replaceMatches('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4')
            ->toString();
    }

    public function digits(): string
    {
        return $this->cpf;
    }
}
