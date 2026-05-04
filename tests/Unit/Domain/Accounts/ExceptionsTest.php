<?php

use Src\Domain\Accounts\Exceptions\AccountNotActiveException;
use Src\Domain\Accounts\Exceptions\AccountNotFoundException;
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

describe('AccountNotFoundException', function () {
    it('has the correct error code', function () {
        $e = new AccountNotFoundException;
        expect($e->getErrorCode())->toBe('ACCOUNT_NOT_FOUND');
    });

    it('is a DomainException', function () {
        $e = new AccountNotFoundException;
        expect($e)->toBeInstanceOf(DomainException::class);
    });
});

describe('AccountNotActiveException', function () {
    it('has the correct error code', function () {
        $e = new AccountNotActiveException;
        expect($e->getErrorCode())->toBe('ACCOUNT_NOT_ACTIVE');
    });

    it('is a DomainException', function () {
        $e = new AccountNotActiveException;
        expect($e)->toBeInstanceOf(DomainException::class);
    });
});
