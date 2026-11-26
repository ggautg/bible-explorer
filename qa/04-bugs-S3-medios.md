# 04 — Bugs S3 (Medios / Moderados)

## Índice
- [Estado](#estado)
- [BUG-004 — El slug no se actualiza al cambiar el nombre](#bug-004)
- [BUG-009 — Verificación por correo activa pero no exigida](#bug-009)
- [BUG-014 — Búsqueda de texto con 500 bajo SQLite](#bug-014)

## Estado
No hay defectos S3 abiertos. BUG-004 (Fase 2, perfil) y BUG-009 (Fase 6, settings) fueron corregidos en la ronda de corrección 1; BUG-014 (ronda 3) se corrigió con un fallback según el driver. Todos fueron verificados con la suite QA en verde y, en el caso de BUG-014, también en navegador.

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
