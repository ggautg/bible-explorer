# Cambios de la auditoría

Todos los cambios están sin commitear en el árbol de trabajo. Las dependencias se actualizaron solo dentro de las restricciones ya declaradas en `composer.json`; no se desactivaron middleware, validaciones ni autenticación.

## Pruebas y arnés

| Archivo | Cambio |
| --- | --- |
| `tests/Pest.php` | `RefreshDatabase` habilitado para `Feature`. |
| `tests/Feature/DashboardTest.php`, `tests/Feature/Auth/*.php` | `route('dashboard')` → `route('community.feed')`. |
| `tests/Feature/BibleToolsIntegrityTest.php` | Nuevo: metadatos derivados y referencias inválidas. |
| `tests/Feature/SocialInteractionsTest.php` | Nuevo: toggles idempotentes y throttle. |
| `tests/Feature/DatabaseSeederTest.php` | Nuevo: seeder idempotente. |

## Comunidad (post-auditoría)

| Archivo | Cambio |
| --- | --- |
| `database/migrations/2026_11_26_100000_add_kind_to_posts_table.php` | Nueva: tipo de publicación `kind` (default `reflexion`). |
| `app/Models/Post.php` | Constantes `KINDS`/`SELECTABLE_KINDS` y campo `kind` fillable. |
| `app/Http/Controllers/PostController.php` | Valida `kind`, expone la etiqueta en el payload y deriva `book_name` desde `VerseMetadata` en `store()`/`reply()`. |
| `resources/js/components/Social/PostComposer.vue` | Selector de opciones de comunidad y selector de versículo cuando elegís “Versículo”. |
| `resources/js/components/Social/VerseReferencePicker.vue` | Nuevo: autocompletado de libros + selects de capítulo y versículo. |
| `resources/js/components/Social/PostCard.vue` | Etiqueta visible del tipo de publicación. |
| `app/Http/Controllers/Bible/SearchController.php`, `routes/web.php` | Endpoint JSON `GET /api/bible/structure` para capítulos y versículos. |

## Backend

| Archivo | Cambio |
| --- | --- |
| `database/migrations/2026_07_24_114928_add_fulltext_index_to_bible_verses_table.php` | Guard por driver MySQL en `up()` y `down()`. |
| `database/migrations/2026_07_22_131513_add_score_and_range_to_bible_cross_references_table.php` | `down()` elimina `to_verse_end` y `score`. |
| `app/Http/Controllers/PostInteractionController.php` | Like con `delete()` + `insertOrIgnore()` sobre clave única. |
| `app/Http/Controllers/FollowController.php` | Seguimiento con el mismo patrón atómico. |
| `app/Http/Controllers/ProfileWallController.php` | Reacciones con el mismo patrón; textos bíblicos resueltos en lote. |
| `app/Http/Controllers/Bible/FavoriteController.php` | Valida solo la referencia; deriva `book_name`/`text` en servidor. |
| `app/Http/Controllers/Bible/NoteController.php` | Valida solo la referencia y el cuerpo; deriva `book_name`. |
| `app/Http/Controllers/PostController.php` | `viewerStateFor()` precarga textos; `serializePostCore()` los consume. |
| `app/Http/Controllers/CommunityFeedController.php` | Resolución de textos por lote. |
| `app/Http/Controllers/HomeController.php`, `PostController.php`, `CommunityFeedController.php` | Ajustes de tipos para PHPStan. |
| `app/Services/VerseMetadata.php` | Nuevo: resolución de metadatos y textos por lote. |
| `app/Console/Commands/*.php`, `app/Services/BibleGraphService.php`, `app/Models/*.php` | Generics y `Arr::wrap()` para PHPStan (sin supresiones). |
| `database/seeders/DatabaseSeeder.php` | `firstOrCreate()` en lugar de `create()`. |
| `routes/web.php` | `throttle:15,1` en escrituras sociales; `throttle:30,1` en APIs de favoritos/notas. |

## Frontend y herramientas

| Archivo | Cambio |
| --- | --- |
| `.prettierignore` | Excluye archivos generados por Wayfinder. |
| `eslint.config.js` | `vue/block-lang` permite `js`/`ts` y bloques sin `lang`, coherente con el proyecto mixto. |
| `resources/js/pages/settings/Profile.vue` | `<p>` que envolvía `<Form>` reemplazado por `<div>` + `<span>` (HTML inválido detectado por Prettier). |
| `resources/js/layouts/SettingsLayout.vue`, `Bible/Shared.vue`, `Posts/Show.vue`, `Welcome.vue`, `components/Profile/ProfileGoalsSection.vue` | Variables `props` sin usar eliminadas. |
| Múltiples archivos bajo `resources/js/` | Formato aplicado con Prettier y fixes de ESLint. |
| Múltiples migraciones/modelos | Formato aplicado con Pint. |

## Segunda pasada: recomendaciones aplicadas

| Archivo | Cambio |
| --- | --- |
| `composer.lock` | Actualización de dependencias dentro de las restricciones vigentes (Laravel 13.20 → 13.44, Fortify 1.37 → 1.44, passkeys/web-auth, Guzzle, CommonMark, Flysystem, Pest, Pint, Wayfinder, etc.). `composer audit` quedó sin avisos. |
| `database/migrations/2026_11_26_000001_add_integrity_indexes_to_bible_explorer.php` | Nueva: índice entrante en `bible_cross_references`, deduplicación y unicidad de referencias y rebibleos; `down()` revierte todo. Ajustada para MySQL (error 1093) y para reanudarse si un intento anterior dejó el índice creado. |
| `app/Http/Controllers/PostInteractionController.php` | `toggleRebible()` usa `insertOrIgnore()` apoyado en la nueva clave única. |
| `resources/js/components/TwoFactorSetupModal.vue` | QR renderizado como `<img>` con `data:image/svg+xml` en lugar de `v-html`. |
| `resources/js/lib/flashToast.js` | Lista blanca de tipos de toast con fallback a `info`. |
| `resources/js/composables/useVerseSearch.js` | `AbortController` + `requestId` para descartar respuestas obsoletas; cancelación al desmontar. |
| `resources/js/composables/useConstellationGraph.js` | D3 importado por submódulos; nodos accesibles por teclado con `tabindex`, `role` y `aria-label`. |
| `resources/js/components/Social/PostCard.vue`, `pages/Bible/Chapter.vue`, `pages/Bible/Graph.vue` | Timers de copia limpiados en `onUnmounted`; `aria-pressed` en like y rebibleo. |
| `resources/js/pages/Community/Feed.vue`, `pages/Profile/Show.vue` | `aria-pressed` en botones de seguir. |
| `app/Models/User.php` | `avatarFor()` también consulta el slug para los testimonios conocidos. |

## No aplicado a propósito

- Traducción explícita en favoritos/notas/posts y eliminación de `legacy_comment_post_map`: requieren decisión de producto y migración de datos.
- Soporte táctil del grafo y ajuste de los 300 ticks: cambio de UX no cubierto por pruebas.
- Eliminación de `dragon-code/contracts` y `dragon-code/support`: son dependencias transitivas y `composer audit` solo las marca como abandonadas.
- Credenciales de la base local: no se versionaron ni se incluyen en documentos.
