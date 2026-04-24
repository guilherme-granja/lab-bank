<?php

namespace Src\Interfaces\Jobs\Accounts;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Application\Accounts\DataObjects\OpenAccountData;
use Src\Application\Accounts\Handlers\OpenAccountHandler;
use Throwable;

#[Queue('accounts')]
#[Tries(3)]
#[Backoff(60)]
class OpenAccountJob implements ShouldBeEncrypted, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $customerId,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(OpenAccountHandler $handler): void
    {
        ($handler)(OpenAccountData::from(['customer_id' => $this->customerId]));
    }
}
