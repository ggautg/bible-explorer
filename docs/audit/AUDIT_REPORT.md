# Informe general de auditoría

**Proyecto:** Explorador Bíblico (`bible-explorer`)
**Fecha:** 2026-11-26
**Alcance:** backend Laravel 13, frontend Vue 3/Inertia 3, migraciones y seeders, tests, seguridad, rendimiento y calidad de código.
**Idioma:** español rioplatense.

## Resumen ejecutivo

El repositorio estaba funcional en producción pero con el arnés de pruebas roto, 62 errores de PHPStan, formato/lint fallidos y varios hallazgos de integridad de datos y rendimiento. Se auditó primero sin modificar código, se identificó la causa raíz de cada fallo y se aplicaron correcciones acotadas con regresiones nuevas.

Estado final de las verificaciones ejecutadas:

| Verificación | Antes | Después |
| --- | --- | --- |
| `php artisan test` | 31 errores (BD no aislada + ruta `dashboard` inexistente) | **46/46 pruebas, 170 aserciones** |
| `./vendor/bin/phpstan analyse --memory-limit=1G` | 62 errores | **0 errores** |
| `./vendor/bin/pint --test` | fallido | **passed** |
| `npm run lint:check` | fallido (`import/order`, `vue/block-lang`, variables sin usar) | **passed** |
| `npm run format:check` | fallido (8+ archivos) | **passed** |
| `npm run types:check` | passed | **passed** |
| `npm run build` | fallido (`vendor/autoload.php` ausente) | **passed** |
| `composer audit` | 80 avisos / 15 paquetes | **0 avisos** (2 paquetes abandonados informativos) |

## Causas raíz confirmadas

1. **Tests sin base de datos:** `RefreshDatabase` estaba comentado en `tests/Pest.php`; las pruebas usaban la BD local y fallaban con `no such table: users`.
2. **Rutas obsoletas en tests:** `config/fortify.php` define `home => /comunidad`, pero los tests asumían una ruta `dashboard` que no existe.
3. **Migración MySQL-only bajo SQLite:** `fullText('text')` no existe en SQLite, por lo que cualquier prueba con `RefreshDatabase` fallaba al migrar.
4. **Metadatos fiables de terceros:** favoritos y notas aceptaban `book_name`/`text` del cliente, permitiendo guardar datos inconsistentes con el versículo referenciado.
5. **Toggles con check-then-write:** like, seguimiento y reacciones consultaban y luego escribían; con el tiempo suficiente podían duplicar o perder interacciones.
6. **N+1 en feeds:** cada publicación/comentario con referencia bíblica ejecutaba una consulta separada para traer el texto.
7. **Anotaciones de tipos incompletas:** relaciones Eloquent y colecciones sin genéricos explicaban la mayor parte de los 62 errores de PHPStan.

## Correcciones aplicadas

Ver `CHANGELOG_AUDIT.md` para el listado completo. Resumen:

- Aislamiento y reparación del arnés de tests; 6 nuevos tests de regresión.
- Guard por driver en la migración FULLTEXT y `down()` incompleto en la migración de `score`/`range`.
- Toggles atómicos apoyados en las claves únicas existentes (`post_likes`, `follows`, `profile_comment_reactions`).
- Derivación en servidor de `book_name` y `text` para favoritos y notas; referencias inexistentes responden 422.
- Resolución por lote de textos bíblicos en feed, perfil y muro (`App\Services\VerseMetadata`).
- Límites de frecuencia en rutas de escritura social y de herramientas bíblicas.
- Seed idempotente.
- Generics correctos en modelos/servicios y correcciones de tipos en controladores, sin supresiones ni baselines.
- Configuración de ESLint/Prettier y corrección de HTML inválido en `settings/Profile.vue`.

## Segunda pasada: recomendaciones aplicadas

Tras el primer informe se ejecutaron las recomendaciones de bajo riesgo: actualización de dependencias dentro de las restricciones vigentes (`composer audit` sin avisos), migración de índices/unicidad con deduplicación previa, toggle de rebibleo atómico, QR 2FA como imagen, lista blanca en `flashToast`, `AbortController` en la búsqueda, import de D3 por submódulos, limpieza de timers y `aria-pressed` en los controles sociales. La suite sigue en 46/46 y todas las verificaciones continúan en verde.

## Estado de los hallazgos

| Estado | Detalle |
| --- | --- |
| Verificado | Suite de tests, PHPStan, Pint, ESLint, Prettier, `vue-tsc`, build y `composer audit` sin avisos. Ver `TEST_REPORT.md`. |
| Corregido | Arnés de tests, FULLTEXT en SQLite, `down()` de migración, toggles atómicos, metadatos derivados, N+1 de textos, throttles, seeder idempotente, types PHP, lint/format, dependencias, índices/unicidad en BD, QR 2FA, búsqueda con cancelación, timers, `aria-pressed` e import de D3. Ver `CHANGELOG_AUDIT.md`. |
| No corregido | Traducción no modelada en favoritos/notas/posts, migración legacy de un solo nivel, tabla auxiliar `legacy_comment_post_map`, `alpha` del grafo se agota a los 300 ticks, 2 paquetes abandonados informativos. |
| No verificable | `EXPLAIN` en MySQL, concurrencia real, cabeceras y configuración de producción, QR 2FA en navegador. |
| Pendiente | Validar sobre MySQL con datos reales, decidir la migración de traducción y eliminar la tabla auxiliar legacy. |

## Límites de la auditoría

- Se conectó a MySQL 8.0.35 recién al final: se aplicaron las migraciones pendientes y se creó un perfil de prueba, pero no se corrió `EXPLAIN` ni se verificó concurrencia real.
- No se ejecutaron pruebas de concurrencia real; la atomicidad se apoya en restricciones únicas existentes y se validó con pruebas secuenciales.
- Las dependencias se actualizaron dentro de las restricciones ya declaradas en `composer.json` (sin cambios de major); `composer audit` quedó sin avisos.
- No se desactivaron middleware, validaciones ni autenticación para hacer pasar pruebas.
- Las credenciales locales no se reproducen en estos documentos.

## Documentos

- `ARCHITECTURE.md`
- `SECURITY_AUDIT.md`
- `TEST_REPORT.md`
- `DATABASE_AUDIT.md`
- `PERFORMANCE_REPORT.md`
- `TECHNICAL_DEBT.md`
- `CHANGELOG_AUDIT.md`
- `RECOMMENDATIONS.md`
