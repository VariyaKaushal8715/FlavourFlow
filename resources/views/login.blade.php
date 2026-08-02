<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 360px;
        }

        h2 {
            margin-top: 0;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 0.8rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .message {
            margin-bottom: 1rem;
            padding: 0.7rem;
            border-radius: 6px;
            background: #dcfce7;
            color: #166534;
        }

        .errors {
            margin-bottom: 1rem;
            padding: 0.7rem;
            border-radius: 6px;
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Login</h2>

        @if (session('message'))
            <div class="message">{{ session('message') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Log In</button>
        </form>
        <p style="margin: 1rem 0 0; text-align: center;"><a href="{{ route('home') }}">Back to shop</a></p>
    </div>
</body>
</html>
