<?php

use Illuminate\Support\Collection;
use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Identity\Enums\Kyc\DocumentTypeEnum;

describe('ToArray trait', function () {
    it('names() returns all case names', function () {
        expect(DocumentTypeEnum::names())->toBe(['Cpf', 'Rg', 'Cnh', 'Passport']);
    });

    it('values() returns all case values', function () {
        expect(DocumentTypeEnum::values())->toBe(['cpf', 'rg', 'cnh', 'passport']);
    });

    it('array() returns name => value map', function () {
        expect(DocumentTypeEnum::array())->toBe([
            'Cpf' => 'cpf',
            'Rg' => 'rg',
            'Cnh' => 'cnh',
            'Passport' => 'passport',
        ]);
    });

    it('collect() returns a Laravel collection', function () {
        $collection = DocumentTypeEnum::collect();

        expect($collection)->toBeInstanceOf(Collection::class);
        expect($collection->count())->toBe(4);
    });

    it('works with AccountTypeEnum', function () {
        expect(AccountTypeEnum::values())->toBe(['checking', 'savings']);
        expect(AccountTypeEnum::names())->toBe(['Checking', 'Savings']);
    });
});
