<?php

test('visitors can send an email message via contact email endpoint', function () {
    $response = $this->postJson(route('contact.email'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'subject' => 'Inquiry regarding Royal Garam Masala',
        'message' => 'Hello team, do you ship to Mumbai?',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Thank you, John Doe! Your email message has been sent successfully to support@flavourflow.com.');
});

test('contact email endpoint validates required input fields', function () {
    $response = $this->post(route('contact.email'), []);

    $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
});
