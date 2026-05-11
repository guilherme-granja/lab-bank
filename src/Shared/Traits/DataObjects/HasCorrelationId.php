<?php

namespace Src\Shared\Traits\DataObjects;

trait HasCorrelationId
{
    private string $correlationId;

    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }

    public function setCorrelationId(string $correlationId): void
    {
        $this->correlationId = $correlationId;
    }
}
