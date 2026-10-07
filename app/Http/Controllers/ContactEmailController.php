<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormSubmitted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactEmailController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        Log::info('Contact Email Received', $validated);

        try {
            $adminEmail = config('mail.from.address', 'flavourflow006@gmail.com');
            Mail::to($adminEmail)->send(new ContactFormSubmitted(
                $validated['name'],
                $validated['email'],
                $validated['subject'],
                $validated['message']
            ));
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch contact form email: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you, '.$validated['name'].'! Your email message has been sent successfully.',
        ]);
    }
}
