<?php

namespace App\Http\Services;

use App\Enums\SesiKonselingEvent;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    private const SEND_URL = 'https://api.fonnte.com/send';

    // Bounded and never retried: a hanging gateway must not hold a counselor's
    // approval open waiting on a message that is not part of the transaction.
    private const TIMEOUT_SECONDS = 5;

    /**
     * Send a WhatsApp message and report what the gateway said about it.
     *
     * The event travels with the send so a failure in the log can be traced
     * back to the schedule change that caused it.
     *
     * @return array{success: bool, target: string|null, reason: string|null, response: string|null}
     */
    public function sendMessage(?string $target, string $message, SesiKonselingEvent $event): array
    {
        $number = $this->normalizePhoneNumber($target);

        if ($number === null) {
            Log::warning('Fonnte send skipped: phone number holds no usable digits.', [
                'event' => $event->value,
                'target' => $target,
            ]);

            return $this->result(false, null, 'phone number holds no usable digits');
        }

        try {
            $response = Http::withHeaders(['Authorization' => config('services.fonnte.token')])
                ->timeout(self::TIMEOUT_SECONDS)
                ->retry(1, 0)
                ->post(self::SEND_URL, [
                    'target' => $number,
                    'message' => $message,
                    'countryCode' => (string) config('services.fonnte.country_code'),
                ]);
        } catch (ConnectionException $e) {
            Log::error('Fonnte send failed: gateway could not be reached.', [
                'event' => $event->value,
                'target' => $number,
                'reason' => $e->getMessage(),
                'response' => null,
            ]);

            return $this->result(false, $number, $e->getMessage());
        }

        $body = $response->json() ?? [];

        if ($response->successful() && ($body['status'] ?? null) === true) {
            return $this->result(true, $number, null);
        }

        $reason = $this->gatewayReason($body);

        Log::error('Fonnte send rejected by gateway.', [
            'event' => $event->value,
            'target' => $number,
            'reason' => $reason,
            'response' => $response->body(),
        ]);

        return $this->result(false, $number, $reason, $response->body());
    }

    /**
     * Turn a number as typed at registration into one the gateway can dial.
     *
     * @return string|null The dialable number, or null when it holds nothing to dial.
     */
    public function normalizePhoneNumber(?string $target): ?string
    {
        if ($target === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $target);

        if ($digits === '') {
            return null;
        }

        $countryCode = (string) config('services.fonnte.country_code');

        if (str_starts_with($digits, '0')) {
            $digits = $countryCode.substr($digits, 1);
        }

        // A bare country code leaves no subscriber number behind it.
        return strlen($digits) > strlen($countryCode) ? $digits : null;
    }

    private function gatewayReason(array $body): string
    {
        return $body['reason'] ?? $body['detail'] ?? $body['message'] ?? 'gateway gave no reason';
    }

    /**
     * @return array{success: bool, target: string|null, reason: string|null, response: string|null}
     */
    private function result(bool $success, ?string $target, ?string $reason, ?string $response = null): array
    {
        return compact('success', 'target', 'reason', 'response');
    }
}
