<?php

use Src\Domain\Accounts\Exceptions\CustomerInAccountAlreadyExistsException;

describe('CustomerInAccountAlreadyExistsException', function () {
    it('has the correct error code', function () {
        $e = new CustomerInAccountAlreadyExistsException;
        expect($e->getErrorCode())->toBe('CLIENT_ALREADY_EXISTS');
    });

    it('is a DomainException', function () {
        $e = new CustomerInAccountAlreadyExistsException;
        expect($e)->toBeInstanceOf(DomainException::class);
    });
});
