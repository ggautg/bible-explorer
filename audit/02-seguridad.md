# 02 — Seguridad

Hallazgos con impacto directo en confidencialidad, integridad, disponibilidad o autenticación. Las explicaciones están en español; identificadores y fragmentos de código, en inglés. Los valores sensibles están censurados.

## Hallazgos de seguridad detallados

### CFG-001 Credencial de BD versionada en README e historial

- **Severidad:** Alta
- **Referencia:** OWASP A07:2021 / CWE-798
- **Ubicación:** `README.md:49`
- **Descripción:** El README versionado contiene una contraseña real de MySQL para el usuario `root`. La misma cadena aparece en los commits `ba55c3f` y `030c9fb`, así que no basta con editar el archivo actual.
- **Evidencia:**
  - `README.md:44-49`: `DB_CONNECTION=mysql`, `DB_USERNAME=root`, `DB_PASSWORD=<REDACTED>` (valor real censurado en este informe).
  - `git log --all --oneline -S"<REDACTED>"` → `ba55c3f`, `030c9fb`.
  - `git ls-files README.md` → `README.md` (está trackeado).
- **Impacto:** Cualquier persona con acceso de lectura al repositorio obtiene la credencial. Si se reutiliza en un entorno compartido o producción, otorga control total de la base de datos: lectura de hashes de contraseñas, secretos 2FA, notas y datos personales.
- **Escenario práctico:** Clonar el repositorio → leer `README.md:49` → conectarse a `127.0.0.1:3306` si el puerto es alcanzable → consultar `users` con privilegios `root`.
- **Corrección:** Reemplazar el valor por un placeholder, rotar la credencial, purgar el historial con `git filter-repo`/BFG y avisar a quienes ya clonaron el repositorio. Agregar secret scanning al CI y crear un usuario de aplicación con privilegios mínimos.
- **Ejemplo:**
  - Antes: `DB_PASSWORD=<REDACTED>`
  - Después: `DB_PASSWORD=cambiar-en-tu-entorno-local`
- **Esfuerzo:** S
- **Estado:** Verificado en local. Si el repositorio remoto es público `NO VERIFICADO` (no se consultó la visibilidad).

### CFG-002 `APP_DEBUG` activo y SSL desactivado por debug

- **Severidad:** Alta
- **Referencia:** OWASP A05:2021 / CWE-489
- **Ubicación:** `.env.example:4`, `config/app.php:42`, `app/Console/Commands/SeedBibleText.php:120-121`
- **Descripción:** El entorno actual ejecuta con `APP_ENV=local` y `APP_DEBUG=true`. Además, el cliente HTTP de importación desactiva la verificación TLS cuando `config('app.debug')` es true, no solo cuando el entorno es `local`.
- **Evidencia:**
  - `.env.example:4`: `APP_DEBUG=true`
  - `php artisan about`: `Environment .. local`, `Debug Mode .. ENABLED`
  - `config/app.php:42`: `'debug' => (bool) env('APP_DEBUG', false),`
  - `SeedBibleText.php:120-121`: `if (app()->environment('local') || config('app.debug')) { $client = $client->withoutVerifying(); }`
- **Impacto:** En producción, una excepción muestra stack trace, rutas internas y bindings SQL. El mismo flag abre la puerta a una importación bíblica sin validación de certificado, permitiendo contenido manipulado si hay un ataque de intermediario.
- **Escenario práctico:** Enviar una referencia malformada que provoque 500 → Ignition revela consulta y variables; o interceptar `bible.helloao.org` durante `php artisan bible:seed-text` e insertar texto ajeno en `bible_verses`.
- **Corrección:** Fijar `APP_DEBUG=false`, `APP_ENV=production` en despliegue. Quitar `|| config('app.debug')` y dejar solo `app()->environment('local')`, o eliminar el bypass por completo y confiar en el almacén de certificados del sistema.
- **Ejemplo:**
  - Antes: `if (app()->environment('local') || config('app.debug'))`
  - Después: `if (app()->environment('local'))`
- **Esfuerzo:** S
- **Estado:** Verificado en local. Producción `NO VERIFICADO`.

### CFG-003 Sin cabeceras de seguridad HTTP en la aplicación

