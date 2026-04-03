<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->isValidCpf($value)) {
            $fail('O campo :attribute não é um CPF válido.');
        }
    }

    private function isValidCpf(string $value): bool
    {
        $cpf = $this->onlyNumbers($value);

        if (! $this->hasValidLength($cpf) || $this->isRepeatedSequence($cpf)) {
            return false;
        }

        return $this->validateDigits($cpf);
    }

    private function onlyNumbers(string $value): string
    {
        return preg_replace('/\D/', '', $value);
    }

    private function hasValidLength(string $cpf): bool
    {
        return strlen($cpf) === 11;
    }

    private function isRepeatedSequence(string $cpf): bool
    {
        return preg_match('/^(\d)\1{10}$/', $cpf) === 1;
    }

    private function validateDigits(string $cpf): bool
    {
        $sum = 0;
        for ($i = 0, $weight = 10; $weight >= 2; $i++, $weight--) {
            $sum += (int) $cpf[$i] * $weight;
        }

        $firstDigit = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);

        if ((int) $cpf[9] !== $firstDigit) {
            return false;
        }

        $sum = 0;
        for ($i = 0, $weight = 11; $weight >= 2; $i++, $weight--) {
            $sum += (int) $cpf[$i] * $weight;
        }

        $secondDigit = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);

        return (int) $cpf[10] === $secondDigit;
    }
}
