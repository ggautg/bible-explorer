# Arquitectura

## Stack

- **Backend:** Laravel 13, PHP 8.3, MySQL local (SQLite en memoria para tests), Inertia 3.
- **Frontend:** Vue 3 (JavaScript en páginas/componentes nuevos), Tailwind CSS 4, Wayfinder para helpers de rutas.
- **Autenticación:** Laravel Fortify (login, registro, verificación, reset, 2FA, passkeys).

## Mapa de responsabilidades

| Capa | Ubicación | Rol |
| --- | --- | --- |
| Rutas web/social/biblia | `routes/web.php` | `/comunidad`, perfiles, posts, interacciones, lectura, búsqueda, API de favoritos/notas. |
| Rutas de configuración | `routes/settings.php` | Perfil, seguridad, apariencia (Fortify + controladores propios). |
| Controladores | `app/Http/Controllers/` | Delgados: validan, coordinan modelos/servicios y responden Inertia/redirect/JSON. |
| Servicios | `app/Services/` | `BibleGraphService` (grafo) y `VerseMetadata` (resolución de textos/nombres). |
| Modelos | `app/Models/` | `User`, `Post`, `Favorite`, `Note`, `BibleVerse`, `BibleBook`, `ProfileComment` y reacciones. |
| Migraciones | `database/migrations/` | Esquema bíblico base, social y migraciones de datos legacy. |
| Vistas | `resources/js/pages/` | Páginas Inertia; `BiblePageShell` envuelve las páginas autenticadas del producto. |
| Componentes | `resources/js/components/` | UI reutilizable (social, biblia, perfil, formularios). |
| Composables | `resources/js/composables/` | Estado reactivo: favoritos, notas, búsqueda, grafo, toasts, tema. |

## Flujos principales

### Feed social

`PostController::index` / `CommunityFeedController::index` → consulta con relaciones eager load → `viewerStateFor()` precarga likes/rebibleos y textos bíblicos → `serializePost()` / `map()` → Inertia `Community/Feed`.

El feed de comunidad combina tres fuentes:

- `PostController` para publicaciones estilo red social.
- `CommunityFeedController` para comentarios de muros.
- `ProfileController` y `ProfileWallController` para vistas de perfil.

### Interacciones

`PostInteractionController` (like/rebibleo), `FollowController` (seguir) y `ProfileWallController::react` usan escrituras idempotentes apoyadas en claves únicas. Las rutas de escritura tienen `throttle`.

### Herramientas bíblicas

Lectura (`ReaderController`), búsqueda (`TextSearchController`, `SearchController`), grafo (`BibleGraphService`), favoritos y notas. Favoritos y notas derivan `book_name`/`text` en el servidor desde `bible_verses` (`spa_rvg`) y `bible_books`.

## Temas y UI

Seis temas bíblicos (pergamino, olivo, jordán, getsemaní, sinaí, patmos) con persistencia en `useAppearance`. El default es getsemaní. Se usan tokens `bible-*` y variables `background`/`foreground`; los colores hardcodeados se limitan a mapeos de testimonios ya existentes.

## Observaciones arquitectónicas

- Hay dos serializadores de publicaciones paralelos (`PostController::serializePost` y `ProfileWallController::serializeComment`) con lógica similar; unificable en un presentador compartido.
- `ProfileController` inyecta `PostController` como presentador, acoplamiento que se puede extraer a un servicio propio.
- Los archivos bajo `resources/js/actions` y `resources/js/routes` son generados por Wayfinder; se excluyeron de Prettier en `.prettierignore` y se restauraron a su estado versionado después del build para evitar ruido.
- Los textos visibles están en español rioplatense; se mantienen términos de producto ya establecidos cuando aportan claridad.
