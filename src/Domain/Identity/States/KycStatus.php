<?php

namespace Src\Domain\Identity\States;

use Spatie\ModelStates\Exceptions\InvalidConfig;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;
use Src\Domain\Identity\States\Kyc\Approved;
use Src\Domain\Identity\States\Kyc\Expired;
use Src\Domain\Identity\States\Kyc\Pending;
use Src\Domain\Identity\States\Kyc\Processing;
use Src\Domain\Identity\States\Kyc\Rejected;

abstract class KycStatus extends State
{
    /**
     * @throws InvalidConfig
     */
    public static function config(): StateConfig
    {
        return parent::config()
            ->registerState([
                Pending::class,
                Processing::class,
                Approved::class,
                Rejected::class,
                Expired::class,
            ])
            ->default(Pending::class)
            ->allowTransition(Pending::class, Processing::class)
            ->allowTransition(Processing::class, Approved::class)
            ->allowTransition(Processing::class, Rejected::class)
            ->allowTransition(Rejected::class, Pending::class)
            ->allowTransition(Approved::class, Expired::class)
            ->allowTransition(Expired::class, Pending::class);
    }
}
