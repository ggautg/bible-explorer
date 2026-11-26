<?php

use Tests\TestCase;

uses(TestCase::class);

test('the QA environment uses SQLite in memory', function () {
    expect(config('database.default'))->toBe('sqlite')
        ->and(config('database.connections.sqlite.database'))->toBe(':memory:')
        ->and(config('app.env'))->toBe('testing');
});

test('auxiliary services are isolated in memory', function () {
    expect(config('session.driver'))->toBe('array')
        ->and(config('mail.default'))->toBe('array')
        ->and(config('queue.default'))->toBe('sync')
        ->and(config('cache.default'))->toBe('array');
});
