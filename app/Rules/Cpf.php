<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Cpf implements ValidationRule
{
    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->passes($value)) {
            $fail('O campo :attribute não é um CPF válido.');
        }
    }

    private function passes(string $value): bool
    {
        $c = preg_replace('/\D/', '', $value);

        if (strlen($c) !== 11 || preg_match("/^{$c[0]}{11}$/", $c)) {
            return false;
        }

        for ($s = 10, $n = 0, $i = 0; $s >= 2; $s--) {
            $n += $c[$i++] * $s;
        }

        if ($c[9] !== ((($n %= 11) < 2) ? 0 : 11 - $n)) {
            return false;
        }

        for ($s = 11, $n = 0, $i = 0; $s >= 2; $s--) {
            $n += $c[$i++] * $s;
        }

        return $c[10] === ((($n %= 11) < 2) ? 0 : 11 - $n);
    }
}
