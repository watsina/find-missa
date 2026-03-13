<?php

test('asset URLs use HTTPS when request comes through a reverse proxy', function () {
    $response = $this->get('/', [
        'X-Forwarded-Proto' => 'https',
        'X-Forwarded-Host' => 'findmissa.org',
    ]);

    $response->assertSuccessful();

    $content = $response->getContent();

    // Vite assets should use HTTPS, not HTTP
    expect($content)->not->toContain('http://findmissa.org');
});
