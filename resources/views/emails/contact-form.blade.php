<x-mail::message>
# New Contact Inquiry Received

You have received a new message from the **FlavourFlow** website contact form.

**From:** {{ $name }} ({{ $email }})  
**Subject:** {{ $subject }}

<x-mail::panel>
{{ $userMessage }}
</x-mail::panel>

Thanks,  
{{ config('app.name') }}
</x-mail::message>
