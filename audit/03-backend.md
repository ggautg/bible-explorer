# 03 — Backend Laravel

Auditoría de controladores, rutas, middleware, validación, autorización y migraciones. Alcance: repositorio local y base de datos MySQL local. Todo lo que no pudo verificarse se marca `NO VERIFICADO`.

## Resumen de la capa

| Severidad | Cantidad | IDs |
|---|---:|---|
| Media | 4 | BE-001, BE-002, BE-003, BE-004 |
| Baja | 2 | BE-005, BE-007 |
| Informativa | 2 | BE-006, BE-008 |

---

## BE-001 La pestaña "Me gusta" expone las preferencias de cualquier perfil

- **Severidad:** Media
- **Capa:** Backend / Privacidad
- **Referencia:** OWASP A01:2021 / CWE-359
- **Ubicación:** `app/Http/Controllers/ProfileController.php:36-39`, `resources/js/pages/Profile/Show.vue:34-38,201-214`
- **Descripción:** El perfil público calcula la pestaña `me-gusta` para **cualquier** usuario consultado, sin comparar `profile.isOwn`. La UI tampoco oculta esa pestaña cuando se mira a otra persona.
- **Evidencia:**
  - `ProfileController.php:36-39`: `} else { // me-gusta` / `$likedIds = $user->likedPosts()->pluck('posts.id');` / `$query->whereIn('id', $likedIds);`
  - `ProfileController.php:78-79`: el payload incluye `isFollowing` e `isOwn`, pero no restringe el contenido de la pestaña.
  - `resources/js/pages/Profile/Show.vue:37`: `{ key: 'me-gusta', label: 'Me gusta' },` dentro del array `tabs` global.
  - `resources/js/pages/Profile/Show.vue:202-204`: `v-for="t in tabs"` sin filtro por `profile.isOwn`.
- **Impacto:** Cualquier usuario autenticado puede enumerar qué publicaciones le gustaron a otra persona. Si esa persona interactuó con publicaciones sensibles (peticiones de oración, testimonios, problemas de salud), se filtra una preferencia privada sin consentimiento.
- **Escenario práctico:** Una persona autenticada abre `GET /perfil/victima?tab=me-gusta` y lee los `id`, `body` y metadatos devueltos en las props de Inertia.
- **Corrección:** Restringir la rama `me-gusta` a `$viewer?->id === $user->id` (devolver 403 o colección vacía) y ocultar la pestaña en la UI cuando `!profile.isOwn`. Si se quiere mantener pública, agregar una preferencia explícita de privacidad en configuración.
- **Ejemplo:**
  - Antes: `} else { // me-gusta`
  - Después: `} elseif ($viewer?->id === $user->id) { // me-gusta`
- **Esfuerzo:** S
- **Estado:** Verificado en código. La intención de producto (público vs. privado) `NO VERIFICADO`; documentar la decisión.

## BE-002 Endpoints de búsqueda sin throttling ni límite superior de entrada

- **Severidad:** Media
- **Capa:** Backend / Disponibilidad
- **Referencia:** OWASP A04:2021 / CWE-770
- **Ubicación:** `routes/web.php:54-58,70`, `app/Http/Controllers/Bible/TextSearchController.php:30-48`, `app/Http/Controllers/Bible/SearchController.php:38-48,107-110`
- **Descripción:** Las rutas `/buscar`, `/api/bible/books-search`, `/api/bible/structure` y `/api/bible/resolve` están dentro del grupo `auth` pero no llevan middleware `throttle`. Los controladores solo exigen un mínimo de caracteres; no hay `max` ni validación formal en `q`/`ref`. `/buscar` ejecuta `MATCH ... AGAINST` sobre 31.102 versículos.
- **Evidencia:**
  - `routes/web.php:55-57`: tres `Route::get` sin `->middleware('throttle:...')`.
  - `routes/web.php:70`: `Route::get('/buscar', [TextSearchController::class, 'search'])->name('bible.search');` sin throttle.
  - `TextSearchController.php:30-32`: `$query = trim((string) $request->input('q', '')); if (mb_strlen($query) < 3) ...` (solo mínimo).
  - `TextSearchController.php:44-45`: `selectRaw('MATCH(text) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$query])` y `whereRaw(...)`.
  - `SearchController.php:38,45`: `$query = trim((string) $request->input('q','')); ... ->where('name','like',"%{$query}%")`.
  - `SearchController.php:110`: `$this->parser->parse((string) $request->input('ref', ''))` sin límite.
