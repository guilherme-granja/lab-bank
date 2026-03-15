<?php

namespace Src\Infrastructure\Auth;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

class PersonalAccessToken extends SanctumToken
{
    protected $connection = 'identity';
}
