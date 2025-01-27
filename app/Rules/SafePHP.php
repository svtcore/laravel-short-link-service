<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafePHP implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $decodedValue = urldecode($value);
        $decodedValueBase64 = base64_decode($value, true);

        if ($decodedValueBase64 !== false && base64_encode($decodedValueBase64) === $value) {
            $value = $decodedValueBase64;
        } else {
            $value = $decodedValue;
        }

        $value = strtolower($value);

        $forbiddenCommands = [
            'exec', 'system', 'shell_exec', 'passthru', 'popen', 'proc_open', 'eval', 'assert', 
            'base64_decode', 'shell_exec', 'phpinfo', 'proc_open', 'fopen', 'fsockopen', 
            'popen', 'mkdir', 'touch', 'chmod', 'chown', 'unlink', 'rmdir'
        ];

        foreach ($forbiddenCommands as $command) {
            if (stripos($value, $command) !== false) {
                $fail('The input contains potentially dangerous PHP or shell command.');
                return;
            }
        }

        $dangerousSymbols = [';', '&', '|', '&&', '>', '<', '`', '$(', '$( )', '*/'];
        foreach ($dangerousSymbols as $symbol) {
            if (stripos($value, $symbol) !== false) {
                $fail('The input contains potentially dangerous symbols.');
                return;
            }
        }

        return;
    }
}