- **Severidad:** Media
- **Referencia:** OWASP A05:2021 / CWE-693
- **Ubicación:** `bootstrap/app.php:19-27`
- **Descripción:** El registro de middleware solo agrega `HandleAppearance`, `HandleInertiaRequests` y `AddLinkHeadersForPreloadedAssets`. No hay middleware ni configuración de servidor que emita CSP, HSTS, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` ni `Permissions-Policy`.
- **Evidencia:**
  - `bootstrap/app.php:22-26`: `$middleware->web(append: [HandleAppearance::class, HandleInertiaRequests::class, AddLinkHeadersForPreloadedAssets::class]);`
  - Búsqueda de `X-Frame-Options|Content-Security-Policy|Strict-Transport-Security|Referrer-Policy|X-Content-Type-Options` en `app/` y `config/` → sin coincidencias.
- **Impacto:** La app queda expuesta a clickjacking, MIME sniffing, filtrado de la URL de origen y ausencia de HSTS. Es defensa en profundidad: CSRF ya mitiga acciones cross-site, pero la capa visual queda desprotegida.
- **Escenario práctico:** Un sitio externo incrusta la app en un `<iframe>` y superpone botones para engañar a la persona usuaria; el navegador no recibe `X-Frame-Options: DENY`.
- **Corrección:** Agregar un middleware `AddSecurityHeaders` al grupo `web`, o configurar las cabeceras en Nginx/Apache. Empezar con CSP en modo `report-only` para no romper Vite/Inertia.
- **Ejemplo:**
  - Antes: `$middleware->web(append: [HandleAppearance::class, ...]);`
  - Después: `$middleware->web(append: [AddSecurityHeaders::class, HandleAppearance::class, ...]);` con `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Strict-Transport-Security: max-age=31536000; includeSubDomains`.
- **Esfuerzo:** S
- **Estado:** Verificada la ausencia en código. Cabeceras reales `NO VERIFICADO` (requiere acceso al servidor o a una respuesta HTTP de producción).

### CFG-004 Cookie de sesión sin `Secure` y sesión sin cifrar

- **Severidad:** Media
- **Referencia:** OWASP A05:2021 / CWE-614
- **Ubicación:** `config/session.php:172,185,202`, `bootstrap/app.php:20`, `.env.example:32`
- **Descripción:** `SESSION_SECURE_COOKIE` no está definido en `.env` ni en `.env.example`, por lo que la cookie no queda marcada como `Secure`. `SESSION_ENCRYPT=false` guarda los datos de sesión en texto plano en MySQL. Dos cookies de interfaz quedan explícitamente fuera del cifrado.
- **Evidencia:**
  - `config/session.php:172`: `'secure' => env('SESSION_SECURE_COOKIE'),`
  - `config/session.php:185`: `'http_only' => env('SESSION_HTTP_ONLY', true),`
  - `config/session.php:202`: `'same_site' => env('SESSION_SAME_SITE', 'lax'),`
  - `.env.example:32`: `SESSION_ENCRYPT=false`
  - `bootstrap/app.php:20`: `$middleware->encryptCookies(except: ['appearance', 'sidebar_state']);`
  - Búsqueda de `^(SESSION_SECURE_COOKIE|SESSION_ENCRYPT)=` en `.env` → sin salida para `SESSION_SECURE_COOKIE`.
- **Impacto:** Si el despliegue sirve HTTP o detrás de un proxy que no marca HTTPS, la cookie de sesión viaja en claro. Los datos de sesión persisten sin cifrar en la tabla `sessions`.
- **Escenario práctico:** Una persona entra por `http://` en producción; un atacante en la misma red captura la cookie y reutiliza la sesión autenticada.
- **Corrección:** Definir `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=lax` (o `strict`), forzar HTTPS y HSTS. Activar `SESSION_ENCRYPT=true` si se guardan datos sensibles en sesión.
- **Ejemplo:**
  - Antes: `SESSION_DRIVER=database` (sin `SESSION_SECURE_COOKIE`)
  - Después: `SESSION_SECURE_COOKIE=true` + `SESSION_ENCRYPT=true`
- **Esfuerzo:** S
- **Estado:** Verificado en configuración local. Producción `NO VERIFICADO`.

### CFG-005 Nivel de log `debug` y PII en logs sin rotación

