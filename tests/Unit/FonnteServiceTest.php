<?php

namespace Tests\Unit;

use App\Enums\SesiKonselingEvent;
use App\Http\Services\FonnteService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class FonnteServiceTest extends TestCase
{
    private const TOKEN = 'fonnte-token-from-config';

    private const SUCCESS_BODY = ['status' => true, 'detail' => 'success'];

    private FonnteService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.fonnte.token' => self::TOKEN,
            'services.fonnte.country_code' => '62',
        ]);

        $this->service = app(FonnteService::class);
    }

    public function test_it_normalizes_a_number_typed_with_a_leading_zero(): void
    {
        $this->assertSame('6281234567890', $this->service->normalizePhoneNumber('081234567890'));
    }

    public function test_it_leaves_an_already_prefixed_number_unchanged(): void
    {
        $this->assertSame('6281234567890', $this->service->normalizePhoneNumber('6281234567890'));
    }

    public function test_it_removes_a_leading_plus(): void
    {
        $this->assertSame('6281234567890', $this->service->normalizePhoneNumber('+6281234567890'));
    }

    public function test_it_strips_dashes_spaces_dots_and_parentheses(): void
    {
        $this->assertSame('6281234567890', $this->service->normalizePhoneNumber('0812-3456-7890'));
        $this->assertSame('6281234567890', $this->service->normalizePhoneNumber('0812 3456 7890'));
        $this->assertSame('6281234567890', $this->service->normalizePhoneNumber('0812.3456.7890'));
        $this->assertSame('6281234567890', $this->service->normalizePhoneNumber('(0812) 3456-7890'));
    }

    public function test_it_normalizes_every_accepted_format_to_the_same_outbound_target(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(self::SUCCESS_BODY)]);

        foreach (['081234567890', '6281234567890', '+6281234567890', '(0812) 3456-7890'] as $stored) {
            $this->service->sendMessage($stored, 'halo', SesiKonselingEvent::Approval);

            Http::assertSent(function (Request $request) {
                return $request['target'] === '6281234567890';
            });
        }
    }

    public function test_it_sends_no_request_and_logs_a_warning_for_a_number_without_usable_digits(): void
    {
        Http::fake();
        Log::spy();

        $result = $this->service->sendMessage('bukan nomor', 'halo', SesiKonselingEvent::Approval);

        Http::assertNothingSent();
        Log::shouldHaveReceived('warning')->once();
        $this->assertFalse($result['success']);
    }

    public function test_it_treats_an_empty_number_as_unusable(): void
    {
        $this->assertNull($this->service->normalizePhoneNumber(null));
        $this->assertNull($this->service->normalizePhoneNumber(''));
        $this->assertNull($this->service->normalizePhoneNumber('0'));
    }

    public function test_it_authenticates_with_the_configured_token_and_no_bearer_prefix(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(self::SUCCESS_BODY)]);

        $this->service->sendMessage('081234567890', 'halo', SesiKonselingEvent::Approval);

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', self::TOKEN);
        });
    }

    public function test_it_sends_the_target_as_a_string(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(self::SUCCESS_BODY)]);

        $this->service->sendMessage('081234567890', 'halo', SesiKonselingEvent::Approval);

        Http::assertSent(function (Request $request) {
            return is_string($request['target']);
        });
    }

    public function test_it_takes_the_country_code_from_configuration(): void
    {
        config(['services.fonnte.country_code' => '1']);

        Http::fake(['api.fonnte.com/*' => Http::response(self::SUCCESS_BODY)]);

        $this->service->sendMessage('081234567890', 'halo', SesiKonselingEvent::Approval);

        Http::assertSent(function (Request $request) {
            return $request['countryCode'] === '1'
                && $request['target'] === '181234567890';
        });
    }

    public function test_it_returns_a_successful_result_when_the_gateway_accepts_the_message(): void
    {
        Http::fake(['api.fonnte.com/*' => Http::response(self::SUCCESS_BODY)]);

        $result = $this->service->sendMessage('081234567890', 'halo', SesiKonselingEvent::Approval);

        $this->assertTrue($result['success']);
        $this->assertSame('6281234567890', $result['target']);
        $this->assertNull($result['reason']);
    }

    public function test_it_returns_the_gateway_reason_and_logs_the_failure_details(): void
    {
        Http::fake([
            'api.fonnte.com/*' => Http::response(['status' => false, 'reason' => 'insufficient quota']),
        ]);
        Log::spy();

        $result = $this->service->sendMessage('081234567890', 'halo', SesiKonselingEvent::Approval);

        $this->assertFalse($result['success']);
        $this->assertSame('insufficient quota', $result['reason']);
        $this->assertStringContainsString('insufficient quota', $result['response']);

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['target'] === '6281234567890'
                    && $context['reason'] === 'insufficient quota'
                    && str_contains($context['response'], 'insufficient quota');
            });
    }

    public function test_it_returns_a_failure_result_when_the_gateway_cannot_be_reached(): void
    {
        Http::fake(function () {
            throw new ConnectionException('cURL error 28: Operation timed out');
        });
        Log::spy();

        $result = $this->service->sendMessage('081234567890', 'halo', SesiKonselingEvent::Approval);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('timed out', $result['reason']);

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['target'] === '6281234567890'
                    && str_contains($context['reason'], 'timed out');
            });
    }
}
