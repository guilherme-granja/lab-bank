<?php

namespace Src\Domain\Accounts\States;

use Spatie\ModelStates\Exceptions\InvalidConfig;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;
use Src\Domain\Accounts\States\Account\Active;
use Src\Domain\Accounts\States\Account\Blocked;
use Src\Domain\Accounts\States\Account\Closed;
use Src\Domain\Accounts\States\Account\Pending;

abstract class AccountStatus extends State
{
    /**
     * @throws InvalidConfig
     */
    public static function config(): StateConfig
    {
        return parent::config()
            ->registerState([
                Pending::class,
                Active::class,
                Closed::class,
                Blocked::class,
            ])
            ->default(Pending::class)
            ->allowTransition(Pending::class, Active::class)
            ->allowTransition(Active::class, Blocked::class)
            ->allowTransition(Active::class, Closed::class)
            ->allowTransition(Blocked::class, Active::class);
    }
}
