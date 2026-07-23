<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee('Royal Garam Masala');
});
