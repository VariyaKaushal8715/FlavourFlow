<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendTestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {to? : Target email address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email using configured Gmail SMTP credentials';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $recipient = $this->argument('to') ?: config('mail.from.address');

        $this->info("Sending test email to: {$recipient}");

        try {
            Mail::raw("Hello from FlavourFlow!\n\nThis is a test email confirming that your Gmail SMTP configuration is working correctly.", function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('FlavourFlow Gmail SMTP Test Email');
            });

            $this->info('Test email successfully sent via Gmail SMTP!');

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Failed to send test email: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
