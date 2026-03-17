<?php

namespace Src\Shared\Traits\Enum;

use Illuminate\Support\Collection;

trait ToArray
{
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function array(): array
    {
        return array_combine(self::names(), self::values());
    }

    public static function collect(): Collection
    {
        return collect(self::array());
    }
}
