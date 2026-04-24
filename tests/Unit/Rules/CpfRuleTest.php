<?php

use App\Rules\Cpf;

describe('Cpf validation rule', function () {
    it('passes for a valid CPF', function () {
        $rule = new Cpf;
        $failed = false;

        $rule->validate('cpf', '529.982.247-25', function () use (&$failed) {
            $failed = true;
        });

        expect($failed)->toBeFalse();
    });

    it('passes for a valid unformatted CPF', function () {
        $rule = new Cpf;
        $failed = false;

        $rule->validate('cpf', '52998224725', function () use (&$failed) {
            $failed = true;
        });

        expect($failed)->toBeFalse();
    });

    it('fails for a CPF with repeated digits', function () {
        $rule = new Cpf;
        $failed = false;

        $rule->validate('cpf', '111.111.111-11', function () use (&$failed) {
            $failed = true;
        });

        expect($failed)->toBeTrue();
    });

    it('fails for a CPF with wrong length', function () {
        $rule = new Cpf;
        $failed = false;

        $rule->validate('cpf', '1234567890', function () use (&$failed) {
            $failed = true;
        });

        expect($failed)->toBeTrue();
    });

    it('fails when the first check digit is wrong', function () {
        // Modify the 10th digit to make the first verifier fail but pass length/repeat checks
        $rule = new Cpf;
        $failed = false;

        $rule->validate('cpf', '52998224715', function () use (&$failed) {
            $failed = true;
        });

        expect($failed)->toBeTrue();
    });

    it('fails when the second check digit is wrong', function () {
        // First verifier passes but second fails
        $rule = new Cpf;
        $failed = false;

        $rule->validate('cpf', '52998224726', function () use (&$failed) {
            $failed = true;
        });

        expect($failed)->toBeTrue();
    });
});
