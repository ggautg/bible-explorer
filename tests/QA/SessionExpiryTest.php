<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

test('an inertia request with an expired csrf token is sent to login', function () {
    $request = Request::create('/posts', 'POST');
    $request->headers->set('X-Inertia', 'true');

    $response = app(ExceptionHandler::class)->render(
        $request,
        new TokenMismatchException('CSRF token mismatch.')
    );

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toContain('/login')
        ->and($response->headers->get('Location'))->not->toContain('/posts');
});

test('a non inertia request keeps the framework 419 response', function () {
    $request = Request::create('/posts', 'POST');

    $response = app(ExceptionHandler::class)->render(
        $request,
        new TokenMismatchException('CSRF token mismatch.')
    );

    expect($response->getStatusCode())->toBe(419);
});

test('a json request with an expired csrf token keeps the framework 419 response', function () {
    $request = Request::create('/api/notes', 'POST');
    $request->headers->set('Accept', 'application/json');

    $response = app(ExceptionHandler::class)->render(
        $request,
        new TokenMismatchException('CSRF token mismatch.')
    );

    expect($response->getStatusCode())->toBe(419)
        ->and($response->headers->get('Content-Type'))->toContain('application/json');
});

test('expired sessions still produce json 401 on the bible apis', function () {
    $this->getJson('/api/notes')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');

    $this->putJson('/api/notes', [
        'book_code' => 'GEN',
        'chapter' => 1,
        'verse' => 1,
        'body' => 'Nota sin sesión',
    ])->assertUnauthorized();
});

test('the frontend sends axios 401 and 419 responses to login', function () {
    $source = (string) file_get_contents(base_path('resources/js/app.js'));

    expect($source)->toContain('axios.interceptors.response.use')
        ->toContain('401, 419')
        ->toContain("window.location.assign('/login')");
});
