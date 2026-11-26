<?php

use Symfony\Component\Finder\Finder;
use Tests\TestCase;

uses(TestCase::class);

/**
 * Lee un archivo fuente del frontend para comprobar los cambios estáticos
 * acordados en la ronda de corrección 2.
 *
 * @param  string  $path  Ruta relativa a la raíz del proyecto.
 * @return string Contenido del archivo.
 */
function qaSource(string $path): string
{
    return file_get_contents(base_path($path));
}

test('accessible state is exposed by toggles, comboboxes and tabs', function () {
    expect(qaSource('resources/js/components/PasswordInput.vue'))
        ->toContain(':aria-pressed="showPassword"')
        ->not->toContain(':tabindex="-1"')
        ->and(qaSource('resources/js/components/InputError.vue'))
        ->toContain('role="alert"')
        ->toContain('aria-live="polite"')
        ->and(qaSource('resources/js/components/AppearanceTabs.vue'))
        ->toContain(':aria-pressed="theme === item.id"')
        ->and(qaSource('resources/js/components/Social/VerseReferencePicker.vue'))
        ->toContain('function onBookKeydown(event)')
        ->toContain('@keydown="onBookKeydown"')
        ->toContain(':aria-activedescendant=')
        ->and(qaSource('resources/js/composables/useConstellationGraph.js'))
        ->toContain(".on('focus', function ()")
        ->and(qaSource('resources/js/pages/Bible/Graph.vue'))
        ->toContain('function onSearchKeydown(event)')
        ->toContain('@keydown="onSearchKeydown"')
        ->toContain(':aria-activedescendant=')
        ->toContain('role="option"')
        ->toContain('role="alert"')
        ->toContain('role="group"')
        ->not->toContain('role="img"')
        ->not->toContain('text-bible-muted-dim')
        ->and(qaSource('resources/js/components/Bible/NavHeader.vue'))
        ->not->toContain('text-bible-muted/80')
        ->and(qaSource('resources/js/pages/Bible/Chapter.vue'))
        ->toContain(':aria-expanded="showComparison"')
        ->toContain(':aria-pressed="Boolean(noteFor(versePayload(v)))"')
        ->toContain(':aria-pressed="Boolean(isFavorite(versePayload(v)))"')
        ->toContain('`Grafo de ${book.name} ${chapter}:${v.verse}`')
        ->toContain('`Compartir ${book.name} ${chapter}:${v.verse}`')
        ->and(qaSource('resources/js/pages/Community/Feed.vue'))
        ->toContain(":aria-current=\"tab === t.key ? 'page' : undefined\"")
        ->and(qaSource('resources/js/pages/Profile/Show.vue'))
        ->toContain(":aria-current=\"tab === t.key ? 'page' : undefined\"")
        ->and(qaSource('resources/js/layouts/SettingsLayout.vue'))
        ->toContain(":aria-current=\"isActive(item.href) ? 'page' : undefined\"")
        ->and(qaSource('resources/js/layouts/AuthLayout.vue'))
        ->toContain('<main');
});

test('auth forms rely on DOM order instead of positive tabindex values', function () {
    expect(qaSource('resources/js/pages/auth/Login.vue'))
        ->not->toContain(':tabindex="')
        ->and(qaSource('resources/js/pages/auth/Register.vue'))
        ->not->toContain(':tabindex="');
});

test('the low contrast muted text utility is not used', function () {
    $hits = [];
    $files = Finder::create()
        ->in(base_path('resources/js'))
        ->name('*.vue');

    foreach ($files as $file) {
        if (str_contains($file->getContents(), 'text-bible-muted-dim')) {
            $hits[] = $file->getRelativePathname();
        }
    }

    expect($hits)->toBeEmpty();
});

test('the shared document exposes a meta description', function () {
    expect(qaSource('resources/views/app.blade.php'))
        ->toContain('<meta name="description"');
});

test('sensitive two factor content is removed from the accessibility tree when hidden', function () {
    expect(qaSource('resources/js/components/TwoFactorRecoveryCodes.vue'))
        ->toContain(':aria-expanded="isRecoveryCodesVisible"')
        ->toContain('v-if="isRecoveryCodesVisible"')
        ->not->toContain("'h-0 opacity-0'")
        ->and(qaSource('resources/js/components/TwoFactorSetupModal.vue'))
        ->toContain(':aria-label=')
        ->and(qaSource('resources/js/pages/auth/Login.vue'))
        ->toContain('role="status"')
        ->and(qaSource('resources/js/pages/settings/Security.vue'))
        ->toContain('role="status"');
});

test('placeholder controls are disabled and repeated list actions have unique names', function () {
    expect(qaSource('resources/js/pages/Welcome.vue'))
        ->toContain('disabled')
        ->and(qaSource('resources/js/pages/Bible/Book.vue'))
        ->toContain(':aria-label="`Capítulo ${c} de ${book.name}`"')
        ->and(qaSource('resources/js/pages/Bible/Search.vue'))
        ->toContain(':aria-label="`Ver conexiones de ${r.bookName} ${r.chapter}:${r.verse}`"')
        ->and(qaSource('resources/js/pages/Bible/Favorites.vue'))
        ->toContain(':aria-label="`Quitar ${f.book_name} ${f.chapter}:${f.verse} de favoritos`"')
        ->and(qaSource('resources/js/pages/Bible/Notes.vue'))
        ->toContain(':aria-label="`Eliminar nota de ${n.book_name} ${n.chapter}:${n.verse}`"')
        ->and(qaSource('resources/js/components/PasskeyItem.vue'))
        ->toContain(':aria-label="`Eliminar llave de acceso ${passkey.name}`"');

    $placeholders = [];
    $files = Finder::create()
        ->in(base_path('resources/js'))
        ->name('*.vue');

    foreach ($files as $file) {
        if (str_contains($file->getContents(), ': \'#\'')) {
            $placeholders[] = $file->getRelativePathname();
        }
    }

    expect($placeholders)->toBeEmpty();
});

test('the build does not regenerate versioned Wayfinder artifacts', function () {
    $config = qaSource('vite.config.ts');

    expect($config)
        ->toContain('const isBuild = process.argv.includes(\'build\')')
        ->toContain('...(isBuild ? [] : [wayfinder({ formVariants: true })])')
        ->not->toContain('MissingAppKeyException');
});
