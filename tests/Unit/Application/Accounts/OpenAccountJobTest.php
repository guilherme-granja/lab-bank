<?php

use Illuminate\Support\Facades\Event;
use Src\Application\Accounts\DataObjects\OpenAccountData;
use Src\Application\Accounts\Handlers\OpenAccountHandler;
use Src\Interfaces\Jobs\Accounts\OpenAccountJob;

use function Pest\Laravel\mock;

describe('OpenAccountJob', function () {
    it('calls OpenAccountHandler with the correct customer id', function () {
        Event::fake();

        $handler = mock(OpenAccountHandler::class);
        $handler->shouldReceive('__invoke')
            ->once()
            ->with(Mockery::on(fn (OpenAccountData $data) => $data->customerId === 'customer-uuid'));

        $job = new OpenAccountJob(customerId: 'customer-uuid');
        $job->handle($handler);
    });
});
