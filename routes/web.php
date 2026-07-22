<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login', ['message' => session('message')]);
})->name('login');

Route::post('/login', function (Request $request) {
    $email = $request->input('email');
    $password = $request->input('password');

    if ($email === 'admin@example.com' && $password === 'password') {
        return redirect()->route('login')->with('message', 'Login successful!');
    }

    return back()->withErrors([
        'credentials' => 'Please use admin@example.com and password.',
    ])->withInput();
})->name('login.submit');
