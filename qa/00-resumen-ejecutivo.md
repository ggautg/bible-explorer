# 00 — Resumen ejecutivo

## Índice
- [Alcance](#alcance)
- [Qué se probó y qué no](#qué-se-probó-y-qué-no)
- [Veredicto de liberación](#veredicto-de-liberación)
- [Bugs por severidad](#bugs-por-severidad)
- [Hallazgos ordenados por prioridad](#hallazgos-ordenados-por-prioridad)
- [Métricas de pruebas](#métricas-de-pruebas)
- [Ronda de corrección 1 — backend](#ronda-de-corrección-1--backend)
- [Ronda de corrección 2 — frontend y build](#ronda-de-corrección-2--frontend-y-build)
- [Ronda 3 — verificación en navegador](#ronda-3--verificación-en-navegador)
- [Ronda 4 — rendimiento en navegador](#ronda-4--rendimiento-en-navegador)
- [Ronda 5 — carga con volumen](#ronda-5--carga-con-volumen)
- [Ronda 6 — seguridad funcional](#ronda-6--seguridad-funcional)
- [Cierre del ciclo](#cierre-del-ciclo)

## Alcance
Auditoría QA funcional, de integración, regresión, seguridad funcional, rendimiento básico y accesibilidad de un proyecto Laravel 13 + Inertia/Vue 3 + MySQL. Se trabajó **solo sobre SQLite en memoria**, sin acceso a producción ni a la BD de desarrollo.

## Qué se probó y qué no
**Probado (Fase 1):**
- Instalación desde lockfiles (`composer install`, `npm ci`).
- `migrate:fresh --seed` sobre SQLite `:memory:` y ciclo completo `migrate` → `migrate:reset` → `migrate` sobre un archivo SQLite temporal.
- Suite existente (54 tests) y suite QA (2 tests de entorno).
- `npm run lint:check`, `format:check`, `types:check`, `build`, `composer validate`, `composer audit`.
- Revisión del tail de `storage/logs/laravel.log`.

**Probado (Fase 2 — autenticación, seguridad y throttles):**
- Registro (incluido Unicode/ñ y correo con distinta capitalización), slug de perfil, actualización de perfil, borrado de cuenta con cascada.
- Throttle de login (5 intentos) y del challenge 2FA (5 códigos).
- Flujo completo de 2FA: activar, resolver challenge con TOTP válido, desactivar.
- 11 tests nuevos en `tests/QA/AuthSecurityTest.php`: 8 pasan y 3 fallan (dos causas raíz).

**Probado (Fase 3 — social):**
- Feed (pestañas `para_ti` y `siguiendo`), publicaciones, respuestas, me gusta, rebibleos, seguimiento, privacidad de la pestaña “me gusta” (test heredado) y acceso de invitados.
- Límites de cuerpo (solo espacios, 2000 caracteres, Unicode/emoji/<script>), auto-seguimiento, auto-rebibleo, idempotencia del rebibleo y visualización de respuestas anidadas.
- 11 tests nuevos en `tests/QA/SocialFeedTest.php`: 10 pasan y 1 falla (BUG-005, S4).

**Probado (Fase 4 — notas, favoritos y compartir):**
- APIs `/api/notes` y `/api/favorites`: aislamiento por usuario, upsert/creación idempotente, borrado selectivo, validación de tipos y de referencia, cuerpo en blanco, throttle de 30/min y respuesta 401 para invitados.
- Página pública `/compartido/{libro}/{capítulo}/{versículo}`: texto y nombre derivados del servidor, acceso sin sesión y 404 para referencias inexistentes.
- 14 tests nuevos en `tests/QA/BibleNotesFavoritesTest.php`: 12 pasan y 2 fallan (misma causa raíz, BUG-006 S4).

**Probado (Fase 5 — lectura, búsqueda y grafo bíblico):**
- Listado de libros en orden canónico, páginas de capítulos y versículos, traducción alterna, navegación entre capítulos y respuestas 404 para referencias inexistentes.
- APIs `/api/bible/books-search`, `/api/bible/structure` y `/api/bible/resolve`: autocompletado con límite de 8, validación, nombres acentuados, formatos malos (422) y libros/versículos ausentes (404).
- Grafo: centro, nodos, aristas entrantes/salientes, dedupe, límite de 25 por dirección, nodo central sin conexiones, 404 y consistencia entre aristas y nodos.
- Búsqueda de texto: el caso con `q < 3` se ejecutó desde el inicio; `q >= 3` se cubrió en la ronda 3 con un fallback `LIKE` escapado para SQLite. `MATCH ... AGAINST` sobre MySQL real sigue **NO VERIFICADO**.
- 20 tests nuevos en `tests/QA/BibleReadingSearchTest.php`: 17 pasan, 2 fallan (BUG-007 y BUG-008) y 1 se omite explícitamente; el omitido se reactivó en la ronda 3.

**Probado (Fase 6 — settings, frontend estático e integridad):**
- Rutas de settings: acceso de invitados, redirect `/settings`, componentes Inertia de perfil y apariencia, middleware `verified`, throttle de 6 intentos de contraseña y well-known de passkeys.
- Persistencia de tema: precedencia de la cookie `theme` sobre `appearance` y fallback a `getsemani` en `HandleAppearance`.
- Metadatos de passkeys expuestos al frontend (nombre, authenticator derivado del AAGUID y fechas relativas), sin emular WebAuthn.
- Integridad: índices entrante y único de cross-references, índice único de rebibleos, rechazo de duplicados y cascada de passkeys al borrar usuario.
- Revisión estática de accesibilidad sobre componentes propios Vue; resultados en `09-lo-que-funciona-bien.md` y `11-anexos.md`.
- 14 tests nuevos en `tests/QA/SettingsFrontendIntegrityTest.php`: 12 pasan y 2 fallan (misma causa raíz, BUG-009 S3).

**No probado:** lector de pantalla dedicado, revisión manual de viewports, búsqueda FULLTEXT sobre MySQL real y carga sostenida bajo servidor web con workers. En la ronda 3 se ejecutó E2E de login/logout en navegador, se revisó la consola y se corrieron axe/Lighthouse; en la ronda 4 se perfiló el renderer y en la ronda 5 se corrió carga ligera con 15.000 versículos. No se instalaron Playwright, Dusk ni runners JS.

## Veredicto de liberación
**GO condicionado a MySQL y lector de pantalla.** Los 16 hallazgos quedaron corregidos y revalidados: suite QA en verde (85/85, 0 omitidos), suite heredada 54/54 y Lighthouse 1,0 en las once páginas auditadas. La verificación en navegador ya se hizo con teclado, árbol de accesibilidad y axe/Lighthouse; la carga ligera con 15.000 versículos respondió 170/170 con 200. Lo que sigue NO VERIFICADO es FULLTEXT sobre MySQL real, un lector de pantalla dedicado, viewports manuales y carga sostenida con servidor web real.

## Bugs por severidad
| Severidad | Cantidad | Estado |
| --- | ---: | --- |
| S1 Crítico | 0 | Sin hallazgos |
| S2 Alto | 0 | BUG-003 corregido y verificado |
| S3 Medio | 3 | BUG-004, BUG-009 y BUG-014 corregidos y verificados |
| S4 Bajo | 9 | BUG-002, BUG-005 a BUG-008, BUG-010 a BUG-012 y BUG-015 corregidos y verificados |
| S5 Cosmético | 3 | BUG-001, BUG-013 y BUG-016 corregidos y verificados |

## Hallazgos ordenados por prioridad
| # | ID | Título | Severidad | Prioridad | Estado |
| --- | --- | --- | --- | --- | --- |
| 1 | BUG-003 | Los usuarios nuevos no reciben slug y su perfil devuelve 404 | S2 | P1 | CORREGIDO y verificado |
| 2 | BUG-004 | Cambiar el nombre no actualiza el slug del perfil | S3 | P2 | CORREGIDO y verificado |
| 3 | BUG-009 | La verificación por correo está activa pero no se exige: el middleware `verified` nunca aplica | S3 | P2 | CORREGIDO y verificado |
| 4 | BUG-002 | El build falla en una instalación limpia si no existe `.env` con `APP_KEY` | S4 | P4 | CORREGIDO y verificado |
| 5 | BUG-001 | Wayfinder regenera ~70 archivos versionados cambiando CRLF por LF | S5 | P4 | CORREGIDO y verificado |
| 6 | BUG-005 | Una respuesta a una respuesta queda invisible en el hilo | S4 | P3 | CORREGIDO y verificado |
| 7 | BUG-006 | El borrado de notas y favoritos acepta capítulo o versículo 0 | S4 | P3 | CORREGIDO y verificado |
| 8 | BUG-007 | Un `%` en la búsqueda de libros devuelve todos los libros | S4 | P3 | CORREGIDO y verificado |
| 9 | BUG-008 | El grafo genera aristas hacia versículos que no existen | S4 | P3 | CORREGIDO y verificado |
| 10 | BUG-010 | Controles de teclado inaccesibles en contraseñas, selector de versículo y grafo | S4 | P3 | CORREGIDO y verificado (estático) |
| 11 | BUG-011 | Toggles y pestañas no exponen su estado a tecnología asistiva | S4 | P4 | CORREGIDO y verificado (estático) |
| 12 | BUG-012 | Códigos 2FA “ocultos” y mensajes dinámicos no se anuncian | S4 | P3 | CORREGIDO y verificado (estático) |
| 13 | BUG-013 | Botones placeholder y nombres repetidos en listas | S5 | P4 | CORREGIDO y verificado (estático y navegador) |
| 14 | BUG-014 | `/buscar?q=...` responde 500 bajo SQLite por usar FULLTEXT de MySQL | S3 | P2 | CORREGIDO y verificado |
| 15 | BUG-015 | Problemas de axe vistos en navegador: orden de foco, contraste, nombres y landmark | S4 | P3 | CORREGIDO y verificado |
| 16 | BUG-016 | Falta la meta descripción compartida | S5 | P4 | CORREGIDO y verificado |

## Métricas de pruebas
| Métrica | Valor |
| --- | ---: |
| Tests ejecutados | 139 |
| Pasados | 139 |
| Fallidos | 0 |
| Omitidos | 0 |
| Aserciones | 714 |
| Migraciones up / down | 23 / 23 |
| Duración suite principal (54) | 3,2 s |
| Duración suite QA (85) | 3,4 s |
| Build | OK, 2,17 s sin `.env` |
| Lighthouse (11 páginas) | 1,0 en accesibilidad, buenas prácticas y SEO |
| Hallazgos abiertos | 0 |
| Hallazgos corregidos | 16 |

Los 10 fallos originales correspondían a 7 causas raíz (BUG-003 a BUG-009), todas corregidas y verificadas. Los seis hallazgos de frontend/build (BUG-001, BUG-002 y BUG-010 a BUG-013) se cerraron con tests estáticos y luego se confirmaron en navegador. La ronda 3 agregó otros tres hallazgos (BUG-014 a BUG-016) que también quedaron corregidos. Ya no hay tests omitidos: el caso FULLTEXT se ejecuta con fallback en SQLite, mientras que `MATCH ... AGAINST` en MySQL real sigue NO VERIFICADO. La ronda 6 sumó 8 tests preventivos de seguridad sin encontrar defectos nuevos. Los 54 tests heredados siguen pasando: no hay regresiones. Tampoco se usó un lector de pantalla dedicado.

Nota: las 8 coincidencias de error en el tail del log corresponden a fallos de configuración del ciclo anterior (manifest de Vite ausente y `APP_KEY` faltante), ya resueltos. No se generaron nuevos errores durante la línea base.

<a id="ronda-de-corrección-1--backend"></a>

## Ronda de corrección 1 — backend
Con autorización explícita para modificar código de aplicación se corrigieron los siete defectos ejecutados:

| ID | Cambio | Verificación |
| --- | --- | --- |
| BUG-003 | `App\Models\User` genera el slug en el evento `creating` cuando viene vacío | Registro con `María José Ñandú` → `maria-jose-nandu`; `/perfil/{slug}` responde 200 |
| BUG-004 | El evento `updating` regenera el slug cuando cambia el `name`, con sufijo único | `juan-perez` → `ana-maria-nandu` tras editar el perfil |
| BUG-009 | `User` implementa `Illuminate\Contracts\Auth\MustVerifyEmail` | `mustVerifyEmail=true`; las rutas `verified` redirigen y el borrado queda bloqueado |
| BUG-005 | `PostController::show()` recorre todos los descendientes, no solo los hijos directos | La respuesta anidada aparece en `replies.1` |
| BUG-006 | Nuevo `VerseReferenceValidationRules` compartido por escritura y borrado de notas/favoritos | `chapter=0` y `verse=0` responden 422 |
| BUG-007 | `SearchController::books()` escapa `\`, `%` y `_` con `ESCAPE` explícito | `q=%25` devuelve `[]` |
| BUG-008 | `BibleGraphService` filtra aristas cuyos extremos no están entre los nodos | No aparece el extremo `XXX-9-9` |

Revalidación: `php artisan test tests/QA` → 69 pasan, 1 omitido, 391 aserciones; `php artisan test` → 54/54; Pint sin diferencias; PHPStan 0 errores. Los tests QA originales se conservaron activos, sin `skip` ni desactivación.

<a id="ronda-de-corrección-2--frontend-y-build"></a>

## Ronda de corrección 2 — frontend y build
Se cerraron los seis hallazgos restantes y se agregaron 4 tests estáticos reejecutables en `tests/QA/FrontendIntegrityRound2Test.php`.

| ID | Cambio | Verificación |
| --- | --- | --- |
| BUG-010 | `PasswordInput` es tabulable con `aria-pressed`; los dos combobox implementan flechas/Enter/Escape con `aria-activedescendant`; el error del grafo se anuncia y el `<svg>` usa `role=group` para no ocultar los nodos | Test estático + `types:check` |
| BUG-011 | `aria-pressed` en temas, notas, favoritos y toggle 2FA; `aria-expanded` en comparación y menú; `aria-current` en pestañas y navegación | Test estático |
| BUG-012 | Los códigos 2FA se sacan del DOM con `v-if`; errores con `role=alert`, estados con `role=status`, botón de copiar con nombre y etiqueta | Test estático |
| BUG-013 | Botones placeholder deshabilitados; se eliminaron los `href="#"`; cada ítem de lista tiene `aria-label` con referencia o llave | Test estático |
| BUG-002 | El plugin Wayfinder no corre durante `vite build` | `npm run build` sin `.env`: OK en 1,79 s |
| BUG-001 | Al no regenerarse en el build, no se introducen cambios nuevos en los artefactos versionados | Hash estable antes/después del build en archivos representativos de `actions` y `routes` |

Revalidación tras la ronda 2: `php artisan test tests/QA` → 73 pasan, 1 omitido, 429 aserciones; `php artisan test` → 54/54; Pint y PHPStan en verde; `npm run lint:check`, `format:check` y `types:check` en verde. No se agregó ningún runner JS ni se tocó `package.json`. Nota: el árbol de trabajo ya tenía artefactos Wayfinder modificados por la fase de hallazgos; el build actual no los reescribe.

<a id="ronda-3--verificación-en-navegador"></a>

## Ronda 3 — verificación en navegador
Se levantó la app con un archivo SQLite temporal en `C:\Users\Desarrollo\AppData\Local\Temp\opencode\`, sin datos reales y sin tocar `phpunit.xml`. No se agregó ningún runner JS: se usó el navegador integrado, su árbol de accesibilidad, teclado, consola y Lighthouse.

| ID | Hallazgo | Corrección | Verificación |
| --- | --- | --- | --- |
| BUG-010 | El ojo de contraseña quedaba después de todos los controles con `tabindex` positivo en Login/Register | Se eliminaron los `tabindex` positivos; ahora el foco sigue el orden del DOM | Tab real: email → olvidé password → password → ojo → recordarme → enviar; Enter cambia `aria-pressed` y el `type` |
| BUG-014 | `/buscar?q=Dios` respondía 500 en SQLite por `MATCH ... AGAINST` | `TextSearchController` usa `LIKE` escapado cuando el driver no es MySQL; MySQL conserva FULLTEXT | 2 resultados en navegador; test QA antes omitido ahora pasa |
| BUG-015 | axe marcó contraste bajo, nombres que no incluían el texto visible, falta de `<main>` en auth y orden de foco | Se subió el contraste, se ajustaron `aria-label`, se envolvió `AuthLayout` en `<main>` y se ordenó el foco | Lighthouse 1,0 en accesibilidad en las 11 páginas auditadas |
| BUG-016 | No había meta descripción compartida | Se agregó en `resources/views/app.blade.php` | SEO pasó de 0,8 a 1,0 |

Páginas auditadas con Lighthouse 1,0/1,0/1,0 (accesibilidad, buenas prácticas, SEO): `/`, `/login`, `/register`, `/forgot-password`, `/comunidad`, `/leer/GEN/1`, `/buscar?q=Dios`, `/notas`, `/favoritos`, `/settings/appearance` y `/bible/GEN/1/1`. El combobox del compositor y el del grafo se verificaron con flechas, Enter y Escape. No hubo errores de consola propios; el único 422 registrado fue la respuesta esperada de `/api/bible/resolve` ante un formato inválido.

Revalidación tras la ronda 3: `php artisan test tests/QA` → 77/77, 449 aserciones; `php artisan test` → 54/54; Pint, PHPStan, `lint:check`, `format:check`, `types:check` y `npm run build` en verde.

<a id="ronda-4--rendimiento-en-navegador"></a>

## Ronda 4 — rendimiento en navegador
Se perfiló el renderer de Chromium con `trace.start`/`stop`/`analyze` y se leyó `PerformanceNavigationTiming` en siete URLs, sobre el mismo SQLite temporal y con caché falsa. No es una prueba de carga ni entrega Web Vitals: complementa los tiempos de suite con datos reales de carga de página.

| Página | `loadEventEnd` | Tareas largas | Bloqueo por tareas largas |
| --- | ---: | ---: | ---: |
| `/` (invitado) | 344 ms | 0 en el trace (no medido en esa corrida) | — |
| `/login` | 297 ms | 0 | 0 ms |
| `/comunidad` | 363 ms | 1 | 3,2 ms |
| `/leer/GEN/1` | 896 ms | 0 | 0 ms |
| `/bible/GEN/1/1` | 426 ms | 0 | 0 ms |
| `/buscar?q=Dios` | 311 ms | 0 | 0 ms |
| `/notas` | 507 ms | 0 | 0 ms |

El detalle de eventos, transferencias y condiciones está en `08-rendimiento-y-compatibilidad.md` y `11-anexos.md`. No se detectaron tareas largas significativas (la mayor fue 3,2 ms de bloqueo); `/leer/GEN/1` mostró un valor atípico de 896 ms en una medición única, sin promediado.

<a id="ronda-5--carga-con-volumen"></a>

## Ronda 5 — carga con volumen
Se sembraron 15.000 versículos sintéticos en un SQLite temporal separado y se levantó un segundo `artisan serve`. Sin instalar `ab`, `wrk` ni `k6`: se usaron scripts PHP temporales con `curl_multi` y una sesión autenticada.

| Escenario | Peticiones | Respuestas 200 | Errores | Promedio | p95 |
| --- | ---: | ---: | ---: | ---: | ---: |
| Ligero (5 concurrentes × 10 por ruta, 5 rutas) | 50 | 50 | 0 | 660–772 ms | 1114–1276 ms |
| Pico corto (20 concurrentes × 40 por ruta, 3 rutas) | 120 | 120 | 0 | 2232–2389 ms | 4149–4375 ms |

Sin 500 ni timeouts. El aumento de latencia con 20 concurrentes se explica porque el servidor integrado de PHP en Windows atiende una petición a la vez: es encolamiento, no degradación de la aplicación. Estos números no son un SLA ni un techo de capacidad. Sigue NO VERIFICADO el comportamiento con FPM/Apache, MySQL FULLTEXT sobre corpus grande y carga sostenida. El veredicto pasa a **GO condicionado a MySQL y lector de pantalla**.

<a id="ronda-6--seguridad-funcional"></a>

## Ronda 6 — seguridad funcional
Se cerró el hueco documentado en `11-anexos.md` bajo “Riesgos no cubiertos”: no había tests QA de mass assignment ni de render seguro de HTML. Se agregaron 8 tests en `tests/QA/SecurityFunctionalTest.php`. **No apareció ningún defecto nuevo**: los controles existentes ya son correctos.

| Escenario | Esperado | Resultado |
| --- | --- | --- |
| `PATCH /settings/profile` con `slug`, `church`, `email_verified_at`, `is_admin` y `role` | Solo se aplican `name` y `email`; el slug se deriva del nombre | El slug pasó a `nombre-legitimo`, no al manipulado; sin columnas `is_admin`/`role`; verificación intacta |
| `POST /posts` con `user_id`, `parent_id`, `rebible_of_id` y `book_name` ajenos | El servidor impone identidad, relaciones nulas y nombre derivado | Igual |
| `POST /posts` con `kind=rebibleada` | 422: es un tipo automático, no elegible | Igual |
| `PUT /api/notes` y `POST /api/favorites` con `user_id` de otro usuario | La fila queda asociada al autenticado | Igual |
| Upsert de nota con la misma referencia desde dos usuarios | Dos filas, cada una con su cuerpo | Igual |
| Cuerpo con `<script>` en nota | Viaja como texto plano en la API y no se renderiza como HTML | JSON con el string literal; sin `v-html`/`innerHTML` en `resources/js` |

Nota: `user_id` sigue siendo *fillable* en `Post`, `Note` y `Favorite` (riesgo latente). Los tests bloquean la regresión: si algún día se cambia a `create($request->all())` o `fill($request->all())`, fallan.

<a id="cierre-del-ciclo"></a>

## Cierre del ciclo
Checklist final de consistencia de los 12 informes:

| Ítem | Resultado |
| --- | --- |
| Informes en `qa/` | 12/12 presentes (`00` a `11`) |
| Enlaces internos del índice | 0 rotos: todos los enlaces resuelven contra anclas explícitas o encabezados |
| Hallazgos documentados | 16/16 (`BUG-001` a `BUG-016`), ninguno abierto |
| Casos de prueba | CP-001 a CP-132, con suites QA y heredada en verde |
| Tests QA / heredados | 85/85 (488 aserciones) y 54/54 (226 aserciones), 0 omitidos |
| Herramientas | Pint, PHPStan, `lint:check`, `format:check`, `types:check` y `npm run build` en verde |
| Evidencia de navegador | Lighthouse 1,0/1,0/1,0 en 11 páginas; perfilado de renderer y 170 peticiones de carga |
| Veredicto | **GO condicionado a MySQL y lector de pantalla** |

Pendiente externo, fuera de este entorno: `MATCH ... AGAINST` sobre MySQL aislado, lector de pantalla dedicado (NVDA/VoiceOver), revisión manual de viewports y carga con servidor web real. No se hizo commit, push ni PR, y los borrados preexistentes declarados intencionales siguen intactos.
