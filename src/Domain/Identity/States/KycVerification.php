<?php

namespace Src\Domain\Identity\States;

use Spatie\ModelStates\Exceptions\InvalidConfig;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;
use Src\Domain\Identity\States\KycVerification\Approved;
use Src\Domain\Identity\States\KycVerification\Expired;
use Src\Domain\Identity\States\KycVerification\Pending;
use Src\Domain\Identity\States\KycVerification\Processing;
use Src\Domain\Identity\States\KycVerification\Rejected;

abstract class KycVerification extends State
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
            ->allowTransition(Pending::class, Expired::class);
    }
}
