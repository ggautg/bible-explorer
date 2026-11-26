# 09 — Buenas prácticas encontradas

Controles que ya funcionan y que conviene preservar. Cada ítem cita evidencia verificable; no se incluyen suposiciones sobre producción.

## Configuración y autenticación

- `.env` está excluido por `.gitignore:19`; `.env.example` no contiene secretos reales. La credencial versionada está en `README.md:49` y en el historial (CFG-001), no en la plantilla de entorno.
- `config/session.php:185,202` usa `http_only=true` y `same_site=lax` por defecto.
- Las rutas de archivos del framework validan firma en GET/PUT y aplican CSP `default-src 'none'` a archivos servidos (`vendor/.../ServeFile.php:34-37`, `ReceiveFile.php:47`).
- Fortify declara limiters `login`, `two-factor` y `passkeys` en `config/fortify.php:117-120`, además de soporte para 2FA y llaves de acceso (`config/fortify.php`).
- Existen límites de frecuencia para publicaciones, seguir, "me gusta", rebibleo, favoritos y notas (`routes/web.php:62-67` y controladores).
- `Settings\ProfileController::update()` usa `$request->validated()` e invalida `email_verified_at` al cambiar el correo (`Settings/ProfileController.php:46-50`).
- `Settings\SecurityController::edit()` selecciona `credential` pero no lo serializa (`SecurityController.php:39-50`).
- `AppServiceProvider.php:48-52` usa fechas inmutables y prohíbe comandos destructivos de base de datos cuando `app()->isProduction()`.

## Backend

- No hay `DB::raw` con entrada del usuario sin binding: `TextSearchController.php:44-45` usa marcadores `?`.
- No se encontraron `eval`, `exec`, `shell_exec`, `unserialize` ni `system` en `app/` (`grep` → 0 coincidencias).
- Autorización por usuario: notas y favoritos se consultan siempre desde `$request->user()->notes()/favorites()` (`NoteController.php:27,53,82`; `FavoriteController.php:27,54,80`), sin ID de usuario manipulable.
- Idempotencia de "me gusta", seguir y rebibleo apoyada en índices únicos reales: `post_likes(user_id,post_id)` (`create_post_likes_table.php:21`), `follows(follower_id,followed_id)` (`create_follows_table.php:20`) y `posts_user_rebible_unique` (`2026_11_26_000001:88-92`).
- Metadatos de versículo derivados en servidor con `VerseMetadata::forReference()` (`PostController.php:187-205`, `NoteController.php:48-51`), evitando confiar en `book_name` del cliente.
- Serializadores omiten `password`, `two_factor_secret`, `two_factor_recovery_codes` y `remember_token` (`User.php:40`) y no exponen correos en publicaciones ni listados de personas (`PostController.php:364-394`).
- Los controladores son delgados y delegan en servicios para grafo y metadatos, coherente con `AGENTS.md` (`BibleGraphService.php:16-145`, `VerseMetadata.php`).
- El feed real usa `PostController@index` (`routes/web.php:25`) y serializa de forma centralizada, en vez de dispersar la lógica en la vista.

## Base de datos

- Todas las tablas usan InnoDB y `utf8mb4_unicode_ci` (`information_schema.tables`).
- `sql_mode` local incluye `ONLY_FULL_GROUP_BY, STRICT_TRANS_TABLES, NO_ZERO_IN_DATE, NO_ZERO_DATE, ERROR_FOR_DIVISION_BY_ZERO, NO_ENGINE_SUBSTITUTION`.
- Unicidad real verificada con `SHOW INDEX`:
  - `bible_verses_translation_book_code_chapter_verse_unique`
  - `bible_cross_refs_unique_reference`
  - `posts_user_rebible_unique`
  - `favorites_user_id_book_code_chapter_verse_unique`
  - `notes_user_id_book_code_chapter_verse_unique`
  - `post_likes(user_id,post_id)`
  - `follows(follower_id,followed_id)`
