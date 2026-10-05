<?php

namespace App\Console\Commands;

use App\Enums\SesiKonselingEvent;
use App\Http\Services\FonnteService;
use Illuminate\Console\Command;

class SendTestWhatsApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test {target : The phone number to send to} {message? : The message to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test WhatsApp message via Fonnte to verify delivery';

    /**
     * Execute the console command.
     */
    public function handle(FonnteService $fonnte)
    {
        $target = $this->argument('target');
        $message = $this->argument('message') ?: 'This is a test message from the server console.';

        $normalized = $fonnte->normalizePhoneNumber($target);

        if ($normalized === null) {
            $this->error("The phone number '{$target}' holds no usable digits.");

            return self::FAILURE;
        }

        $this->info("Sending to normalized target: {$normalized}");

        $result = $fonnte->sendMessage($normalized, $message, SesiKonselingEvent::Approval);

        if ($result['success']) {
            $this->info('Message sent successfully.');

            return self::SUCCESS;
        }

        $this->error('Failed to send message: '.$result['reason']);

        return self::FAILURE;
    }
}
