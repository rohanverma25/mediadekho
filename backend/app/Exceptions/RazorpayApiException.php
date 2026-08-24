<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;

/**
 * Thrown when Razorpay's API itself rejects/fails a request. The real
 * error detail is logged by the caller — this exception's message is
 * generic on purpose so it's always safe to surface to the customer.
 */
class RazorpayApiException extends \RuntimeException
{
    public function __construct(string $logMessage = '')
    {
        parent::__construct('We could not start the payment. Please try again in a moment.');

        if ($logMessage !== '') {
            Log::error('Razorpay API error: '.$logMessage);
        }
    }
}
