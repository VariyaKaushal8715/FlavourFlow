<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class AccountCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function __construct(public User $user)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to FlavourFlow'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-created'
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Account created email failed', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