- **Severidad:** Media
- **Referencia:** OWASP A09:2021 / CWE-532
- **Ubicación:** `.env.example:21`, `config/logging.php:61-66`, `storage/logs/laravel.log`
- **Descripción:** El canal por defecto es `stack → single` con `LOG_LEVEL=debug`. Un error de prueba ya dejó en `laravel.log` un correo electrónico, la SQL con su binding y el stack trace completo. El canal `single` no rota por día.
- **Evidencia:**
  - `.env.example:21`: `LOG_LEVEL=debug`
  - `config/logging.php:61-66`: `'single' => ['driver' => 'single', 'path' => storage_path('logs/laravel.log'), 'level' => env('LOG_LEVEL', 'debug'), ...]`
  - `storage/logs/laravel.log` (correo censurado): `[2026-11-26 08:48:47] testing.ERROR: SQLSTATE[HY000] ... where "email" = [EMAIL_REDACTED] ... at .../Connection.php:857`
  - Tamaño observado: 680.343 bytes.
- **Impacto:** Crecimiento ilimitado del archivo y exposición de PII/consultas a quien pueda leer `storage/`. En producción con depuración activa el volumen y el detalle aumentan.
- **Escenario práctico:** Un bucle de errores 500 escribe cientos de miles de líneas con correos hasta llenar el disco; o una persona operadora con acceso a `storage/` obtiene los correos de las usuarias.
- **Corrección:** Usar `LOG_LEVEL=info|warning` en producción, canal `daily` con retención, centralización y redacción de bindings sensibles. No versionar ni servir `storage/logs`.
- **Ejemplo:**
  - Antes: `LOG_CHANNEL=stack`, `LOG_STACK=single`, `LOG_LEVEL=debug`
  - Después: `LOG_STACK=daily`, `LOG_LEVEL=info`, `LOG_DAILY_DAYS=30`
- **Esfuerzo:** S
- **Estado:** Verificado en local (el archivo existe y contiene PII). Producción `NO VERIFICADO`.

### BE-001 La pestaña "Me gusta" expone las preferencias de cualquier perfil

- **Severidad:** Media
- **Referencia:** OWASP A01:2021 / CWE-359
- **Ubicación:** `app/Http/Controllers/ProfileController.php:36-39`, `resources/js/pages/Profile/Show.vue:34-38,201-214`
- **Descripción:** El perfil público calcula la pestaña `me-gusta` para cualquier usuario consultado, sin comparar `profile.isOwn`. La interfaz tampoco oculta esa pestaña cuando se mira a otra persona.
- **Evidencia:**
  - `ProfileController.php:36-39`: `$likedIds = $user->likedPosts()->pluck('posts.id');` / `$query->whereIn('id', $likedIds);`
  - `ProfileController.php:78-79`: el payload incluye `isFollowing` e `isOwn`, pero no restringe el contenido de la pestaña.
  - `Profile/Show.vue:37`: `{ key: 'me-gusta', label: 'Me gusta' },` dentro del array `tabs` global.
  - `Profile/Show.vue:202-204`: `v-for="t in tabs"` sin filtro por `profile.isOwn`.
- **Impacto:** Cualquier usuario autenticado puede enumerar qué publicaciones le gustaron a otra persona, incluidas peticiones de oración o testimonios sensibles.
- **Escenario práctico:** Abrir `GET /perfil/victima?tab=me-gusta` y leer los `id`, `body` y metadatos devueltos en las props de Inertia.
- **Corrección:** Restringir la rama a `$viewer?->id === $user->id` y ocultar la pestaña cuando `!profile.isOwn`, o agregar una preferencia explícita de privacidad.
- **Ejemplo:** `} elseif ($viewer?->id === $user->id) { // me-gusta`
- **Esfuerzo:** S
- **Estado:** Verificado en código. La intención de producto `NO VERIFICADO`. Detalle completo en `03-backend.md`.

### BE-002 Endpoints de búsqueda sin throttling ni límite superior de entrada

- **Severidad:** Media
- **Referencia:** OWASP A04:2021 / CWE-770
- **Ubicación:** `routes/web.php:54-58,70`, `TextSearchController.php:30-48`, `SearchController.php:38-48,107-110`
- **Descripción:** Las rutas de búsqueda están dentro del grupo `auth` pero sin `throttle`; los controladores solo exigen un mínimo de caracteres y no validan `max`. `/buscar` ejecuta `MATCH ... AGAINST` sobre 31.102 versículos.
- **Evidencia:**
  - `routes/web.php:55-57`: `Route::get('/books-search', ...)`, `/structure`, `/resolve` sin `throttle`.
  - `routes/web.php:70`: `Route::get('/buscar', [TextSearchController::class, 'search'])->name('bible.search');`
  - `TextSearchController.php:30-32`: `if (mb_strlen($query) < 3) ...` (solo mínimo).
  - `SearchController.php:45`: `->where('name','like',"%{$query}%")`; `SearchController.php:110`: `$this->parser->parse((string) $request->input('ref', ''))`.
