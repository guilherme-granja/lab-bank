<?php

use Illuminate\Support\Facades\DB;
use Src\Infrastructure\Services\SequenceService;

describe('SequenceService', function () {
    it('generates a sequential account number', function () {
        $service = new SequenceService;

        $first = $service->generateAccountNumberSequence();
        $second = $service->generateAccountNumberSequence();

        expect((int) $second)->toBe((int) $first + 1);
    });

    it('returns a string', function () {
        $service = new SequenceService;

        expect($service->generateAccountNumberSequence())->toBeString();
    });

    it('each call increments the last_value in the sequences table', function () {
        $service = new SequenceService;

        $before = DB::connection('accounts')
            ->table('sequences')
            ->where('name', 'account_number')
            ->value('last_value');

        $service->generateAccountNumberSequence();

        $after = DB::connection('accounts')
            ->table('sequences')
            ->where('name', 'account_number')
            ->value('last_value');

        expect((int) $after)->toBe((int) $before + 1);
    });
});
