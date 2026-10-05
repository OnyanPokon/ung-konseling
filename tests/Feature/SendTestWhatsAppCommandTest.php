<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SendTestWhatsAppCommandTest extends TestCase
{
    public function test_it_prints_the_normalized_number_and_reports_success()
    {
        Http::fake([
            'api.fonnte.com/*' => Http::response(['status' => true, 'detail' => 'success']),
        ]);

        $this->artisan('whatsapp:test', [
            'target' => '081234567890',
            'message' => 'test message',
        ])
            ->expectsOutputToContain('Sending to normalized target: 6281234567890')
            ->expectsOutputToContain('Message sent successfully.')
            ->assertSuccessful();

        Http::assertSent(function ($request) {
            return $request['target'] === '6281234567890' && $request['message'] === 'test message';
        });
    }

    public function test_it_reports_failure_and_prints_the_gateway_reason()
    {
        Http::fake([
            'api.fonnte.com/*' => Http::response(['status' => false, 'reason' => 'insufficient quota']),
        ]);

        $this->artisan('whatsapp:test', [
            'target' => '081234567890',
            'message' => 'test message',
        ])
            ->expectsOutputToContain('Sending to normalized target: 6281234567890')
            ->expectsOutputToContain('Failed to send message: insufficient quota')
            ->assertFailed();
    }

    public function test_it_fails_immediately_if_number_cannot_be_normalized()
    {
        Http::fake();

        $this->artisan('whatsapp:test', [
            'target' => 'invalid',
        ])
            ->expectsOutputToContain("The phone number 'invalid' holds no usable digits.")
            ->assertFailed();

        Http::assertNothingSent();
    }
}