- **Impacto:** Una cuenta autenticada puede enviar consultas largas y sostener carga de CPU/MySQL. El `LIKE '%...%'` no usa índice.
- **Escenario práctico:** Bucle `GET /buscar?q=<cadena de 50.000 caracteres>` durante minutos; sin límite por usuario o IP.
- **Corrección:** Agregar `throttle:30,1`, validar `'q' => 'required|string|max:120'` y `'ref' => 'required|string|max:120'`, y escapar `%`/`_` en el `LIKE`. Las consultas ya usan bindings: **no hay inyección SQL**.
- **Ejemplo:** `Route::get('/buscar', ...)->middleware('throttle:30,1')->name('bible.search');`
- **Esfuerzo:** S
- **Estado:** Verificado en código. Carga real `NO VERIFICADO` (sin `EXPLAIN` ni prueba de carga). Detalle en `03-backend.md`.

### BE-003 La verificación de email no se exige en las áreas principales

- **Severidad:** Media
- **Referencia:** OWASP A07:2021 / CWE-287
- **Ubicación:** `routes/web.php:23`, `routes/settings.php:15-27`, `config/fortify.php:166`
- **Descripción:** Fortify habilita `Features::emailVerification()`, pero el grupo principal solo pide `auth`; `verified` aparece únicamente en configuración y borrado de cuenta.
- **Evidencia:**
  - `config/fortify.php:166`: `Features::emailVerification(),`
  - `routes/web.php:23`: `Route::middleware('auth')->group(function () {`
  - `routes/settings.php:15`: `Route::middleware(['auth', 'verified'])->group(function () {`
- **Impacto:** Una cuenta con correo no verificado puede publicar, seguir, dar "me gusta", rebiblear y escribir notas. Facilita spam y suplantación.
- **Escenario práctico:** Registrarse con un correo ajeno, no confirmarlo y usar igualmente el feed y las publicaciones.
- **Corrección:** Agregar `verified` al grupo principal si el producto exige identidad verificada; si es opcional, documentarlo y quitar la feature.
- **Ejemplo:** `Route::middleware(['auth', 'verified'])->group(function () {`
- **Esfuerzo:** S
- **Estado:** Verificado en código. Decisión de producto `NO VERIFICADO`. Detalle en `03-backend.md`.

### FE-001 El estado de notas y favoritos sobrevive al cambio de usuario en la misma pestaña

- **Severidad:** Media
- **Referencia:** OWASP A01:2021 / CWE-488
- **Ubicación:** `useNotes.js:7-9,37-39`, `useFavorites.js:8-10,38-40`, `UserMenuContent.vue:20-22,44-50`, `resources/js/routes/index.ts:85`
- **Descripción:** `notes`, `favorites`, `loaded` y `loading` son singletons del bundle. El cierre de sesión es una visita Inertia y `router.flushAll()` solo borra el caché de prefetch.
- **Evidencia:**
  - `useNotes.js:7-9`: `const notes = ref([]); const loaded = ref(false); const loading = ref(false);`
  - `useNotes.js:37-39`: `if (... && !loaded.value && !loading.value) { void loadNotes(); }`
  - `UserMenuContent.vue:20-22`: `const handleLogout = () => { router.flushAll(); };`
  - `node_modules/@inertiajs/core/dist/index.js:3222-3224`: `flushAll() { prefetchedRequests.removeAll(); }`
- **Impacto:** Una persona B puede ver notas y favoritos de A si ambas usan la misma pestaña sin recarga, hasta que hagan F5.
- **Escenario práctico:** En una computadora compartida, A guarda una nota médica, cierra sesión, entra B; `/notas` muestra la nota de A.
- **Corrección:** Resetear los singletons al cerrar sesión o al cambiar `page.props.auth.user.id`, o forzar recarga completa en el logout.
- **Ejemplo:** `if (!loaded.value || loadedUserId.value !== currentUserId.value) { notes.value = []; loaded.value = false; await loadNotes(); }`
- **Esfuerzo:** S
- **Estado:** Verificado en código. Reproducción en navegador `NO VERIFICADO`. Detalle en `05-frontend.md`.

### OPS-001 Versiones npm con avisos de seguridad conocidos (incluye runtime)