- Índices para el grafo en ambas direcciones: `bible_cross_references_from_book_from_chapter_from_verse_index` y `bible_cross_refs_to_reference_index`.
- Índice FULLTEXT en `bible_verses.text` y consultas con binding (`add_fulltext_index...php:16-22`, `TextSearchController.php:44-45`).
- Claves foráneas con `cascadeOnDelete()` en datos de usuario: publicaciones, "me gusta", seguimientos, favoritos, notas, comentarios y sesiones (`create_posts_table.php:18-20`, `create_post_likes_table.php:17-18`, `create_follows_table.php:16-17`, etc.).
- `jobs`, `job_batches` y `failed_jobs` existen (`create_jobs_table.php:14-47`), coherente con `QUEUE_CONNECTION=database`.
- El sembrador de texto bíblico usa `upsert()` por lotes de 500 con clave `(translation, book_code, chapter, verse)` (`SeedBibleText.php:91-97`), por lo que es reanudable.
- Integridad de datos local verificada: 66 libros, 31.102 versículos en `spa_rvg`, 344.799 referencias cruzadas, 0 huérfanos de libro/versículo y 0 referencias de publicaciones sin versículo.
- Contenido real comprobado: Juan (`JHN`) tiene 21 capítulos; Juan 3 tiene 36 versículos e incluye el 16.
- `php artisan migrate:status` confirma 21 migraciones aplicadas, incluidas las de índices de integridad y `kind`.

## Frontend

- No hay `v-html`, `innerHTML`, `outerHTML`, `document.write`, `new Function`, `eval` ni `target="_blank"` en `resources/js` (`grep` → 0 coincidencias). Vue escapa por defecto las interpolaciones `{{ }}`.
- Las URLs de compartir se construyen desde `window.location.origin` más códigos validados en servidor: `PostCard.vue:64`, `Bible/Chapter.vue:66`, `Bible/Graph.vue:69`.
- Las peticiones `fetch` usan `encodeURIComponent`/`URLSearchParams` y `AbortController` con debounce: `useVerseSearch.js:42-45,86-88`, `VerseReferencePicker.vue:86-89,151-160`.
- `useAppearance.js:45-65` valida el tema con un conjunto de ids permitidos antes de escribirlo en `data-theme`; cualquier valor manipulado cae a `getsemani`.
- Las cookies de tema usan `SameSite=Lax` y están excluidas explícitamente del cifrado de Inertia (`useAppearance.js:80`, `bootstrap/app.php:20`), coherente con su naturaleza no sensible.
- Axios agrega automáticamente `X-XSRF-TOKEN` desde la cookie `XSRF-TOKEN` que emite Laravel; las mutaciones van por POST/PUT/DELETE con middleware `web`.
- El cliente envía solo `book_code`/`chapter`/`verse`; `book_name` y `text` se derivan en servidor (`VerseMetadata`).
- `useExplorationTrail.js:9-27` persiste el recorrido del grafo en `sessionStorage` con manejo de errores y sin datos personales.
- Se usan tokens del tema (`bible-*`) y seis temas bíblicos validados, consistente con `AGENTS.md`.

## Dependencias y CI

- Las actions de GitHub están fijadas por SHA completo y el workflow usa `permissions: contents: read` (`.github/workflows/tests.yml:9-10,18,23,30`), lo que reduce riesgo de supply-chain.
- `composer audit` reporta **0 avisos de seguridad**; solo 2 paquetes abandonados informativos.
- `composer.lock` y `package-lock.json` están versionados (`git ls-files`), lo que habilita instalaciones deterministas si se adopta `npm ci`.
- CI fija PHP 8.3 y Node 22 (`.github/workflows/tests.yml:25,32`), coherente con `composer.json:12`.
- El pipeline ejecuta Pint, PHPStan, ESLint, Prettier, `vue-tsc` y la suite completa (`composer.json:65-71`, `.github/workflows/tests.yml:37-38`).
- Verificación previa en verde: 52 tests / 196 aserciones, Pint, PHPStan 0 errores, ESLint, Prettier, `vue-tsc` y `npm run build`.
- Los tests usan SQLite en memoria, correo `array`, cola `sync` y sesión `array` (`phpunit.xml:26-31`), evitando depender de MySQL en CI.
- `composer.json:108-115` habilita `optimize-autoloader`, `preferred-install: dist` y un `allow-plugins` explícito y mínimo.
- El archivo generado por Wayfinder durante el build se restauró a `HEAD`, evitando ruido no intencional en el árbol de trabajo.

## Cómo preservar estas prácticas

1. Agregar tests de regresión para las unicidades y para la derivación de metadatos en servidor.
2. Mantener `composer audit` y sumar auditoría npm al CI (OPS-002).
3. No introducir `v-html`, HTML dinámico ni `window.open` sin revisión de seguridad.
4. Documentar cualquier decisión que debilite estas protecciones (por ejemplo, hacer público `me-gusta`).
5. Revisar esta lista antes de cerrar cada cambio relevante, tal como pide `AGENTS.md`.
