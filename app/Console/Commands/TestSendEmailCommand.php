<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestSendEmailCommand extends Command
{
    protected $signature = 'mail:send-test {email=flavourflow006@gmail.com}';

    protected $description = 'Send a test email using configured SMTP settings';

    public function handle(): int
    {
        $recipient = $this->argument('email');
        $this->info("Sending test email via Gmail SMTP to: {$recipient}");

        try {
            Mail::raw('Hello! This is a test email from FlavourFlow system to verify Gmail SMTP SSL connection.', function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('FlavourFlow SMTP Real Test - '.now()->toDateTimeString());
            });

            $this->info("✓ Test email sent successfully to {$recipient}!");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('✗ Failed to send email: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
