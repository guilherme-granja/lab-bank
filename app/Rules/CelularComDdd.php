<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CelularComDdd implements ValidationRule
{
    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^\(\d{2}\)\s?\d{4,5}-\d{4}$/', $value)) {
            $fail('O campo :attribute não é um celular com DDD válido.');
        }
    }
}
