<?php

namespace Src\Shared\ValueObjects;

abstract class ValueObject
{
    abstract public function equals(self $other): bool;

    abstract public function toString(): string;

    public function __toString(): string
    {
        return $this->toString();
    }
}
