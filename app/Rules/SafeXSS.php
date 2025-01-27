<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeXSS implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Step 1: Decode the value if it appears to be URL-encoded or base64 encoded
        $decodedValue = urldecode($value);
        $decodedValueBase64 = base64_decode($value, true);

        // If the decoded base64 string is valid, use it as the value
        if ($decodedValueBase64 !== false && base64_encode($decodedValueBase64) === $value) {
            $value = $decodedValueBase64;  // base64 decoded value
        } else {
            $value = $decodedValue;  // URL decoded value
        }

        // Convert value to lowercase to ignore case sensitivity
        $value = strtolower($value);

        // Step 2: Remove dangerous tags and attributes
        $value = $this->removeDangerousTags($value);

        // Step 3: Check for dangerous protocols (e.g., javascript, vbscript, data)
        if (preg_match('/(javascript|vbscript|data):/i', $value)) {
            $fail('The URL contains a potentially dangerous protocol.');
            return;
        }

        // Step 4: Check for dangerous XSS payloads using known patterns
        $xssPayloads = [
            // from the provided XSS payload list
            '<script.*?>.*?<\/script>',
            '<img.*?src=["\'].*?["\'].*?onerror=["\'].*?["\'].*?>',
            '<iframe.*?src=["\'].*?["\'].*?<\/iframe>',
            '<object.*?>.*?<\/object>',
            '<embed.*?>.*?<\/embed>',
            '<a.*?href=["\']javascript:.*?["\'].*?>',
            '<body.*?onload=["\'].*?["\'].*?>',
            '<meta.*?http-equiv=["\']refresh["\'].*?content=["\'].*?javascript:.*?["\'].*?>',
            '<svg.*?>.*?<\/svg>',
            // Add other payloads from the list here
            '<svg/onload=eval.*?>',
            '<img src="javascript:alert(1)">',
            '"><script>alert(1)</script>',
            '<img src="x" onerror="alert(1)">',
            '<div style="background-image: url(javascript:alert(1))">',
            // More patterns from the list can be added
        ];

        foreach ($xssPayloads as $payload) {
            if (preg_match('/' . $payload . '/is', $value)) {
                $fail('The URL contains a potentially dangerous XSS payload.');
                return;
            }
        }

        // Step 5: Additional validation - check for <iframe>, <script> and other dangerous HTML elements
        $encodedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        // Step 6: If encoded value differs from original value, report as dangerous
        if ($encodedValue !== $value) {
            $fail('The URL contains potentially unsafe content.');
            return;
        }

        // If everything is clear, return the sanitized and validated value
        return;
    }

    /**
     * Remove potentially dangerous HTML tags and attributes.
     *
     * @param  string  $value
     * @return string
     */
    protected function removeDangerousTags(string $value): string
    {
        // Convert value to lowercase for uniformity
        $value = strtolower($value);

        // Remove potentially dangerous HTML tags and their content
        $value = preg_replace('/<script.*?>.*?<\/script>/is', '', $value); // Remove <script> tags
        $value = preg_replace('/<iframe.*?>.*?<\/iframe>/is', '', $value);  // Remove <iframe> tags
        $value = preg_replace('/<object.*?>.*?<\/object>/is', '', $value);  // Remove <object> tags
        $value = preg_replace('/<embed.*?>.*?<\/embed>/is', '', $value);  // Remove <embed> tags
        $value = preg_replace('/<a.*?href=["\']javascript:.*?["\'].*?>/is', '', $value); // Remove <a href="javascript:">
        $value = preg_replace('/<img.*?src=["\'].*?["\'].*?onerror=["\'].*?["\'].*?>/is', '', $value);  // Remove <img onerror>
        $value = preg_replace('/<.*?on[a-z]+=["\'].*?["\'].*?>/is', '', $value); // Remove inline event handlers (onerror, onload, etc.)
        $value = preg_replace('/<.*?style=["\'].*?["\'].*?>/is', '', $value); // Remove inline style attributes

        return $value;
    }
}
