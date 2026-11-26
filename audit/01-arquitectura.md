# 01 — Arquitectura

Descripción del sistema tal como surge del repositorio y del entorno local. Los aspectos no observables se marcan `NO VERIFICADO`.

## Stack verificado

| Capa | Tecnología | Evidencia |
|---|---|---|
| Lenguaje | PHP 8.3.36 | `php -v` local; `composer.json:12` (`"php": "^8.3"`) |
| Backend | Laravel 13.44.1 | `composer show laravel/framework` → `v13.44.1`; `composer.json:17` |
| Inertia | `inertiajs/inertia-laravel` 3.8.0 | `composer show inertiajs/inertia-laravel` → `v3.8.0`; `composer.json:14` |
| Autenticación | Fortify 1.44.0 + passkeys + 2FA | `composer show laravel/fortify` → `v1.44.0`; `config/fortify.php` |
| Frontend | Vue 3.5.40 (rango `^3.5.13`), Inertia Vue 3, Tailwind 4.1, Vite 8.1.5 | `package.json:37-53`, `npm ls ... --depth=0`, `vite.config.ts:1-33` |
| Base de datos | MySQL 8.0.35 local, `bible_explorer` | `mysql --version`; `.env` local (credencial nunca reproducida) |
| Node | 26.13.0 local / 22 en CI | `node -v`; `.github/workflows/tests.yml:32` |
| Cola y caché | `database` | `.env.example:38,40`; tablas `jobs`, `job_batches`, `failed_jobs` verificadas |
| Servidor | `php artisan serve` solo en script `dev` | `composer.json:55-58`; sin Dockerfile/nginx/Apache en el repo |

## Flujo de una solicitud

1. El navegador carga el bundle Vite (`vite.config.ts:11-19`) y monta la app Inertia (`resources/js/app.js:10-21`).
2. Las navegación de página va por Inertia (`Link`/`router`); las mutaciones ligeras de notas y favoritos van por axios a `/api/notes` y `/api/favorites` (`useNotes.js:25,60,84`, `useFavorites.js:26,62,76`).
3. Laravel aplica el grupo `web`: sesión, CSRF, cookies cifradas excepto `appearance` y `sidebar_state`, `HandleAppearance`, `HandleInertiaRequests` y preload de assets (`bootstrap/app.php:20-26`).
4. El grupo autenticado de `routes/web.php:23-71` contiene feed, publicaciones, perfil, biblia, búsquedas, notas y favoritos.
5. Los controladores validan, coordinan modelos/servicios y devuelven Inertia o JSON. El serializer de publicaciones usa `VerseMetadata` para derivar metadatos del versículo en servidor (`PostController.php:187-205`).
6. Las consultas bíblicas y del grafo llegan a MySQL con índices FULLTEXT y compuestos (`bible_verses`, `bible_cross_references`).
7. La carga del dataset bíblico depende de un tercero: `https://bible.helloao.org/api/...` (`SeedBibleText.php:42`, `SeedBibleCrossReferences.php:42`).

## Inventario de rutas y middleware

- `php artisan route:list --json` → **72 rutas** registradas.
- Aproximadamente **20 rutas sin middleware `auth`**: entrada Fortify (login, registro, recuperación, verificación, 2FA, passkeys) y rutas públicas de lectura bíblica. El número exacto depende de cómo se cuente el prefijo `settings`; se reporta como aproximación porque la lista se revisó en local, no en producción.
- `routes/web.php:23`: `Route::middleware('auth')->group(function () { ... });` agrupa las funciones del producto hasta la línea 71.
- `routes/settings.php:15`: `Route::middleware(['auth', 'verified'])->group(function () { ... });` para perfil, seguridad y borrado de cuenta.
- `routes/web.php:25`: el feed real es `PostController@index`; `CommunityFeedController` y `ProfileWallController` no están rutados (BE-006).
- Límites de frecuencia confirmados: login 5/min, 2FA 5/min, passkeys 10/min, contraseña 6/min, interacciones sociales 15/min, favoritos/notas 30/min (`config/fortify.php:117-120` y `routes/web.php:62-67`).
- **Sin throttle:** búsquedas `books-search`, `structure`, `resolve` y `/buscar` (`routes/web.php:54-58,70`; BE-002).

## Límites de confianza

