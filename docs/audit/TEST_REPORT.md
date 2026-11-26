# Informe de pruebas

## Entorno

- PHP 8.3 + Laravel 13 + Pest (PHPUnit).
- Node 26 + npm 11.
- Base de pruebas: SQLite en memoria (`phpunit.xml`: `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`).
- Dependencias instaladas con `composer install --no-interaction --prefer-dist` y `npm ci --no-audit --no-fund`.

## Línea base (antes de tocar código)

| Comando | Resultado |
| --- | --- |
| `php artisan test` | 31 errores: `no such table: users` y `Route [dashboard] not defined`. |
| `./vendor/bin/pint --test` | Fallido. |
| `./vendor/bin/phpstan analyse --memory-limit=1G` | 62 errores. |
| `npm run lint:check` | Fallido (`import/order`, `vue/block-lang`, variables sin usar). |
| `npm run format:check` | Fallido. |
| `npm run types:check` | Passed. |
| `npm run build` | Fallido: no existía `vendor/autoload.php`. |
| `composer audit` | 80 avisos / 15 paquetes. |

Evidencia guardada en `audit-*-before.txt`, `audit-*.json` en la raíz del repositorio.

## Diagnóstico y reparación del arnés

1. `RefreshDatabase` estaba comentado en `tests/Pest.php`. Al habilitarlo aparecieron 38 errores por `This database driver does not support fulltext index creation`.
2. La migración `2026_07_24_114928_add_fulltext_index_to_bible_verses_table.php` usaba `fullText()`, exclusivo de MySQL. Se agregó un guard `DB::getDriverName() !== 'mysql'` en `up()` y `down()`.
3. Las pruebas apuntaban a `route('dashboard')`, pero Fortify define `home => /comunidad`. Se reemplazó por `route('community.feed')` en los tests de autenticación y dashboard.
4. Resultado intermedio: 39/39 pruebas, 136 aserciones.

## Suite final

```
php artisan test --compact --no-progress --colors=never
  PASS  46 tests  170 assertions
```

Nuevos tests de regresión:

| Archivo | Cubre |
| --- | --- |
| `tests/Feature/BibleToolsIntegrityTest.php` | Favoritos/notas derivan metadatos del servidor; referencias inexistentes → 422. |
| `tests/Feature/SocialInteractionsTest.php` | Like y seguimiento alternan sin duplicar; límite de frecuencia → 429. |
| `tests/Feature/DatabaseSeederTest.php` | Sembrar dos veces no duplica el usuario de prueba. |

## Otras verificaciones finales

| Comando | Resultado | Evidencia |
| --- | --- | --- |
| `./vendor/bin/pint --test` | passed | `audit-pint-final.txt` |
| `./vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | 0 errores | `audit-phpstan-final.txt` |
| `npm run lint:check` | passed | `audit-eslint-final.txt` |
| `npm run format:check` | passed | `audit-format-final.txt` |
| `npm run types:check` | passed | `audit-types-final.txt` |
| `npm run build` | `✓ built in 2.33s` | `audit-build-final.txt` |

Durante `npm run format` Prettier detectó HTML inválido en `resources/js/pages/settings/Profile.vue`: un `<p>` envolvía un `<Form>` (bloque). Se corrigió la estructura a `<div>` + `<span>`.

## Verificación tras aplicar las recomendaciones

| Comando | Resultado | Evidencia |
| --- | --- | --- |
| `composer update --no-interaction --prefer-dist` | Actualizaciones dentro de `composer.json`; sin advisories | `audit-composer-update.txt` |
| `composer audit --no-interaction` | **0 avisos**; 2 paquetes abandonados informativos | salida de consola |
| `php artisan test --compact --no-progress --colors=never` | **46/46, 170 aserciones** | `audit-recommendations-test-final.txt` |
| `./vendor/bin/pint --test` | passed | salida de consola |
| `./vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | 0 errores | salida de consola |
| `npm run lint:check` / `format:check` / `types:check` | passed | salida de consola |
| `npm run build` | `✓ built in 3.12s` | `audit-recommendations-build.txt` |

La migración nueva se ejecutó correctamente en SQLite durante cada prueba con `RefreshDatabase` y, posteriormente, en MySQL 8.0.35 sobre la base `bible_explorer` (344.799 referencias, 0 duplicados; 0 rebibleos duplicados). No se corrió `EXPLAIN`.

## No ejecutado / no verificable

- Pruebas E2E en navegador: el repositorio no incluye suite E2E configurada.
- Pruebas de concurrencia real: no hay entorno con MySQL paralelo; la integridad se apoya en claves únicas.
- `EXPLAIN` sobre consultas reales: requiere la base de datos MySQL con datos representativos.
- Los archivos generados por Wayfinder (`resources/js/actions`, `resources/js/routes`) se restauraron a su estado versionado tras el build; el build los regenera y sus cambios son solo comentarios de número de línea y normalización de saltos de línea.
