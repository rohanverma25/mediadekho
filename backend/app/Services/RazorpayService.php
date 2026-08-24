<?php

namespace App\Services;

use App\Exceptions\RazorpayApiException;
use App\Exceptions\RazorpayNotConfiguredException;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class RazorpayService
{
    private const API_BASE = 'https://api.razorpay.com/v1';

    /**
     * @return array<string, mixed> the decoded Razorpay order object
     *
     * @throws RazorpayNotConfiguredException
     * @throws RazorpayApiException
     */
    public function createOrder(int $amountInPaise, string $receipt, array $notes = []): array
    {
        [$keyId, $keySecret] = $this->credentials();

        $response = Http::withBasicAuth($keyId, $keySecret)
            ->asJson()
            ->post(self::API_BASE.'/orders', [
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'receipt' => $receipt,
                'notes' => $notes,
            ]);

        if (! $response->successful()) {
            throw new RazorpayApiException('createOrder failed: '.$response->body());
        }

        return $response->json();
    }

    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        [, $keySecret] = $this->credentials();

        $expected = hash_hmac('sha256', "{$orderId}|{$paymentId}", $keySecret);

        return hash_equals($expected, $signature);
    }

    /**
     * @return array{0: string, 1: string}
     *
     * @throws RazorpayNotConfiguredException
     */
    private function credentials(): array
    {
        $setting = Setting::current();
        $keyId = $setting->razorpay_key_id;
        $keySecret = $setting->razorpay_key_secret;

        if (blank($keyId) || blank($keySecret)) {
            throw new RazorpayNotConfiguredException();
        }

        return [$keyId, (string) $keySecret];
    }
}