- **Impacto:** Una cuenta autenticada (o creada sin verificación, ver BE-003) puede enviar muchas consultas largas y sostener carga de CPU/MySQL. El índice FULLTEXT alivia pero no elimina el costo; el `LIKE '%...%'` no usa índice.
- **Escenario práctico:** Bucle `GET /buscar?q=<cadena de 50.000 caracteres>` y `GET /api/bible/books-search?q=%25%25%25` durante minutos; sin límite por IP/usuario.
- **Corrección:** Agregar `->middleware('throttle:30,1')` a las cuatro rutas, validar `'q' => 'required|string|max:120'` y `'ref' => 'required|string|max:120'`, y escapar `%`/`_` antes del `LIKE`. Nota positiva: las consultas ya usan bindings, por lo que **no hay inyección SQL** aquí.
- **Ejemplo:**
  - Antes: `Route::get('/buscar', [TextSearchController::class, 'search'])->name('bible.search');`
  - Después: `Route::get('/buscar', ...)->middleware('throttle:30,1')->name('bible.search');`
- **Esfuerzo:** S
- **Estado:** Verificado en código. Carga real en producción `NO VERIFICADO` (no se ejecutó `EXPLAIN` ni prueba de carga).

## BE-003 La verificación de email no se exige en las áreas principales

- **Severidad:** Media
- **Capa:** Backend / Autenticación
- **Referencia:** OWASP A07:2021 / CWE-287
- **Ubicación:** `routes/web.php:23`, `routes/settings.php:15-27`, `config/fortify.php:166`
- **Descripción:** Fortify habilita `Features::emailVerification()`, pero el grupo principal de rutas sociales y bíblicas solo pide `auth`. El middleware `verified` aparece únicamente en borrado de cuenta y configuración de seguridad.
- **Evidencia:**
  - `config/fortify.php:166`: `Features::emailVerification(),`
  - `routes/web.php:23`: `Route::middleware('auth')->group(function () {` (incluye feed, publicaciones, favoritos, notas y búsqueda hasta la línea 71).
  - `routes/settings.php:15`: `Route::middleware(['auth', 'verified'])->group(function () {`.
- **Impacto:** Una cuenta con correo no verificado puede publicar, seguir, dar "me gusta", rebiblear y escribir notas. Facilita spam y suplantación de identidad usando el correo de otra persona, aunque el correo no se muestre en las publicaciones.
- **Escenario práctico:** Registrarse con un correo ajeno, no confirmarlo y usar igualmente `/comunidad` y `/posts`.
- **Corrección:** Agregar `verified` al grupo de `routes/web.php:23` si el producto exige identidad verificada; si la verificación es solo opcional, documentarlo y quitar la feature para evitar falsa sensación de control.
- **Ejemplo:**
  - Antes: `Route::middleware('auth')->group(function () {`
  - Después: `Route::middleware(['auth', 'verified'])->group(function () {`
- **Esfuerzo:** S
- **Estado:** Verificado en código. Decisión de producto `NO VERIFICADO`.

## BE-004 La migración de integridades elimina duplicados sin respaldo restaurable

- **Severidad:** Media
- **Capa:** Backend / Migraciones
- **Referencia:** OWASP A04:2021 / CWE-664
- **Ubicación:** `database/migrations/2026_11_26_000001_add_integrity_indexes_to_bible_explorer.php:42-60,78-86,95-113`
- **Descripción:** `up()` ejecuta `DELETE` sobre `bible_cross_references` y `posts` para poder crear índices únicos. `down()` solo elimina los índices: no reconstruye las filas borradas.
- **Evidencia:**
  - Líneas 42-51 (`mysql`): `DELETE bcr FROM bible_cross_references bcr LEFT JOIN (...) ... WHERE keep.keep_id IS NULL`
  - Líneas 78-85: `...->each(function ($row) ... { $ids = ...->pluck('id'); DB::table('posts')->whereIn('id', $ids->slice(1))->delete(); });`
  - Líneas 95-113 (`down()`): solo `dropUnique`/`dropIndex`; ninguna reinserción.
- **Impacto:** En una instalación con duplicados históricos reales, desplegar esta migración destruye referencias y rebibleos sin posibilidad de rollback desde la propia migración. En local no hubo pérdida: se verificaron 0 duplicados antes de aplicarla, pero producción `NO VERIFICADO`.
- **Escenario práctico:** Un despliegue con 500 referencias duplicadas conserva solo el `MIN(id)` de cada grupo; las 499 restantes desaparecen y un `rollback` no las devuelve.
- **Corrección:** Antes de borrar, volcar las filas a una tabla `*_deduplication_backup` (o archivo) con timestamp y hacer que `down()` las restaure; envolver `up()` en `DB::transaction()`; o mover la limpieza a un comando Artisan explícito con `--confirm` y backup previo.
- **Ejemplo:**
  - Antes: `DB::table('posts')->whereIn('id', $ids->slice(1))->delete();`
  - Después: `DB::table('posts_deduplication_backup')->insert($rowsToDelete->all()); DB::table('posts')->whereIn('id', ...)->delete();`
