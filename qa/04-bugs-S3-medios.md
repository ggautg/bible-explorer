# 04 — Bugs S3 (Medios / Moderados)

## Índice
- [Estado](#estado)
- [BUG-004 — El slug no se actualiza al cambiar el nombre](#bug-004)
- [BUG-009 — Verificación por correo activa pero no exigida](#bug-009)
- [BUG-014 — Búsqueda de texto con 500 bajo SQLite](#bug-014)
- [BUG-017 — Reintentos agotados lanzan excepción en los seeds](#bug-017)
- [BUG-018 — `bible:seed-crossrefs` informa éxito aunque falle la API](#bug-018)
- [BUG-021 — Visita Inertia con CSRF vencido muestra “Page Expired”](#bug-021)
- [BUG-022 — Sesión caducada en APIs sin redirección al login](#bug-022)
- [BUG-023 — Primera visita Inertia descarta notas y favoritos guardados](#bug-023)
- [BUG-026 — Cambiar el correo deja la cuenta sin verificar y sin aviso](#bug-026)
- [BUG-027 — El reset de contraseña revela si un correo está registrado](#bug-027)

## Estado
No hay defectos S3 abiertos. BUG-004 (Fase 2, perfil) y BUG-009 (Fase 6, settings) fueron corregidos en la ronda de corrección 1; BUG-014 (ronda 3) se corrigió con un fallback según el driver; BUG-017 y BUG-018 (ronda 7) se corrigieron en los comandos de importación; BUG-021 y BUG-022 (ronda 8) se corrigieron en el manejo de excepciones y en el cliente axios; BUG-023 (ronda 9) se corrigió en la inicialización del usuario de Inertia; BUG-026 y BUG-027 (ronda 11) se corrigieron en el controlador de perfil y en la respuesta de Fortify para reset. Todos fueron verificados con la suite QA en verde y, en los casos de BUG-014, BUG-022 y BUG-023, también en navegador. Las rondas 12, 13, 14, 15, 16 y 17 no agregaron defectos S3.

<a id="bug-004"></a>

### [BUG-004] Cambiar el nombre no actualiza el slug del perfil
- **Severidad:** S3
- **Prioridad:** P2
- **Frecuencia:** Ocasional (solo al editar el nombre)
- **Tipo:** Funcional / Integridad de datos
- **Módulo / Capa:** Backend / Configuración de perfil
- **Estado de verificación:** CONFIRMADO → **CORREGIDO y verificado** (ronda de corrección 1)
- **Corrección aplicada:** `App\Models\User::booted()` regenera el slug en `updating` cuando `name` cambia, respetando unicidad.
- **Ubicación original:** `app/Http/Controllers/Settings/ProfileController.php:44-52` y `app/Http/Requests/Settings/ProfileUpdateRequest.php:18-21`
- **Entorno:** PHP 8.3.36, Laravel 13.44.1, SQLite `:memory:`, usuario autenticado con slug existente
- **Precondiciones:** usuario con `slug = juan-perez`; sesión autenticada
- **Pasos para reproducir:**
  1. Autenticarse como el usuario con slug `juan-perez`.
  2. Enviar `PATCH /settings/profile` con `name=Ana María Ñandú` y el correo sin cambios.
  3. Consultar el slug guardado y abrir el enlace del perfil del header.
- **Resultado esperado:** el slug pasa a `ana-maria-nandu` (o se mantiene deliberadamente estable si es una decisión de producto documentada) y el enlace del perfil sigue siendo válido.
- **Resultado actual:** el slug permanece `juan-perez` mientras el nombre mostrado es `Ana María Ñandú`; la URL del perfil queda desactualizada respecto del nombre.
- **Evidencia:** `tests/QA/AuthSecurityTest.php:59` — `Failed asserting that two strings are identical. --- Expected 'ana-maria-nandu' +++ Actual 'juan-perez'`. El controlador hace `fill($request->validated())` y `save()`, pero `ProfileUpdateRequest::rules()` solo valida `name` y `email`; no hay lógica de regeneración de slug en el controlador ni en el modelo.
- **Causa raíz probable:** el slug se trata como dato inmutable desde la migración inicial y no se recalcula cuando cambia el atributo del que deriva. No hay invalidación ni redirect permanente de la URL vieja.
- **Impacto:** URLs de perfil con nombres viejos, confusión para el usuario y enlaces compartidos que dejan de coincidir con el nombre visible. Si se corrige BUG-003 generando slug desde el nombre, este defecto se vuelve más visible.
- **Workaround:** editar el slug manualmente en base de datos; no existe desde la UI.
- **Recomendación de corrección:** decidir la política: (a) regenerar slug con sufijo único en `updating` del modelo cuando `name` cambia y redirigir 301 del slug viejo, o (b) documentar que el slug es permanente y mostrarlo como tal en la UI. Implementar (a) en un único lugar para no duplicar la lógica con BUG-003.
- **Test que lo reproduce:** `tests/QA/AuthSecurityTest.php` → `updating a name refreshes the profile slug`.
- **Esfuerzo estimado:** S

<a id="bug-009"></a>

### [BUG-009] La verificación por correo está configurada pero no se exige
- **Severidad:** S3
- **Prioridad:** P2
- **Frecuencia:** Siempre (cada usuario con correo sin verificar)
- **Tipo:** Seguridad funcional / Configuración
- **Módulo / Capa:** Backend / Autenticación / Settings
- **Estado de verificación:** CONFIRMADO → **CORREGIDO y verificado** (ronda de corrección 1)
- **Corrección aplicada:** `App\Models\User` implementa `Illuminate\Contracts\Auth\MustVerifyEmail`.
- **Ubicación original:** `app/Models/User.php:5` (import de `MustVerifyEmail` comentado) y `:41` (`implements PasskeyUser` solamente); `config/fortify.php:167` y `routes/settings.php:15-26`
- **Entorno:** PHP 8.3.36, Laravel 13.44.1, SQLite `:memory:`, Fortify con `Features::emailVerification()` habilitado
- **Precondiciones:** usuario autenticado con `email_verified_at = NULL`
- **Pasos para reproducir:**
  1. Crear un usuario con `User::factory()->unverified()->create()`.
  2. Autenticarse y pedir `GET /settings/appearance` o `GET /settings/security`.
  3. Enviar `DELETE /settings/profile` con la contraseña correcta.
  4. Abrir `GET /settings/profile` y mirar el prop `mustVerifyEmail`.
- **Resultado esperado:** las rutas bajo `verified` redirigen a `verification.notice`, el borrado queda bloqueado y el prop `mustVerifyEmail` es `true` para mostrar “Reenviar correo de verificación”.
- **Resultado actual:** las rutas responden **200**, el borrado se ejecuta y `mustVerifyEmail` es `false`, aunque el envío y la confirmación de correo funcionan porque el trait `MustVerifyEmail` aporta los métodos.
- **Evidencia:**
  - `tests/QA/SettingsFrontendIntegrityTest.php:42` — `Property [mustVerifyEmail] does not match the expected value. Failed asserting that false is identical to true.`
  - `tests/QA/SettingsFrontendIntegrityTest.php:54` — `Expected response status code [201, 301, 302, 303, 307, 308] but received 200.`
  - `Illuminate\Foundation\Auth\User` usa el trait `Illuminate\Auth\MustVerifyEmail`, pero `App\Models\User` no implementa la interfaz `Illuminate\Contracts\Auth\MustVerifyEmail`; el middleware `verified` y `ProfileController::edit()` consultan `instanceof`, por lo que siempre devuelven falso.
  - La suite heredada `tests/Feature/Auth/EmailVerificationTest.php` pasa porque exercise los métodos del trait, no el middleware: la funcionalidad existe pero no se aplica como gate.
- **Causa raíz probable:** se importó el contrato como comentario al generar el modelo y nunca se agregó a la declaración `implements`. No hay test heredado que cruce el middleware `verified` con un usuario sin verificar.
- **Impacto:** la política de verificación configurada no se cumple: cuentas sin verificar pueden entrar a seguridad/apariencia y borrar su cuenta, y la UI no ofrece reenviar el correo. La verificación sigue siendo útil para el Redirect de `verification.verify`, pero deja de ser un requisito. No expone datos de otros usuarios.
- **Workaround:** completar el enlace de verificación enviado por correo; mientras tanto, restringir manualmente el acceso a settings desde el frontend. No hay forma de que el usuario vea el botón de reenvío.
- **Recomendación de corrección:** descomentar `use Illuminate\Contracts\Auth\MustVerifyEmail;` y agregarlo a `implements` en `App\Models\User`; verificar que el backfill de `email_verified_at` sea consistente con cuentas preexistentes y reforzar la suite con un test de middleware `verified`.
- **Test que lo reproduce:** `tests/QA/SettingsFrontendIntegrityTest.php` → `profile and appearance pages render the expected inertia components` y `unverified users cannot open verified settings or delete their account`.
- **Esfuerzo estimado:** S

<a id="bug-014"></a>

### [BUG-014] `/buscar?q=...` responde 500 bajo SQLite
- **Severidad:** S3
- **Prioridad:** P2
- **Frecuencia:** Siempre que se busca con 3 o más caracteres en un entorno SQLite
- **Tipo:** Funcional / Compatibilidad de driver
- **Módulo / Capa:** Backend / Bíblicas / Búsqueda de texto
- **Estado de verificación:** CONFIRMADO en navegador → **CORREGIDO y verificado** (ronda 3)
- **Corrección aplicada:** `TextSearchController` elige `MATCH ... AGAINST` solo cuando el driver no es SQLite; en SQLite usa `LIKE ? ESCAPE ?` con `%`, `_` y `\` escapados, ordenando por referencia.
- **Ubicación original:** `app/Http/Controllers/Bible/TextSearchController.php:43-48`
- **Entorno:** `artisan serve` sobre SQLite temporal en `C:\Users\Desarrollo\AppData\Local\Temp\opencode\`, sin datos reales
- **Precondiciones:** sesión autenticada y versículos sembrados con “Dios” en el texto
- **Pasos para reproducir:**
  1. Abrir `http://127.0.0.1:8000/buscar?q=Dios`.
  2. Observar la respuesta y la consola del navegador.
- **Resultado esperado:** la página lista los versículos que contienen el término, sin errores 500.
- **Resultado actual:** pantalla de `Internal Server Error`; `QueryException` con `SQLSTATE[HY000]: near "(": syntax error` y dos 500 en consola.
- **Evidencia:** cuerpo de la página con el SQL `MATCH(text) AGAINST(Juan IN NATURAL LANGUAGE MODE)`; `tests/QA/BibleReadingSearchTest.php` marcaba el caso como omitido por depender de MySQL.
- **Causa raíz probable:** la consulta de texto completo se escribió para MySQL sin guard de driver, y QA ejecuta sobre SQLite.
- **Impacto:** la búsqueda de texto —funcionalidad principal— queda caída en cualquier instalación SQLite; en MySQL sigue funcionando y no se vio afectada.
- **Workaround:** usar MySQL o buscar con menos de 3 caracteres (lo que devuelve resultados vacíos).
- **Recomendación de corrección:** abstrair la estrategia de búsqueda por driver, manteniendo FULLTEXT en MySQL y `LIKE` escapado en SQLite; reactivar el test omitido.
- **Test que lo reproduce:** `tests/QA/BibleReadingSearchTest.php` → `text search with three characters works through the sqlite fallback`.
- **Esfuerzo estimado:** S

<a id="bug-017"></a>

### [BUG-017] Reintentos agotados lanzan una excepción sin controlar en los comandos de importación
- **Severidad:** S3
- **Prioridad:** P2
- **Frecuencia:** Siempre que la API externa falla de forma persistente o corta la conexión
- **Tipo:** Robustez / Manejo de errores
- **Módulo / Capa:** Backend / Comandos Artisan (`bible:seed-text`, `bible:seed-crossrefs`)
- **Estado de verificación:** CONFIRMADO por test → **CORREGIDO y verificado** (ronda 7)
- **Corrección aplicada:** `retry(..., null, false)` para que la última respuesta 500 se devuelva al llamador, y `catch (ConnectionException)` en `handle()` con mens propio y `self::FAILURE`.
- **Ubicación original:** `app/Console/Commands/SeedBibleText.php:42,118` y `app/Console/Commands/SeedBibleCrossReferences.php:43-45,55,79`
- **Entorno:** PHP 8.3.36, Laravel 13.44.1, SQLite `:memory:`; API simulada con `Http::fake()`
- **Precondiciones:** API `bible.helloao.org` responde 500 en todos los intentos, o lanza `ConnectionException`
- **Pasos para reproducir:**
  1. Simular tres respuestas 500 seguidas con `Http::fake()`.
  2. Ejecutar `php artisan bible:seed-text spa_rvg` (o `bible:seed-crossrefs`).
  3. Observar la salida y el código de salida.
- **Resultado esperado:** mensaje de error legible, sin stack trace, y código de salida de fallo; la BD queda sin importar.
- **Resultado actual (pre-corrección):** `RequestException: HTTP request returned status code 500` escapaba de `Http::get()` y el comando terminaba con traza PHP; el `if ($response->failed())` nunca se ejecutaba.
- **Evidencia:** antes del fix, `php artisan test tests/QA/ExternalApiResilienceTest.php` mostró el error en `vendor/.../Response.php:340` con traza en `SeedBibleText.php:42`, `SeedBibleCrossReferences.php:44` y `SeedBibleCrossReferences.php:55`. Después del fix: 8/8 tests pasan.
- **Causa raíz probable:** en Laravel 13, `retry($times, $sleep, $when, $throw = true)` lanza la última respuesta fallida como `RequestException`. Los comandos usaban el valor por defecto y además construían un `PendingRequest` nuevo sin capturar excepciones de transporte.
- **Impacto:** una caída temporal de la API externa rompe el seed con un error técnico; cualquier tarea programada que leyera el código de salida no obtenía un fallo controlado, y la salida no indicaba qué hacer.
- **Workaround:** ejecutar el comando a mano y revisar la traza; no había mensaje accionable.
- **Recomendación de corrección:** desactivar el `throw` automático de `retry`, capturar `ConnectionException` por separado y centralizar el mensaje de error; conservar los reintentos con backoff.
- **Test que lo reproduce:** `tests/QA/ExternalApiResilienceTest.php` → `bible:seed-text fails cleanly when the api keeps returning 500`, `bible:seed-crossrefs fails cleanly when the dataset index is unavailable` y `bible:seed-crossrefs reports failed chapters instead of claiming success`.
- **Esfuerzo estimado:** S

<a id="bug-018"></a>

### [BUG-018] `bible:seed-crossrefs` informa éxito aunque la API falle o no devuelva datos
- **Severidad:** S3
- **Prioridad:** P2
- **Frecuencia:** Cada vez que falla el índice o uno o más capítulos del conjunto de datos
- **Tipo:** Integridad de datos / Observabilidad
- **Módulo / Capa:** Backend / Comando Artisan `bible:seed-crossrefs`
- **Estado de verificación:** CONFIRMADO por código y test → **CORREGIDO y verificado** (ronda 7)
- **Corrección aplicada:** validar `failed()` del índice y del payload vacío, contar capítulos fallidos, emitir `No se pudieron importar N capítulos...` y devolver `self::FAILURE` cuando corresponda.
- **Ubicación original:** `app/Console/Commands/SeedBibleCrossReferences.php:43-45,59-61,69-71`
- **Entorno:** PHP 8.3.36, Laravel 13.44.1, SQLite `:memory:`; API simulada con `Http::fake()`
- **Precondiciones:** `books.json` responde sin `books`, o el endpoint de capítulo responde 500 de forma persistente
- **Pasos para reproducir:**
  1. Simular `books.json` con un libro de un capítulo y `GEN/1.json` con cuatro respuestas 500.
  2. Ejecutar `bible:seed-crossrefs` y mirar el código de salida y el texto final.
  3. Consultar `bible_cross_references`.
- **Resultado esperado:** error explícito con la cantidad de capítulos no importados, código de fallo y tabla vacía (o parcial, pero nunca un “importado correctamente” engañoso).
- **Resultado actual (pre-corrección):** el capítulo fallido solo avanzaba la barra de progreso y al final se imprimía `Referencias cruzadas importadas correctamente.` con código 0; con un índice vacío pasaba lo mismo.
- **Evidencia:** código original sin ninguna rama de error (`->json('books', [])` directo y `if ($response->successful()) { ... }` sin `else`). Los tests nuevos fallaron hasta que se agregó el conteo de fallos; tras la corrección, 8/8 pasan.
- **Causa raíz probable:** el comando asumió que la API siempre responde 200 y trató las respuestas fallidas como “capítulo sin datos” en vez de como error.
- **Impacto:** el grafo bíblico puede quedar vacío o incompleto mientras la automatización cree que el import salió bien; nadie se entera hasta que un usuario nota que faltan conexiones.
- **Workaround:** revisar manualmente el row count de `bible_cross_references` después de cada importación.
- **Recomendación de corrección:** verificar estado HTTP y forma del payload, acumular capítulos fallidos y devolver código distinto de cero; opcionalmente registrar los pares libro/capítulo fallidos.
- **Test que lo reproduce:** `tests/QA/ExternalApiResilienceTest.php` → `bible:seed-crossrefs reports failed chapters instead of claiming success` y `bible:seed-crossrefs fails cleanly when the dataset index is unavailable`.
- **Esfuerzo estimado:** S

<a id="bug-021"></a>

### [BUG-021] Visita Inertia con CSRF vencido muestra la página genérica “Page Expired”
- **Severidad:** S3
- **Prioridad:** P2
- **Frecuencia:** Ocasional (acciones posteriores a la expiración de la sesión)
- **Tipo:** Funcional / Manejo de sesión
- **Módulo / Capa:** Backend / Manejo de excepciones e Inertia
- **Estado de verificación:** CONFIRMADO por `curl` y por test → **CORREGIDO y verificado** (ronda 8)
- **Corrección aplicada:** callback de render para `HttpException` que, con status 419 y header `X-Inertia`, devuelve `redirect()->guest(route('login'))`; las peticiones sin ese header conservan el 419 del framework.
- **Ubicación original:** `bootstrap/app.php:29-37` (solo existía el callback para 404 de rutas bíblicas)
- **Entorno:** `artisan serve` sobre SQLite temporal, petición con `curl` y cookie jar; sin datos reales
- **Precondiciones:** sesión iniciada y token `XSRF-TOKEN` ya inválido (logout o expiración)
- **Pasos para reproducir:**
  1. Hacer login y guardar la cookie con el token XSRF.
  2. Cerrar la sesión (`POST /logout`) y reutilizar el token viejo.
  3. Enviar `POST /posts` con headers `X-Inertia: true`, `X-Inertia-Version` válido y `X-XSRF-TOKEN` vencido.
  4. Observar código, header `Location` y cuerpo.
- **Resultado esperado:** redirección a `/login`, sin markup de error dentro del modal Inertia.
- **Resultado actual (pre-corrección):** `419` con `Content-Type: text/html` y cuerpo `Page Expired`, que Inertia inyectaba en la página en curso.
- **Evidencia:** sondeo `POST /posts con token vencido (Inertia): 419 ... Cuerpo: Page Expired`; tras el fix, `302` con `Location: http://127.0.0.1:8000/login`. En navegador, un clic en “Me gusta” con la sesión invalidada generó `POST /posts/1/like` con 302 y la SPA terminó en `/login`. El test `an inertia request with an expired csrf token is sent to login` falló antes del fix con `Failed asserting that 419 is identical to 302`.
- **Causa raíz probable:** Laravel convierte `TokenMismatchException` en `HttpException(419)` dentro de `prepareException()` antes de ejecutar los callbacks de render; el callback registrado esperaba `TokenMismatchException`, así que nunca hacía match y se usaba la respuesta HTML genérica.
- **Impacto:** al caducar la sesión a mitad de una publicación, like o respuesta, la acción se pierde y la UI queda trabada en una página de error sin salida clara.
- **Workaround:** recargar a mano o navegar a `/login`.
- **Recomendación de corrección:** enganchar el callback en el tipo que realmente llega (`HttpException`), filtrar por status 419 y por `X-Inertia`, y dejar el 419 para clientes JSON/API.
- **Test que lo reproduce:** `tests/QA/SessionExpiryTest.php` → `an inertia request with an expired csrf token is sent to login`, `a non inertia request keeps the framework 419 response` y `a json request with an expired csrf token keeps the framework 419 response`.
- **Esfuerzo estimado:** S

<a id="bug-022"></a>

### [BUG-022] Caducidad de sesión en las APIs de notas y favoritos sin redirección al login
- **Severidad:** S3
- **Prioridad:** P2
- **Frecuencia:** Ocasional (sesión expirada con la SPA abierta)
- **Tipo:** Frontend / Gestión de sesión
- **Módulo / Capa:** Frontend / axios y composables `useNotes` / `useFavorites`
- **Estado de verificación:** PROBABLE por inspección antes del fix (sin evidencia de navegador pre-corrección) y CONFIRMADO en navegador después → **CORREGIDO y verificado** (ronda 8)
- **Corrección aplicada:** interceptor global de respuestas en `resources/js/app.js` que envía a `/login` ante 401 o 419, con una guardia para no repetir la navegación si fallan varias peticiones a la vez.
- **Ubicación original:** `resources/js/app.js` sin interceptores; `resources/js/composables/useNotes.js:44-49` y `useFavorites.js:45-52` solo muestran un toast al cargar
- **Entorno:** navegador integrado sobre `artisan serve` con SQLite temporal
- **Precondiciones:** usuario en `/leer/GEN/1`, notas y favoritos ya cargados en memoria
- **Pasos para reproducir:**
  1. Iniciar sesión y abrir `/leer/GEN/1`.
  2. Invalidar la sesión con `fetch('/logout', { method: 'POST', headers: { 'X-XSRF-TOKEN': token } })` sin salir de la página.
  3. Hacer clic en “Guardar Génesis 1:1 en favoritos”.
  4. Observar las peticiones y la URL final.
- **Resultado esperado:** la petición 401 lleva al usuario al login, sin dejar la UI mostrando el favorito como guardado.
- **Resultado actual (pre-corrección):** el 401 se propagaba al llamador; los composables solo avisaban con un toast durante la carga inicial, no en guardado/borrado, y no había redirección global (conclusión por lectura de código, no ejecutada antes del fix).
- **Evidencia:** después del fix, el sondeo mostró `GET /api/notes tras logout: 401 {"message":"Unauthenticated."}` y, en navegador, `performance.getEntriesByType('resource')` guardó `["/api/favorites","/api/notes","/api/favorites"]` antes de que la SPA cayera en `/login`; el test estático `the frontend sends axios 401 and 419 responses to login` verifica el interceptor.
- **Causa raíz probable:** el manejo de errores estaba repartido por composable y cubría solo la carga; no existía un punto global que reaccionara a respuestas de autenticación expirada.
- **Impacto:** el usuario puede creer que guardó una nota o un favorito cuando la sesión ya murió; queda en una pantalla con datos en cache hasta que recarga manualmente.
- **Workaround:** recargar la página para que el middleware de auth redirija al login.
- **Recomendación de corrección:** interceptor global de axios para 401/419 con guardia de navegación única, manteniendo el rechazo de la promesa para que cada composable conserve su manejo local.
- **Test que lo reproduce:** `tests/QA/SessionExpiryTest.php` → `the frontend sends axios 401 and 419 responses to login` y `expired sessions still produce json 401 on the bible apis`.
- **Esfuerzo estimado:** S

<a id="bug-023"></a>

### [BUG-023] La primera visita Inertia descarta notas y favoritos guardados en la página anterior
- **Severidad:** S3
- **Prioridad:** P2
- **Frecuencia:** Constante en la primera navegación SPA luego de una carga completa
- **Tipo:** Funcional / Estado del cliente
- **Módulo / Capa:** Frontend / `resources/js/app.js` y composables `useNotes` / `useFavorites`
- **Estado de verificación:** CONFIRMADO en navegador → **CORREGIDO y verificado** (ronda 9)
- **Corrección aplicada:** el primer evento `success` de Inertia solo inicializa `currentUserId` y no ejecuta `resetNotes()`, `resetFavorites()` ni `router.flushAll()`.
- **Ubicación original:** `resources/js/app.js:64-75` (`let currentUserId = null` y handler `router.on('success')` sin guardia de inicialización)
- **Entorno:** navegador integrado sobre `artisan serve` con SQLite temporal, sin datos reales
- **Precondiciones:** sesión iniciada, favorito guardado desde `/leer/GEN/1` cargado con F5
- **Pasos para reproducir:**
  1. Hacer login y cargar `/leer/GEN/1` con recarga completa.
  2. Tocar “Guardar Génesis 1:1 en favoritos” y esperar el `POST /api/favorites` 200.
  3. Navegar por el enlace SPA a `/favoritos`.
  4. Observar la lista y las peticiones de red.
- **Resultado esperado:** el favorito recién guardado aparece en `/favoritos`.
- **Resultado actual (pre-corrección):** la página queda vacía, sin ningún `GET /api/favorites` nuevo; una consulta directa a la API devuelve la fila con `id` y referencia correctos. Navegando a otra página y volviendo, el favorito aparece.
- **Evidencia:** `GET /api/favorites` 200, `POST /api/favorites` 200, luego `GET /favoritos` 200 sin `GET /api/favorites` posterior y 0 artículos en el DOM; la API devolvía `{"count":1,"first":{"id":6,"ref":"GEN 1:1"}}`. Tras el fix, 1 artículo con “GÉNESIS 1:1”.
- **Causa raíz probable:** `currentUserId` arranca en `null` y la primera visita Inertia tras una carga completa dispara el handler de cambio de usuario: llama a `resetFavorites()`/`resetNotes()` después de que el componente ya leyó el estado en memoria, sin volver a cargarlo.
- **Impacto:** el usuario cree que perdió la nota o el favorito que acaba de guardar; los datos siguen en el servidor, pero la UI lo desmiente hasta recargar o navegar dos veces.
- **Workaround:** recargar la página o navegar a otra sección y volver.
- **Recomendación de corrección:** inicializar `currentUserId` con el usuario de la primera visita exitosa y limitar el reset a cambios reales de usuario dentro de la SPA.
- **Test que lo reproduce:** `tests/QA/ClientStateRaceTest.php` → `the first inertia visit does not discard client state initialized during a full page load`.
- **Esfuerzo estimado:** S

<a id="bug-026"></a>

### [BUG-026] Cambiar el correo deja la cuenta sin verificar y sin aviso
- **Severidad:** S3
- **Prioridad:** P2
- **Frecuencia:** Constante: afecta a todo cambio de correo en el perfil
- **Tipo:** Funcional / Verificación de cuenta
- **Módulo / Capa:** Backend / `app/Http/Controllers/Settings/ProfileController.php`
- **Estado de verificación:** CONFIRMADO por test fallido → **CORREGIDO y verificado** (ronda 11)
- **Corrección aplicada:** al guardar un correo distinto se llama a `sendEmailVerificationNotification()` después de persistir el cambio.
- **Ubicación original:** `app/Http/Controllers/Settings/ProfileController.php:44-60`
- **Entorno:** PHP 8.3.36, Laravel 13.44.1, SQLite `:memory:`, `Notification::fake()`
- **Precondiciones:** usuario autenticado y verificado con correo `viejo@example.com`
- **Pasos para reproducir:**
  1. Hacer `PATCH /settings/profile` con un correo nuevo.
  2. Revisar `email_verified_at` y las notificaciones enviadas.
  3. Abrir el enlace firmado del correo nuevo.
- **Resultado esperado:** la verificación queda inválida y se envía un `VerifyEmail` al correo nuevo, que permite verificar la cuenta.
- **Resultado actual (pre-corrección):** `email_verified_at` pasaba a `null`, pero no se enviaba ninguna notificación; el usuario solo podía recuperar el acceso tocando “Reenviar correo de verificación”.
- **Evidencia:** el test `changing the email invalidates verification and sends a fresh notice` fallaba con `The expected [Illuminate\Auth\Notifications\VerifyEmail] notification was not sent.`; tras el fix, el test `the new email can be verified after a profile change` dispara `Verified` y la cuenta queda verificada.
- **Causa raíz probable:** el controlador limpiaba la marca de verificación pero no encolaba el aviso, a diferencia del registro de usuarios.
- **Impacto:** el usuario pierde el acceso a `/settings/security` y a `/settings/appearance` sin recibir instrucciones por correo; la recuperación depende de que descubra el botón de reenvío.
- **Workaround:** abrir `/email/verify` y usar “Reenviar correo de verificación”.
- **Recomendación de corrección:** enviar la notificación apenas se persista el correo nuevo, como se implementó.
- **Test que lo reproduce:** `tests/QA/MailAndVerificationTest.php` → `changing the email invalidates verification and sends a fresh notice` y `the new email can be verified after a profile change`.
- **Esfuerzo estimado:** S

<a id="bug-027"></a>

### [BUG-027] El reset de contraseña revela si un correo está registrado
- **Severidad:** S3
- **Prioridad:** P2
- **Frecuencia:** Constante: cualquier pedido de reset con un correo no registrado
- **Tipo:** Seguridad funcional / Fuga de información
- **Módulo / Capa:** Backend / `app/Providers/FortifyServiceProvider.php` y `app/Http/Responses/FailedPasswordResetLinkRequestResponse.php`
- **Estado de verificación:** CONFIRMADO por test fallido → **CORREGIDO y verificado** (ronda 11)
- **Corrección aplicada:** respuesta propia de Fortify que, para `Password::INVALID_USER`, devuelve el mismo estado genérico que un envío correcto, sin distinción en HTML ni en JSON.
- **Ubicación original:** `Laravel\Fortify\Http\Responses\FailedPasswordResetLinkRequestResponse`, enlazado por defecto en `FortifyServiceProvider`
- **Entorno:** PHP 8.3.36, Laravel 13.44.1, SQLite `:memory:`, `Notification::fake()`
- **Precondiciones:** ninguna; basta con un correo ausente en la base
- **Pasos para reproducir:**
  1. Hacer `POST /forgot-password` con un correo registrado y guardar el `status` de sesión.
  2. Repetir con un correo inexistente y comparar el resultado.
  3. Cargar `/forgot-password` y revisar el prop `status` de la página Inertia.
- **Resultado esperado:** el mismo mensaje en ambos casos y ninguna notificación para el correo ausente.
- **Resultado actual (pre-corrección):** el correo inexistente devolvía el error “We can't find a user with that email address.”; el existente devolvía `status`, permitiendo enumerar cuentas.
- **Evidencia:** el test `a password reset request does not disclose whether the email exists` fallaba con `Session is missing expected key [status]` y el mensaje de error; tras el fix ambos pedidos comparten `status` y la vista `auth/ForgotPassword` recibe un `status` no nulo.
- **Causa raíz probable:** Fortify traduce `Password::INVALID_USER` a un error de validación visible, sin una respuesta que oculte la distinción.
- **Impacto:** cualquiera puede comprobar qué correos están registrados, facilitando phishing dirigido y relleno de credenciales.
- **Workaround:** ninguno desde la configuración de la aplicación; solo cambiar el texto traducido no bastaría porque el canal (error vs. estado) también diferencia.
- **Recomendación de corrección:** enlazar una respuesta que trate `INVALID_USER` como éxito aparente, conservando el comportamiento de Fortify para el resto de estados (por ejemplo, throttle).
- **Test que lo reproduce:** `tests/QA/MailAndVerificationTest.php` → `a password reset request does not disclose whether the email exists`.
- **Esfuerzo estimado:** S
