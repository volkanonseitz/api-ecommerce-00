<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPasswordRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen($value) < 12) {
            $fail('Password minimal 12 karakter');

            return;
        }

        if (! preg_match('/[A-Z]/', $value)) {
            $fail('Password harus mengandung minimal 1 huruf kapital');

            return;
        }

        if (! preg_match('/[a-z]/', $value)) {
            $fail('Password harus mengandung minimal 1 huruf kecil');

            return;
        }

        if (! preg_match('/[0-9]/', $value)) {
            $fail('Password harus mengandung minimal 1 angka');

            return;
        }

        if (! preg_match('/[!@#$%^&*(),.?":{}|<>]/', $value)) {
            $fail('Password harus mengandung minimal 1 karakter spesial');

            return;
        }

        // Prevent sequential characters
        if ($this->hasSequentialChars($value)) {
            $fail('Password tidak boleh berisi karakter berurutan (contoh: abc, 123)');

            return;
        }

        // Prevent repeating characters
        if ($this->hasRepeatingChars($value)) {
            $fail('Password tidak boleh berisi karakter berulang (contoh: aaa, 111)');

            return;
        }

        // Prevent common passwords
        if ($this->isCommonPassword($value)) {
            $fail('Password terlalu umum, gunakan kombinasi yang lebih unik');

            return;
        }
    }

    private function hasSequentialChars(string $password): bool
    {
        $length = strlen($password);

        for ($i = 0; $i < $length - 2; $i++) {
            $char1 = ord($password[$i]);
            $char2 = ord($password[$i + 1]);
            $char3 = ord($password[$i + 2]);

            // Check for sequential ASCII values (case-insensitive)
            if (abs($char1 - $char2) === 1 && abs($char2 - $char3) === 1) {
                return true;
            }

            // Check for keyboard sequential patterns (qwerty, asdf, etc.)
            $triplet = strtolower(substr($password, $i, 3));
            $keyboardSequences = ['qwe', 'wer', 'ert', 'rty', 'tyu', 'yui', 'uio', 'iop',
                'asd', 'sdf', 'dfg', 'fgh', 'ghj', 'hjk', 'jkl',
                'zxc', 'xcv', 'cvb', 'vbn', 'bnm',
                '123', '234', '345', '456', '567', '678', '789', '890'];

            if (in_array($triplet, $keyboardSequences)) {
                return true;
            }
        }

        return false;
    }

    private function hasRepeatingChars(string $password): bool
    {
        return preg_match('/([A-Za-z0-9])\1{2,}/', $password) === 1;
    }

    private function isCommonPassword(string $password): bool
    {
        $commonPasswords = [
            'password', 'password123', '123456', '12345678', '123456789',
            'qwerty', 'abc123', 'admin123', 'letmein', 'welcome',
            'monkey', 'dragon', 'baseball', 'football', 'superman',
            'iloveyou', 'trustno1', 'sunshine', 'master', 'hello',
            'freedom', 'whatever', 'qwertyuiop', 'asdfghjkl', 'zxcvbnm',
            'password1', 'password!', 'Password', 'Password123',
        ];

        $lowerPassword = strtolower($password);

        foreach ($commonPasswords as $common) {
            if ($lowerPassword === strtolower($common) ||
                str_contains($lowerPassword, strtolower($common))) {
                return true;
            }
        }

        return false;
    }
}
