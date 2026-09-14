@include('emails.partials.header', ['title' => 'Welcome to FlavourFlow'])
    <h1 style="font-size:24px;line-height:1.3;margin:0 0 12px;">Welcome, {{ $user->name }}.</h1>
    <p style="font-size:15px;line-height:1.7;margin:0 0 18px;">
        Your FlavourFlow account has been created successfully. You can now track orders, manage your profile, and reorder your favorites faster.
    </p>
    <p style="font-size:15px;line-height:1.7;margin:0;">
        Account email: <strong>{{ $user->email }}</strong>
    </p>
@include('emails.partials.footer')