- **Esfuerzo:** M
- **Estado:** Verificado en código. Conteo de duplicados en producción `NO VERIFICADO`.

## BE-005 Listados de personas sin paginación ni límite

- **Severidad:** Baja
- **Capa:** Backend / Rendimiento
- **Referencia:** OWASP A04:2021 / CWE-770
- **Ubicación:** `app/Http/Controllers/PostController.php:56-62`, `app/Http/Controllers/CommunityFeedController.php:106-121`
- **Descripción:** El feed consulta `User::query()->...->get(['id','name','email','slug','church'])` sin `limit`. El payload de cada visita al feed crece linealmente con la cantidad de personas usuarias.
- **Evidencia:**
  - `PostController.php:56-59`: `User::query()->where('id','!=',$viewer->id)->orderBy('name')->get([...])` sin `limit()`.
  - `CommunityFeedController.php:106-109`: mismo patrón sin `limit()`.
- **Impacto:** Con miles de usuarios, cada solicitud de `/comunidad` reserva memoria y serializa todos los registros, aunque la UI muestre pocos. Aumenta latencia y consumo de ancho de banda.
- **Escenario práctico:** Base con 20.000 usuarios → cada feed serializa ~20.000 objetos; varias pestañas abiertas multiplican el costo.
- **Corrección:** Limitar a 20 sugeridos (`->limit(20)`) y mover la búsqueda de personas a un endpoint con `q` + throttle. Nota: los correos se consultan pero **no se serializan** en `serializePerson()` (`PostController.php:364-373`), así que no hay fuga de correos aquí.
- **Ejemplo:**
  - Antes: `->get(['id', 'name', 'email', 'slug', 'church'])`
  - Después: `->limit(20)->get(['id', 'name', 'slug', 'church'])`
- **Esfuerzo:** S
- **Estado:** Verificado en código. Volumen real de usuarios `NO VERIFICADO` (local tiene 1).

## BE-006 Controladores huérfanos con lógica divergente

- **Severidad:** Informativa
- **Capa:** Backend / Mantenibilidad
- **Referencia:** CWE-1164
- **Ubicación:** `app/Http/Controllers/CommunityFeedController.php`, `app/Http/Controllers/ProfileWallController.php`, `routes/web.php:25`
- **Descripción:** `php artisan route:list --json` devuelve 72 rutas y ninguna apunta a estos dos controladores. El feed real usa `PostController@index`.
- **Evidencia:**
  - `routes/web.php:25`: `Route::get('/comunidad', [PostController::class, 'index'])->name('community.feed');`
  - `php artisan route:list --json | Where-Object { $_.action -match 'CommunityFeedController|ProfileWallController' }` → 0 resultados (de 72 rutas).
  - `grep` en `resources/js` sin referencias a `ProfileWallController`.
- **Impacto:** Código muerto que duplica serialización y reglas de negocio; si alguien lo activa por error, puede comportarse distinto (por ejemplo, `CommunityFeedController.php:36-52` lee `profile_comments` en lugar de `posts`).
- **Escenario práctico:** Un futuro cambio migra el feed a `CommunityFeedController` y reintroduce tablas legacy ya eliminadas lógicamente (ver DB-003).
- **Corrección:** Eliminarlos confirmando con el equipo, o marcarlos claramente como obsoletos y cubrirlos con un test que verifique que no están rutados.
- **Ejemplo:** `git rm app/Http/Controllers/CommunityFeedController.php app/Http/Controllers/ProfileWallController.php` (previo OK del equipo).
- **Esfuerzo:** S
- **Estado:** Verificado en local. Intención de negocio `NO VERIFICADO`.

## BE-007 Datos de demostración acoplados a correos y nombres concretos

- **Severidad:** Baja
- **Capa:** Backend / Calidad
- **Referencia:** CWE-1188
- **Ubicación:** `app/Models/User.php:182-214`, `app/Http/Controllers/CommunityFeedController.php:139-192`, `app/Http/Controllers/ProfileController.php:49-65`
- **Descripción:** Avatares, iglesias y "metas de Giovanni" se resuelven comparando correos/nombres/slugs fijos en código. Aunque `User::avatarUrl()` centraliza parte del mapeo, `CommunityFeedController` mantiene copias duplicadas.
- **Evidencia:**
  - `User.php:183-196`: array `$known` con `giovanni.arestivo@example.test`, `piero.arestivo@example.test`, `jose.britez@example.test`.
  - `User.php:208-214`: `churchName()` devuelve iglesias por correo electrónico exacto.
  - `CommunityFeedController.php:139-155` y `161-192`: mismos mapas repetidos.
  - `ProfileController.php:49-65`: `$isGiovanni` habilita `profileGoals` con valores `current/total` quemados.
