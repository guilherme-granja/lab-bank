<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Src\Application\Accounts\DataObjects\DepositData;

describe('DepositData', function () {
    it('reads correlation id from the X-Correlation-ID header', function () {
        $uuid = (string) Str::uuid();
        request()->headers->set('X-Correlation-ID', $uuid);

        $data = new DepositData(accountId: 'a', amount: 100, description: 'd');

        expect($data->getCorrelationId())->toBe($uuid);
    });

    it('builds from a request via fromRequest()', function () {
        $uuid = (string) Str::uuid();
        request()->headers->set('X-Correlation-ID', $uuid);

        $request = Request::create('/api/v1/account/acc-123/deposit', 'POST', [
            'amount' => 250,
            'description' => 'salary',
        ]);
        $route = new Route('POST', 'account/{accountId}/deposit', []);
        $route->bind($request);
        $route->setParameter('accountId', 'acc-123');
        $request->setRouteResolver(fn () => $route);

        $data = DepositData::fromRequest($request);

        expect($data->accountId)->toBe('acc-123');
        expect($data->amount)->toBe(250);
        expect($data->description)->toBe('salary');
    });
});
