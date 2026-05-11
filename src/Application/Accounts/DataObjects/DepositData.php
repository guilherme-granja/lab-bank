<?php

namespace Src\Application\Accounts\DataObjects;

use Illuminate\Http\Request;
use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Src\Shared\Traits\DataObjects\HasCorrelationId;

#[MapName(SnakeCaseMapper::class)]
class DepositData extends Data
{
    use HasCorrelationId;

    public function __construct(
        #[FromRouteParameter('accountId')]
        public string $accountId,
        #[Min(1)]
        public int $amount,
        public ?string $description,
    ) {
        $this->setCorrelationId(request()->header('X-Correlation-ID'));
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            accountId: $request->route('accountId'),
            amount: $request->input('amount'),
            description: $request->input('description'),
        );
    }
}
