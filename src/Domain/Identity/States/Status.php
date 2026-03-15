<?php

namespace Src\Domain\Identity\States;

use Spatie\ModelStates\Exceptions\InvalidConfig;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;
use Src\Domain\Identity\States\Customer\Active;
use Src\Domain\Identity\States\Customer\Blocked;
use Src\Domain\Identity\States\Customer\Closed;
use Src\Domain\Identity\States\Customer\PendingKyc;

abstract class Status extends State
{
    /**
     * @throws InvalidConfig
     */
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(PendingKyc::class)
            ->allowTransition(PendingKyc::class, Active::class)
            ->allowTransition(Active::class, Blocked::class)
            ->allowTransition(Active::class, Closed::class)
            ->allowTransition(Blocked::class, Active::class);
    }
}
