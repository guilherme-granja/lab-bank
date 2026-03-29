<?php

namespace Src\Domain\Accounts\States;

use Spatie\ModelStates\Exceptions\InvalidConfig;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;
use Src\Domain\Accounts\States\Transaction\Completed;
use Src\Domain\Accounts\States\Transaction\Failed;
use Src\Domain\Accounts\States\Transaction\Initiated;
use Src\Domain\Accounts\States\Transaction\Processing;
use Src\Domain\Accounts\States\Transaction\Reversed;

abstract class TransactionStatus extends State
{
    /**
     * @throws InvalidConfig
     */
    public static function config(): StateConfig
    {
        return parent::config()
            ->registerState([
                Initiated::class,
                Processing::class,
                Completed::class,
                Failed::class,
                Reversed::class,
            ])
            ->default(Initiated::class)
            ->allowTransition(Initiated::class, Processing::class)
            ->allowTransition(Processing::class, Completed::class)
            ->allowTransition(Processing::class, Failed::class)
            ->allowTransition(Failed::class, Reversed::class);
    }
}
