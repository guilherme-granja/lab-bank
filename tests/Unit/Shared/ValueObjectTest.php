<?php

use Src\Domain\Identity\ValueObjects\Cpf;

describe('ValueObject', function () {
    it('__toString delegates to toString()', function () {
        $cpf = new Cpf('52998224725');

        expect((string) $cpf)->toBe($cpf->toString());
    });

    it('string cast returns the formatted value', function () {
        $cpf = new Cpf('529.982.247-25');

        expect((string) $cpf)->toBe('529.982.247-25');
    });
});
