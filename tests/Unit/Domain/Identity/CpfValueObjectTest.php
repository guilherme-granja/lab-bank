<?php

use Src\Domain\Identity\ValueObjects\Cpf;
use Src\Shared\ValueObjects\ValueObject;

describe('Cpf ValueObject', function () {
    it('stores only digits stripping formatting', function () {
        $cpf = new Cpf('529.982.247-25');

        expect($cpf->digits())->toBe('52998224725');
    });

    it('accepts unformatted digits directly', function () {
        $cpf = new Cpf('52998224725');

        expect($cpf->digits())->toBe('52998224725');
    });

    it('formats cpf with dots and dash via toString', function () {
        $cpf = new Cpf('52998224725');

        expect($cpf->toString())->toBe('529.982.247-25');
    });

    it('formats cpf from formatted input correctly', function () {
        $cpf = new Cpf('529.982.247-25');

        expect($cpf->toString())->toBe('529.982.247-25');
    });

    it('returns true for equals when cpfs have same digits', function () {
        $cpfA = new Cpf('529.982.247-25');
        $cpfB = new Cpf('52998224725');

        expect($cpfA->equals($cpfB))->toBeTrue();
    });

    it('returns false for equals when cpfs have different digits', function () {
        $cpfA = new Cpf('52998224725');
        $cpfB = new Cpf('11144477735');

        expect($cpfA->equals($cpfB))->toBeFalse();
    });

    it('extends ValueObject base class', function () {
        $cpf = new Cpf('52998224725');

        expect($cpf)->toBeInstanceOf(ValueObject::class);
    });

    it('strips all non-digit characters', function () {
        $cpf = new Cpf('529-982-247.25');

        expect($cpf->digits())->toBe('52998224725');
    });
});