- **Severidad:** Alta
- **Referencia:** OWASP A06:2021 / CWE-1104
- **Ubicación:** `package.json:23,33,42,51`, `package-lock.json:2471,2699,7217,7323`
- **Descripción:** `npm audit` → 22 avisos (2 críticos, 9 altos, 11 moderados). `npm audit --omit=dev` → 8 avisos (7 altos, 1 moderado). `composer audit`, en cambio, da 0 avisos de seguridad.
- **Evidencia:**
  - `package-lock.json:2471`: `axios@1.18.1`; `package-lock.json:7323`: `vue@3.5.40`; `package-lock.json:7217`: `vite@8.1.5`; `package-lock.json:2699`: `concurrently@9.2.4`.
  - `GHSA-vh66-26gq-q6x8`: prototype pollution en adaptador fetch de axios.
  - `GHSA-g2v6-rqmx-r4w6`: XSS por lista negra incompleta de atributos en `@vue/server-renderer`.
  - `GHSA-pqg4-j6r4-53mv`: inyección de comandos en `shell-quote` vía `concurrently` (dev).
- **Impacto:** Superficie de ataque en el cliente y en herramientas de build/desarrollo. El aviso crítico es de desarrollo; por eso el hallazgo es Alto y no Crítica.
- **Escenario práctico:** Respuesta o redirección maliciosa explotando gadgets de axios; o comando de desarrollo con tokens controlados vía `concurrently`.
- **Corrección:** `npm audit fix`, verificar ambos comandos de auditoría y actualizar rangos. Planificar `vue-tsc` 3.3.14 como cambio major separado.
- **Ejemplo:** `"axios": "^1.18.1"` → versión sin avisos según `npm audit fix`.
- **Esfuerzo:** M
- **Estado:** Verificado en el árbol local. Árbol de producción `NO VERIFICADO`. Detalle en `06-dependencias-devops.md`.

### OPS-002 El CI no ejecuta auditoría de dependencias

- **Severidad:** Media
- **Referencia:** OWASP A06:2021 / CWE-1104
- **Ubicación:** `.github/workflows/tests.yml:34-38`, `composer.json:65-71`
- **Descripción:** `composer ci:check` corre lint, formato, tipos y tests, pero no `composer audit` ni `npm audit`.
- **Evidencia:**
  - `.github/workflows/tests.yml:37-38`: `run: composer ci:check`
  - `composer.json:65-71`: `"ci:check"` → `npm run lint:check`, `npm run format:check`, `npm run types:check`, `@test`.
- **Impacto:** OPS-001 puede fusionarse sin fallar el pipeline.
- **Escenario práctico:** Un PR actualiza `axios` a versión vulnerable; los tests pasan y nadie recibe alerta.
- **Corrección:** Agregar `composer audit --locked` y `npm audit --audit-level=high --omit=dev` al CI.
- **Ejemplo:** `run: composer ci:check && composer audit --locked && npm audit --audit-level=high --omit=dev`
- **Esfuerzo:** S
- **Estado:** Verificado en configuración. Ejecución real del workflow `NO VERIFICADO`.

### OPS-003 `composer setup` prepara un entorno inseguro si se usa fuera de CI

- **Severidad:** Media
- **Referencia:** OWASP A05:2021 / CWE-16
- **Ubicación:** `composer.json:47-53`, `.env.example:2-5,23,30-32`, `phpunit.xml:21-31`
- **Descripción:** `setup` copia `.env.example` si no hay `.env`, genera clave y migra con `--force`; la plantilla trae `APP_DEBUG=true`, `APP_ENV=local`, SQLite y `SESSION_ENCRYPT=false`.
- **Evidencia:**
  - `composer.json:49-52`: `copy('.env.example', '.env')`, `artisan key:generate`, `migrate --force`, `npm install`.
  - `.env.example:4`: `APP_DEBUG=true`; `.env.example:23`: `DB_CONNECTION=sqlite`; `.env.example:32`: `SESSION_ENCRYPT=false`.
  - `phpunit.xml:26-27`: `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`.
- **Impacto:** Si un despliegue reutiliza `setup`, puede quedar con depuración activa y sesión sin cifrar.
- **Escenario práctico:** Script de entrega continua invoca `composer setup`; no existía `.env` → se copia la plantilla insegura.
- **Corrección:** Separar `ci:setup` de `deploy:setup` y exigir un `.env` completo antes de migrar.
- **Ejemplo:** `if (!file_exists('.env')) { throw new RuntimeException('Falta .env de despliegue'); }`
- **Esfuerzo:** S
- **Estado:** Verificado en archivos. Uso real en despliegue `NO VERIFICADO`.

