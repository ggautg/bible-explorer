<?php

use Tests\TestCase;

uses(TestCase::class);

test('the custom toast container is mounted in the inertia root', function () {
    $app = (string) file_get_contents(base_path('resources/js/app.js'));

    expect($app)->toContain("import ToastContainer from '@/components/Bible/ToastContainer.vue';")
        ->and($app)->toMatch('/h\(App, props\),\s*h\(ToastContainer\),\s*h\(Sonner/s');
});

test('the toast container announces messages to assistive technology', function () {
    $container = (string) file_get_contents(base_path('resources/js/components/Bible/ToastContainer.vue'));

    expect($container)->toContain('aria-live="polite"')
        ->and($container)->toContain('v-for="t in toasts"');
});

test('a transport failure does not redirect the user to login', function () {
    $app = (string) file_get_contents(base_path('resources/js/app.js'));

    expect($app)->toMatch('/\[401, 419\]\.includes\(error\?\.response\?\.status\)/');
});

test('the mobile shell exposes a toggle that reveals the sidebar', function () {
    $shell = (string) file_get_contents(base_path('resources/js/components/Bible/BiblePageShell.vue'));

    expect($shell)->toContain('aria-controls="mobile-nav"')
        ->and($shell)->toContain(':aria-expanded="mobileOpen ? \'true\' : \'false\'"')
        ->and($shell)->toContain("mobileOpen ? 'block' : 'hidden'");
});
