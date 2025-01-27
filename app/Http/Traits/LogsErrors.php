<?php

namespace App\Http\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

trait LogsErrors
{
    /**
     * Log the error with a custom message and exception details.
     *
     * @param string $message Custom error message.
     * @param Exception $e The exception instance.
     * @param array $additionalData Additional data to include in the log.
     * @return void
     */
    public function logError(string $message, Exception $e, array $additionalData = []): void
    {
        Log::error($message, array_merge([
            'user_ip' => request()->getClientIp(),
            'exception_type' => get_class($e),
            'exception_message' => $e->getMessage(),
            'method' => __METHOD__,
        ], $additionalData));
    }
}