## Hallazgos con impacto indirecto en seguridad

| ID | Severidad | Evidencia breve | Detalle |
|---|---|---|---|
| CFG-006 | Informativa | Sin `Dockerfile`/`nginx.conf`; `README.md:93-95` usa `npm run dev` / `php artisan serve` | `02-seguridad.md` → `10-anexos.md` |
| CFG-007 | Baja | `php artisan about`: `public/storage .. NOT LINKED`; `config/filesystems.php:41-45` declara `visibility => public` | `10-anexos.md` |
| BE-004 | Media | Migración `2026_11_26_000001:42-60,78-86,95-113` elimina duplicados; `down()` no restaura | `03-backend.md`, `04-base-de-datos.md` |
| BE-008 | Informativa | `AppServiceProvider.php:54-61` aplica política fuerte solo con `app()->isProduction()` | `03-backend.md` |
| DB-002 | Baja | `create_bible_cross_references_table.php:16-25` sin `foreign()`; 0 huérfanos en local | `04-base-de-datos.md` |
| DB-004 | Informativa | `add_kind_to_posts_table.php:18`: `$table->string('kind', 24)...` sin `CHECK`/`ENUM` | `04-base-de-datos.md` |
| FE-002 | Baja | `useNotes.js:24-30`: `try ... finally` sin `catch` | `05-frontend.md` |
| OPS-005 | Informativa | 0 archivos de despliegue versionados | `06-dependencias-devops.md` |

## Mapeo OWASP Top 10 (2021)

| Categoría | Hallazgos | Comentario |
|---|---|---|
| A01 Broken Access Control | BE-001, FE-001 | Preferencias de terceros y caché entre cuentas |
| A02 Cryptographic Failures | CFG-004 | Sesión sin cifrar, cookie sin `Secure` declarado |
| A03 Injection | Ninguno confirmado | Consultas con bindings; sin `eval`/`DB::raw` con entrada directa |
| A04 Insecure Design | BE-002, BE-004, DB-001, DB-002, FE-002, FE-003, OPS-005 | Límites, deduplicación e importación |
| A05 Security Misconfiguration | CFG-002, CFG-003, CFG-004, CFG-006, CFG-007, OPS-003, OPS-005 | Depuración, cabeceras, plantillas y despliegue |
| A06 Vulnerable and Outdated Components | OPS-001, OPS-002, OPS-004 | Avisos npm y ausencia de auditoría en CI |
| A07 Identification and Auth Failures | BE-003, BE-008, CFG-001 | Verificación, política de contraseñas y credencial versionada |
| A08 Software and Data Integrity Failures | CFG-002, DB-001 | TLS desactivado por debug e importador no reanudable |
| A09 Security Logging Failures | CFG-005 | PII en logs, sin rotación |
| A10 SSRF | Ninguno confirmado | El único tercero es fijo (`bible.helloao.org`); no hay URLs controladas por el usuario hacia el servidor |

## Controles de seguridad verificados como correctos

- Autenticación Fortify con 2FA y passkeys, limiters declarados en `config/fortify.php:117-120`.
- CSRF por middleware `web` y axios enviando `X-XSRF-TOKEN` desde la cookie que emite Laravel.
- Consultas con marcadores `?` en `TextSearchController.php:44-45`; sin `eval`, `exec`, `shell_exec`, `system` ni `unserialize` en `app/`.
- Autorización por relación de usuario en notas y favoritos (`NoteController.php:27,53,82`, `FavoriteController.php:27,54,80`).
- Serializadores omiten contraseña, secretos 2FA, códigos de recuperación y `remember_token` (`User.php:40`).
- `Settings\ProfileController::update()` usa `$request->validated()` e invalida `email_verified_at` al cambiar el correo (`Settings/ProfileController.php:46-50`).
- `AppServiceProvider.php:50-52` bloquea comandos destructivos de base de datos en producción.
- `.env` está gitignored (`.gitignore:19`) y `config/session.php:185,202` usa `http_only=true` y `same_site=lax` por defecto.
- No hay `v-html`, `innerHTML`, `eval` ni `target="_blank"` en `resources/js`; Vue escapa las interpolaciones por defecto.