- **Impacto:** Si una cuenta real usa esos correos o nombres en producción, recibe avatar/iglesia/metas ajenos sin control del administrador. Además, cualquier cambio de nombre rompe la personalización.
- **Escenario práctico:** Se crea una cuenta con `jose.britez@example.test` en producción → la app le asigna automáticamente una iglesia y avatar predefinidos.
- **Corrección:** Mover estos datos a columnas de `users` o a un seeder/config de demostración separado por entorno, y eliminar las copias duplicadas del controlador muerto (BE-006).
- **Ejemplo:** `config/demo_profiles.php` con `env('DEMO_PROFILES_ENABLED', false)` y consulta por `slug`, nunca por correo electrónico.
- **Esfuerzo:** S
- **Estado:** Verificado en código. Uso real en producción `NO VERIFICADO`.

## BE-008 Política de contraseñas fuerte solo en producción

- **Severidad:** Informativa
- **Capa:** Backend / Autenticación
- **Referencia:** OWASP A07:2021 / CWE-521
- **Ubicación:** `app/Providers/AppServiceProvider.php:54-61`, `app/Concerns/PasswordValidationRules.php:17`, `vendor/laravel/framework/src/Illuminate/Validation/Rules/Password.php:172`
- **Descripción:** `Password::defaults()` devuelve `null` fuera de producción; el framework interpreta eso como `Password::min(8)`. La política de 12 caracteres + mayúscula + minúscula + número + símbolo + `uncompromised()` solo aplica con `APP_ENV=production`.
- **Evidencia:**
  - `AppServiceProvider.php:54-61`: `app()->isProduction() ? Password::min(12)... : null`.
  - `PasswordValidationRules.php:17`: `'password' => $this->passwordRules(),` → `Password::default()`.
  - `Password.php:172`: `return $password instanceof Rule ? $password : static::min(8);`
- **Impacto:** Los tests (que corren con `APP_ENV=testing`, `phpunit.xml:21`) nunca ejercitan la política real; un cambio que la debilite pasaría CI. Además, `uncompromised()` introduce dependencia de HIBP en producción.
- **Escenario práctico:** Se agrega un test de registro con contraseña `12345678`; pasa en CI; en producción la misma contraseña sería rechazada, pero nadie lo validó antes del despliegue.
- **Corrección:** Definir la política fuerte siempre y relajarla solo mediante un flag explícito de test, o agregar un test que fuerce `app()->detectEnvironment(fn () => 'production')` y verifique las reglas. Documentar la dependencia externa de `uncompromised()`.
- **Ejemplo:** extraer `passwordRulesForProduction()` y testearlo directamente sin depender de `APP_ENV`.
- **Esfuerzo:** S
- **Estado:** Verificado en código. Disponibilidad de HIBP en producción `NO VERIFICADO`.

## Controles correctos verificados en esta capa

- No hay `DB::raw` con entrada del usuario sin binding: `TextSearchController.php:44-45` usa marcadores `?`. No se encontraron `eval`, `exec`, `shell_exec`, `unserialize` ni `system` en `app/` (`grep` → 0 coincidencias).
- Autorización por usuario: notas y favoritos se consultan siempre desde `$request->user()->notes()/favorites()` (`NoteController.php:27,53,82`; `FavoriteController.php:27,54,80`), sin ID de usuario manipulable.
- Idempotencia de "me gusta", seguir y rebibleo apoyada en índices únicos reales: `post_likes` (`create_post_likes_table.php:21`), `follows` (`create_follows_table.php:20`) y `posts_user_rebible_unique` (`2026_11_26_000001:88-92`).
- Metadatos de versículo derivados en servidor con `VerseMetadata::forReference()` (`PostController.php:187-205`, `NoteController.php:48-51`), evitando confiar en `book_name` del cliente.
- Serializadores omiten `password`, `two_factor_secret`, `two_factor_recovery_codes` y `remember_token` (`User.php:40`) y no exponen correos en publicaciones/personas (`PostController.php:364-394`).
- `Settings\ProfileController::update()` usa `$request->validated()` e invalida `email_verified_at` al cambiar el correo (`Settings/ProfileController.php:46-50`).
- `Settings\SecurityController::edit()` selecciona `credential` pero no lo serializa (`SecurityController.php:39-50`).
- `AppServiceProvider.php:48-52` usa fechas inmutables y prohíbe comandos destructivos en producción.
- Límites presentes en login (5/min), 2FA (5/min), passkeys (10/min), actualización de contraseña (6/min), publicaciones/seguir/me gusta/rebibleo (15/min) y favoritos/notas (30/min), según `config/fortify.php:117-120` y rutas con `throttle`.