| Frontera | Entrada | Control observado | Riesgo asociado |
|---|---|---|---|
| Visitante → Fortify | credenciales, correo, token 2FA/passkey | limiters, sesión, CSRF, verificación disponible | BE-003 (verificación no exigida en el área principal) |
| Usuario autenticado → social | texto, tipo de publicación, referencia bíblica | validación, `Rule::in`, `VerseMetadata`, índices únicos | BE-002, BE-005, DB-004 |
| Usuario autenticado → biblia | `q`, `ref`, `book_code`, `chapter`, `verse` | bindings SQL, parser de referencias, FULLTEXT | BE-002 (sin `max`/throttle) |
| Cliente → API JSON | notas y favoritos | autorización por `$request->user()`, CSRF de axios | FE-001, FE-002 |
| Tercero `bible.helloao.org` | texto bíblico y referencias cruzadas | HTTPS, `upsert`, validación posterior de integridad | CFG-002 (bypass TLS ligado a debug), DB-001, DB-002 |
| Servidor → logs/session storage | PII, SQL bindings, datos de sesión | canal `single`, sesión en MySQL | CFG-004, CFG-005 |
| Repositorio → operador | `.env.example`, README, historial | `.env` gitignored | CFG-001, OPS-003 |

## Modelo de datos simplificado

| Dominio | Tablas | Relaciones / unicidades verificadas |
|---|---|---|
| Identidad | `users`, `passkeys`, `password_reset_tokens`, `sessions`, `password_history` (si aplica) | passkeys y 2FA por usuario; sesiones en BD |
| Social | `posts`, `post_likes`, `follows` | `parent_id` (respuestas), `rebible_of_id` (rebibleos), `posts_user_rebible_unique`, `post_likes(user_id,post_id)`, `follows(follower_id,followed_id)` |
| Biblia | `bible_books`, `bible_verses`, `bible_cross_references` | `(translation, book_code, chapter, verse)` único; FULLTEXT en `text`; índices en ambas direcciones del grafo |
| Herramientas | `notes`, `favorites` | únicos por `(user_id, book_code, chapter, verse)` |
| Legacy | `profile_comments`, `profile_comment_reactions`, `legacy_comment_post_map` | 0 filas en local; consumidores no rutados (DB-003, BE-006) |
| Colas | `jobs`, `job_batches`, `failed_jobs` | coherente con `QUEUE_CONNECTION=database` |

## Entorno de ejecución y despliegue

- `php artisan about`: entorno `local`, `Debug Mode .. ENABLED`, rutas no cacheadas, vistas cacheadas, `public/storage .. NOT LINKED`.
- No hay `Dockerfile`, `docker-compose.yml`, configuración de Nginx/Apache, `Procfile` ni `.nvmrc` versionados (CFG-006, OPS-005).
- CI: `.github/workflows/tests.yml:3-38` dispara en `push` a `main` y `pull_request`; usa `contents: read`, actions fijadas por SHA, PHP 8.3 y Node 22; ejecuta `composer setup` y `composer ci:check`.
- `composer setup` (`composer.json:47-53`) copia `.env.example`, genera clave, migra con `--force`, instala npm y compila. Es apropiado para CI; peligroso si se reutiliza como despliegue (OPS-003).
- Tests: `phpunit.xml:20-31` fuerza SQLite `:memory:`, correo `array`, cola `sync` y sesión `array`.

## Observaciones arquitectónicas (no clasificadas como hallazgos nuevos)

- La separación controlador/servicio/modelo es coherente con `AGENTS.md`: controladores delgados, servicios para grafo y metadatos, serialización centralizada.
- El estado compartido del cliente (`useNotes`, `useFavorites`, `useAppearance`, `useExplorationTrail`) vive en singletons de módulo. Es simple y eficiente, pero acopla el ciclo de vida del caché al de la pestaña (FE-001).
- La importación bíblica es un comando Artisan, no un job en cola: es operable, pero la reanudabilidad es desigual (`SeedBibleText` usa `upsert`; `SeedBibleCrossReferences` usa `insert` plano, DB-001).
- La capa de presentación usa tokens `bible-*` y seis temas validados (`useAppearance.js:4-65`), consistente con la guía de proyecto.

## NO VERIFICADO en esta capa

- Topología real de producción, servidor web, document root y `php.ini`.
- Número exacto y lista definitiva de rutas públicas en un despliegue (solo se registró el inventario local de 72).
- Cabeceras HTTP efectivas, HTTPS terminado en proxy o balanceador, y políticas de red hacia MySQL.
- Visibilidad del repositorio remoto y acceso de terceros al historial.
