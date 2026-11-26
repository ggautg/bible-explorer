# Deuda técnica

Priorizada por impacto y riesgo. La deuda corregida en esta auditoría se documenta en `CHANGELOG_AUDIT.md`.

## Alta prioridad

| Ítem | Ubicación | Por qué importa |
| --- | --- | --- |
| Correr `EXPLAIN` con los índices nuevos | `bible_cross_references`, `posts` | La migración ya se aplicó en MySQL sin borrar filas; falta medir el plan de consultas. |
| Traducción no modelada en favoritos/notas/posts | Migraciones bíblicas y sociales | Los metadatos derivados se resuelven siempre en `spa_rvg`. |

## Media prioridad

| Ítem | Ubicación | Por qué importa |
| --- | --- | --- |
| Serializadores duplicados | `PostController`, `ProfileWallController`, `CommunityFeedController` | Tres caminos para producir datos de publicaciones; diverge fácilmente. Extraer un presentador compartido. |
| `ProfileController` inyecta `PostController` | `ProfileController.php:15` | Acoplamiento entre controlador y presentador. |
| `legacy_comment_post_map` queda en el esquema | `2026_11_19_130759_create_posts_table.php` | Tabla auxiliar de migración nunca eliminada. |
| Migración legacy de un solo nivel | `2026_11_19_130759_create_posts_table.php` | Solo migra un nivel de `parent_id`; respuestas profundas se pierden. |

## Baja prioridad

| Ítem | Ubicación | Por qué importa |
| --- | --- | --- |
| Grafo sin soporte táctil y con 300 ticks | `Graph.vue`, `useConstellationGraph.js` | Ya navega por teclado, pero no tiene gestos táctiles y `alpha` se agota. |
| Timers globales de `useToast.js` | `useToast.js` | Viven toda la sesión por diseño; no se cancelan al ocultar la app. |
| Páginas `.js` sin type-check estricto | `resources/js/**/*.js` | `vue-tsc` pasa, pero los `.js` no aportan seguridad de tipos. |
| Helpers de tipos vacíos | `tests/Pest.php` (`expect()->toBeOne`, `something()`) | Código de plantilla sin uso. |
| Función genérica `verseOfTheDay` con offset | `HomeController` | `skip($offset)` sobre tabla grande; aceptable hoy, medir si crece. |
| Paquetes abandonados | `composer.lock` | `dragon-code/contracts` y `dragon-code/support` siguen instalados como dependencias transitivas. |

## Deuda ya saldada

- Arnés de tests sin base aislada y rutas obsoletas.
- FULLTEXT incompatible con SQLite.
- 62 errores de PHPStan (generics y tipos de relaciones).
- Lint/format fallidos y HTML inválido en ajustes de perfil.
- Toggles con carrera y metadatos fiados al cliente.
- N+1 de textos bíblicos.
- Seed no idempotente y `down()` incompleto.
- Dependencias con 80 avisos de seguridad: actualizadas, `composer audit` sin avisos.
- Rebibleos sin unicidad y referencias cruzadas sin índice entrante/unicidad.
- `v-html` del QR 2FA reemplazado por `<img>`.
- `flashToast` con lista blanca de tipos.
- Búsqueda con `AbortController`/request id.
- Timers de componentes limpiados en `onUnmounted`.
- Import completo de D3 reducido a submódulos.
- `aria-pressed` en toggles de like, rebibleo y seguir.
