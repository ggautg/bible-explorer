# 07 — Casos de prueba ejecutados

## Índice
- [Casos de Fase 1](#casos-de-fase-1)
- [Casos de Fase 2 — autenticación, seguridad y throttles](#casos-de-fase-2--autenticación-seguridad-y-throttles)
- [Casos de Fase 3 — social](#casos-de-fase-3--social)
- [Casos de Fase 4 — notas, favoritos y compartir](#casos-de-fase-4--notas-favoritos-y-compartir)
- [Casos de Fase 5 — lectura, búsqueda y grafo](#casos-de-fase-5--lectura-búsqueda-y-grafo)
- [Casos de Fase 6 — settings, frontend estático e integridad](#casos-de-fase-6--settings-frontend-estático-e-integridad)
- [Suite heredada](#suite-heredada)
- [Verificación de correcciones (ronda 1)](#verificación-de-correcciones-ronda-1)
- [Verificación de correcciones (ronda 2)](#verificación-de-correcciones-ronda-2)
- [Verificación en navegador, rendimiento, seguridad, API externa, sesiones, estado del cliente, responsive, correos, integridad de BD, presupuestos de consultas, contrato de rutas, límites/validaciones, paths negativos y fronteras finales (rondas 3 a 17)](#verificación-en-navegador-ronda-3)
- [Casos no ejecutados](#casos-no-ejecutados)

<a id="casos-de-fase-1"></a>

## Casos de Fase 1
| ID | Caso | Datos / entorno | Esperado | Real | Estado |
| --- | --- | --- | --- | --- | --- |
| CP-001 | Instalar dependencias desde lockfiles | `composer install`, `npm ci` | Sin cambios en lockfiles | 177 paquetes PHP y 453 Node; lockfiles intactos | Pasó |
| CP-002 | Levantar BD de testing desde cero | SQLite `:memory:`, `migrate:fresh --seed` | 23 migraciones y seeder sin errores | 23 DONE + seeding OK | Pasó |
| CP-003 | Migraciones hacia arriba | SQLite archivo temporal | 23 aplicadas | 23 aplicadas | Pasó |
| CP-004 | Migraciones hacia abajo | `migrate:reset` sobre el mismo archivo | 0 aplicadas, salida 0 | 0 aplicadas, exit 0 | Pasó |
| CP-005 | Re-aplicar migraciones tras rollback | Mismo archivo SQLite | 23 aplicadas | 23 aplicadas | Pasó |
| CP-006 | Suite existente | `php artisan test --compact` | 54/54 | 54/54, 226 aserciones, 2,8 s | Pasó |
| CP-007 | Suite QA de entorno | `php artisan test tests/QA --compact` | 2/2 | 2/2, 7 aserciones, 0,24 s | Pasó |
| CP-008 | Estilo PHP | `./vendor/bin/pint --dirty --test` | Sin diferencias | Sin diferencias | Pasó |
| CP-009 | Análisis estático PHP | `phpstan --memory-limit=1G` | 0 errores | 0 errores | Pasó |
| CP-010 | Lint y formato JS/Vue | `lint:check`, `format:check` | Sin hallazgos | Sin hallazgos | Pasó |
| CP-011 | Tipos Vue | `npm run types:check` | Sin errores | Sin errores | Pasó |
| CP-012 | Build de producción | `npm run build` | Manifest generado | 77 assets, 0,92 MB, 2,61 s | Pasó (con BUG-001) |
| CP-013 | Validar composer | `composer validate --no-check-publish` | Válido | Válido | Pasó |
| CP-014 | Auditoría de dependencias | `composer audit` | 0 avisos | 0 avisos | Pasó |
| CP-015 | Revisión de logs | Últimas 200 líneas de `laravel.log` | Sin errores nuevos | 8 entradas antiguas de setup; ninguna nueva | Pasó |
| CP-016 | Build sin `.env` | Checkout limpio, sin `.env` | Build OK | `MissingAppKeyException` | **Falló** → BUG-002 |

<a id="casos-de-fase-2--autenticación-seguridad-y-throttles"></a>

## Casos de Fase 2 — autenticación, seguridad y throttles
| ID | Caso | Datos / entorno | Esperado | Real | Estado |
| --- | --- | --- | --- | --- | --- |
| CP-017 | Registro con nombre Unicode genera slug | `María José Ñandú`, `maria@example.com` | `maria-jose-nandu` | `slug = NULL` | **Falló** → BUG-003 |
| CP-018 | Enlace de perfil tras registro | `GET /perfil/` con el slug devuelto | 200 | 404 | **Falló** → BUG-003 |
| CP-019 | Cambiar nombre regenera slug | `juan-perez` → `Ana María Ñandú` | `ana-maria-nandu` | `juan-perez` | **Falló** → BUG-004 |
| CP-020 | Correo duplicado con distinta capitalización | `unico@example.com` vs `UNICO@example.com` | Error de validación `email` | Error de validación; 1 solo usuario | Pasó |
| CP-021 | Throttle de login | 5 intentos fallidos + 1 correcto | 6º responde 429 | 429 | Pasó |
| CP-022 | Challenge 2FA con TOTP válido | Secreto real generado con Google2FA | Redirect a `/comunidad` y sesión autenticada | Igual | Pasó |
| CP-023 | Throttle del challenge 2FA | 5 códigos `000000` + 1 extra | 6º responde 429 | 429 | Pasó |
| CP-024 | Activar y desactivar 2FA | Usuario autenticado con password confirmado | `two_factor_secret` se crea y se borra | Igual | Pasó |
| CP-025 | Borrado de cuenta con contenido | Usuario con post, nota y favorito | Usuario y dependencias eliminados por cascada | Igual | Pasó |
| CP-026 | Regresión de la suite heredada | `php artisan test --compact` tras los tests QA | 54/54 | 54/54, 226 aserciones | Pasó |
| CP-027 | Estilo del nuevo archivo QA | `./vendor/bin/pint --dirty --test` | Sin diferencias | Sin diferencias | Pasó |

<a id="casos-de-fase-3--social"></a>

## Casos de Fase 3 — social
| ID | Caso | Datos / entorno | Esperado | Real | Estado |
| --- | --- | --- | --- | --- | --- |
| CP-028 | Publicación con solo espacios/saltos | `"   \n\t  "` | Error de validación `body`, 0 posts | Igual | Pasó |
| CP-029 | Respuesta con solo espacios | `"    "` | Error de validación `body`, 0 respuestas | Igual | Pasó |
| CP-030 | Cuerpo de exactamente 2000 caracteres Unicode | `str_repeat('á', 2000)` | Se crea el post | Igual | Pasó |
| CP-031 | Auto-seguimiento | Usuario se sigue a sí mismo | 0 filas en `follows` | Igual | Pasó |
| CP-032 | Auto-rebibleo | Usuario rebiblea su propio post | 0 rebibleos creados | Igual | Pasó |
| CP-033 | Alternar rebibleo | Usuario rebiblea dos veces | 1 y luego 0 rebibleos | Igual | Pasó |
| CP-034 | Feed solo con posts raíz | Raíz + respuesta | Solo la raíz en `posts` | Igual | Pasó |
| CP-035 | Pestaña `siguiendo` | Propios + seguidos + extraño | Solo propios y seguidos | Igual | Pasó |
| CP-036 | Acceso de invitados | GET feed, POST post, POST like | Redirect a login en los tres | Igual | Pasó |
| CP-037 | Unicode, emoji y `<script>` en el cuerpo | `Gracias, Jesús 🙏 — <script>…</script> ñandú` | Se guarda verbatim | Igual | Pasó |
| CP-038 | Respuesta a una respuesta visible en el hilo | Raíz → respuesta → anidada | Anidada en `replies.1` | `replies.1` no existe | **Falló** → BUG-005 |

<a id="casos-de-fase-4--notas-favoritos-y-compartir"></a>

## Casos de Fase 4 — notas, favoritos y compartir
| ID | Caso | Datos / entorno | Esperado | Real | Estado |
| --- | --- | --- | --- | --- | --- |
| CP-039 | Notas aisladas por usuario | Usuario A crea nota; usuario B consulta y borra | B ve 0 notas y no afecta la de A | Igual | Pasó |
| CP-040 | Favoritos aislados por usuario | Usuario A crea favorito; usuario B consulta y borra | B ve 0 favoritos y no afecta el de A | Igual | Pasó |
| CP-041 | Upsert de nota | Dos `PUT /api/notes` para el mismo versículo | 1 fila con el texto de la segunda versión | Igual | Pasó |
| CP-042 | Crear favorito dos veces | Dos `POST /api/favorites` iguales | 1 fila | Igual | Pasó |
| CP-043 | Nota con cuerpo en blanco | `"   \n"` | 422 con error en `body` | Igual | Pasó |
| CP-044 | Nota para versículo inexistente | `GEN 99:1` | 422, sin nota creada | Igual | Pasó |
| CP-045 | Tipos inválidos en nota | `book_code` array y `chapter` texto | 422 con errores en ambos campos | Igual | Pasó |
| CP-046 | Borrar nota con capítulo 0 | `DELETE /api/notes` con `chapter=0` | 422 con error en `chapter` | 200 `{"ok": true}` | **Falló** → BUG-006 |
| CP-047 | Borrar favorito con versículo 0 | `DELETE /api/favorites` con `verse=0` | 422 con error en `verse` | 200 `{"ok": true}` | **Falló** → BUG-006 |
| CP-048 | Throttle de escritura de notas | 31 `PUT /api/notes` seguidos | El 31º responde 429 | 429 | Pasó |
| CP-049 | Invitados en APIs bíblicas | GET/POST/PUT/DELETE sin sesión | 401 en los cuatro | Igual | Pasó |
| CP-050 | Página compartida pública | `GET /compartido/GEN/1/1` sin sesión | Inertia `Bible/Shared` con texto y nombre del servidor | Igual | Pasó |
| CP-051 | Página compartida con referencia inexistente | `GEN 99:99` y `XXX 1:1` | 404 en ambos | Igual | Pasó |
| CP-052 | Borrado selectivo de notas | Dos notas en `GEN 1:1` y `GEN 1:2`; borrar la primera | Queda solo `GEN 1:2` | Igual | Pasó |

<a id="casos-de-fase-5--lectura-búsqueda-y-grafo"></a>

## Casos de Fase 5 — lectura, búsqueda y grafo
| ID | Caso | Datos / entorno | Esperado | Real | Estado |
| --- | --- | --- | --- | --- | --- |
| CP-053 | Listado de libros en orden canónico | GEN, EXO, JHN con `order` 1, 2, 3 | Inertia `Bible/Books` en ese orden | Igual | Pasó |
| CP-054 | Capítulos de libro inexistente | `GET /bible/XXX/1` | 404 | 404 | Pasó |
| CP-055 | Capítulo sin versículos | `GET /bible/GEN/99` | 404 | 404 | Pasó |
| CP-056 | Versículo con traducción alterna | `GEN 1:1` y `GEN 1:2` en RVA + PDT | `alternateVerses.1` con el texto PDT | Igual | Pasó |
| CP-057 | Navegación entre capítulos | `GEN 1` y `GEN 2` presentes | `hasNext`/`hasPrev` correctos | Igual | Pasó |
| CP-058 | Último capítulo sin “siguiente” | `GET /bible/GEN/2` | `hasNext=false`, `hasPrev=true` | Igual | Pasó |
| CP-059 | Autocompletado con `q` vacío | `q=%20%20` | `[]` | `[]` | Pasó |
| CP-060 | Autocompletado parcial y límite | 9 libros “Prueba N”, `q=Prueba` | 8 resultados, PR1…PR8 | Igual | Pasó |
| CP-061 | `%` literal en autocompletado | `q=%25` | `[]` | GEN, EXO y JHN | **Falló** → BUG-007 |
| CP-062 | Validación de `structure` | `chapter=0` y `book` de 20 caracteres | 422 en ambos campos | Igual | Pasó |
| CP-063 | Resolver referencia acentuada | `Génesis 1:2` | `{book: GEN, chapter: 1, verse: 2}` | Igual | Pasó |
| CP-064 | Referencia malformada | `Genesis 1` | 422 con mensaje de formato | Igual | Pasó |
| CP-065 | Libro o versículo inexistente al resolver | `Marcos 1:1`, `Génesis 99:1` | 404 en ambos | Igual | Pasó |
| CP-066 | Grafo con aristas entrantes y salientes | GEN→JHN y EXO→GEN | Centro `GEN-1-1`, nodo central, dos aristas | Igual | Pasó |
| CP-067 | Grafo sin referencias | `JHN 3:16` | Solo nodo central, `edges=[]` | Igual | Pasó |
| CP-068 | Grafo con centro inexistente | `GET /bible/GEN/99/99` | 404 | 404 | Pasó |
| CP-069 | Límite de conexiones del grafo | 30 referencias salientes | 25 aristas ordenadas por `score` | Igual | Pasó |
| CP-070 | Aristas con nodo existente | Referencia hacia `XXX 9:9` sin versículo | Sin extremos huérfanos | `XXX-9-9` sin nodo | **Falló** → BUG-008 |
| CP-071 | Búsqueda de texto con `q < 3` | `q=ab` | Inertia `Bible/Search`, `results=[]` | Igual | Pasó |
| CP-072 | Búsqueda de texto con `q >= 3` | `q=Dios`, SQLite `:memory:` | Resultados con el término | 500 en el navegador; el test estaba omitido por FULLTEXT | **Falló** → BUG-014 (reactivado en ronda 3) |

<a id="casos-de-fase-6--settings-frontend-estático-e-integridad"></a>

## Casos de Fase 6 — settings, frontend estático e integridad
| ID | Caso | Datos / entorno | Esperado | Real | Estado |
| --- | --- | --- | --- | --- | --- |
| CP-073 | Invitados redirigidos desde settings | GET profile, appearance y security sin sesión | Redirect a login en los tres | Igual | Pasó |
| CP-074 | Redirect de `/settings` | Usuario autenticado | Redirect a `route('profile.edit')` | Igual | Pasó |
| CP-075 | Props Inertia de perfil y apariencia | Usuario con correo sin verificar | `settings/Profile` con `mustVerifyEmail=true`; `settings/Appearance` | `mustVerifyEmail=false` | **Falló** → BUG-009 |
| CP-076 | Usuario sin verificar en rutas `verified` y borrado | `email_verified_at=NULL`, password correcta | Redirect a `verification.notice`; cuenta intacta | 200 en ambas; cuenta borrada | **Falló** → BUG-009 |
| CP-077 | Correo ajeno en actualización de perfil | `otra@example.com` ya existente | Error de validación en `email`; correo propio intacto | Igual | Pasó |
| CP-078 | Throttle de cambio de contraseña | 6 intentos con password actual incorrecta + 1 correcto | El 7º responde 429 y la contraseña no cambia | Igual | Pasó |
| CP-079 | Contraseña nueva débil o sin confirmar | `abc` / confirmación distinta | 422 con error en `password`; contraseña intacta | Igual | Pasó |
| CP-080 | Endpoint público de passkeys | `GET /.well-known/passkey-endpoints` | JSON con `enroll` y `manage` hacia `security.edit` | Igual | Pasó |
| CP-081 | Authenticator expuesto en props de seguridad | Passkey con AAGUID de Windows Hello | `passkeys.0.authenticator = 'Windows Hello'` | Igual | Pasó |
| CP-082 | Precedencia de cookie de tema | `theme=olivo` + `appearance=patmos`, solo `appearance`, sin cookies | `olivo`, `patmos`, `getsemani` | Igual | Pasó |
| CP-083 | Índices de integridad presentes | `getIndexes()` sobre cross-references y posts | Índice entrante, único de referencia y único de rebibleos | Iguales | Pasó |
| CP-084 | Referencia cruzada duplicada | Insertar dos veces la misma fila | `QueryException`; queda 1 fila | Igual | Pasó |
| CP-085 | Rebibleo duplicado del mismo post | Crear dos veces el mismo `rebible_of_id` | `QueryException`; quedan 2 filas | Igual | Pasó |
| CP-086 | Cascada de passkeys al borrar usuario | Usuario con passkey, `$user->delete()` | 0 passkeys huérfanas | Igual | Pasó |
| CP-087 | Controles alcanzables por teclado (estático) | `PasswordInput`, `VerseReferencePicker`, `Graph` | Ojo de contraseña tabulable; combobox con flechas/Enter/Escape; foco visible en nodos | `tabindex="-1"`, sin navegación por teclado, sin foco en `<g>` | **Falló** → BUG-010 (CONFIRMADO estático) |
| CP-088 | Estado de toggles y pestañas (estático) | `AppearanceTabs`, `Chapter`, `Feed`, `Show`, `SettingsLayout`, `NavHeader` | `aria-pressed`/`aria-expanded`/`aria-current` en el control activo | Solo color/borde; sin atributos de estado | **Falló** → BUG-011 (CONFIRMADO estático) |
| CP-089 | Contenido sensible y mensajes dinámicos (estático) | `TwoFactorRecoveryCodes`, `TwoFactorSetupModal`, `InputError`, mensajes de estado | Códigos fuera del árbol accesible; errores con `role=alert` y `aria-describedby`; botón de copiar con nombre | `h-0 opacity-0` sin `hidden`, `<p>` sin rol, icono sin etiqueta | **Falló** → BUG-012 (CONFIRMADO estático) |
| CP-090 | Placeholders y nombres ambiguos (estático) | `Welcome`, `Feed`, `PostCard`, `Book`, `Search`, `Favorites`, `Notes`, `PasskeyItem` | Controles no disponibles deshabilitados; nombres únicos por ítem | Botones sin handler, `href="#"` y nombres repetidos | **Falló** → BUG-013 (CONFIRMADO estático) |
| CP-091 | Regresión de la suite heredada | `php artisan test --compact` tras Fase 6 | 54/54 | 54/54, 226 aserciones, 2,6 s | Pasó |
| CP-092 | Estilo del nuevo archivo QA | `./vendor/bin/pint --dirty --test` | Sin diferencias | Sin diferencias (tras aplicar `new_with_parentheses`) | Pasó |

<a id="suite-heredada"></a>

## Suite heredada
Los 54 tests existentes se ejecutaron en bloque y pasaron. Distribución: autenticación/verificación/reset 25, settings 10, social 8, integridad bíblica 6, dashboard 2, seeder 1, ejemplos 2. Detalle caso por caso en `php artisan test --compact` (salida registrada en `11-anexos.md`).

<a id="verificación-de-correcciones-ronda-1"></a>

## Verificación de correcciones (ronda 1)
Los mismos tests que fallaron se conservaron activos y se rerun tras la corrección. Los casos de Fase 2 a 6 que figuran como “Falló” son el registro histórico del hallazgo; el estado actual está acá.

| ID | Caso | Esperado | Real | Estado |
| --- | --- | --- | --- | --- |
| CP-093 | Slug generado al registrar `María José Ñandú` | `maria-jose-nandu` | Igual | Pasó |
| CP-094 | Perfil por slug tras registro | `GET /perfil/maria-jose-nandu` → 200 | 200 | Pasó |
| CP-095 | Slug al cambiar el nombre | `ana-maria-nandu` | Igual | Pasó |
| CP-096 | Verificación por correo exigida | `mustVerifyEmail=true`, rutas `verified` redirigen y borrado bloqueado | Igual | Pasó |
| CP-097 | Respuesta anidada visible en el hilo | `replies.1.id` presente | Presente | Pasó |
| CP-098 | Borrado de nota/favorito con 0 | 422 en `chapter` y `verse` | Igual | Pasó |
| CP-099 | `%` literal en autocompletado | `q=%25` → `[]` | `[]` | Pasó |
| CP-100 | Grafo sin aristas huérfanas | Sin `XXX-9-9` | Sin ese extremo | Pasó |
| CP-101 | Regresión y estático tras correcciones | 54/54, Pint sin diferencias, PHPStan 0 errores | Igual | Pasó |

Suite QA tras la ronda 1: 70 tests, 69 pasan, 1 omitido (FULLTEXT), 391 aserciones, 3,1 s.

<a id="verificación-de-correcciones-ronda-2"></a>

## Verificación de correcciones (ronda 2)
Los hallazgos estáticos CP-087 a CP-090 se cubren ahora con `tests/QA/FrontendIntegrityRound2Test.php` y se confirmaron con teclado y axe/Lighthouse en la ronda 3 (CP-108 a CP-120).

| ID | Caso | Esperado | Real | Estado |
| --- | --- | --- | --- | --- |
| CP-102 | Teclado en password, combobox y grafo | `aria-pressed`, flechas/Enter/Escape en ambos combobox, error anunciado, foco visible en nodos | Marcadores presentes en el código | Pasó (estático) |
| CP-103 | Estado de toggles y pestañas | `aria-pressed`, `aria-expanded` y `aria-current` | Marcadores presentes en los componentes | Pasó (estático) |
| CP-104 | Contenido 2FA y mensajes dinámicos | Códigos con `v-if`, errores `role=alert`, estados `role=status`, botón con nombre | Igual | Pasó (estático) |
| CP-105 | Placeholders y nombres únicos | Botones sin handler deshabilitados, sin `href="#"`, `aria-label` por ítem | Igual | Pasó (estático) |
| CP-106 | Build sin `.env` | Build OK y sin cambios nuevos en artefactos Wayfinder | 1,79 s; hash estable antes/después del build | Pasó |
| CP-107 | Calidad final del frontend y PHP | `lint:check`, `format:check`, `types:check`, Pint, PHPStan | Todos en verde | Pasó |

Suite QA tras la ronda 2: 74 tests, 73 pasan, 1 omitido (FULLTEXT), 429 aserciones, 3,1 s.

<a id="verificación-en-navegador-ronda-3"></a>

## Verificación en navegador, rendimiento, seguridad, API externa, sesiones, estado del cliente, responsive, correos, integridad de BD, presupuestos de consultas, contrato de rutas, límites/validaciones, paths negativos y fronteras finales (rondas 3 a 17)
Se levantó la app con `artisan serve` sobre un SQLite temporal fuera del repo (`C:\Users\Desarrollo\AppData\Local\Temp\opencode\`), sin Vite dev ni MySQL. Se usaron el navegador integrado, su árbol de accesibilidad, teclado y Lighthouse; no se agregó ningún runner JS.

| ID | Caso | Esperado | Real | Estado |
| --- | --- | --- | --- | --- |
| CP-108 | Orden de foco en `/login` desde el correo | Correo → “¿Olvidaste tu contraseña?” → password → ojo → recordarme → enviar | Secuencia idéntica medida con Tab real | Pasó (BUG-010) |
| CP-109 | Ojo de contraseña en `/login` | `aria-pressed` pasa de `false` a `true` y el input cambia a `text` | Igual con Enter | Pasó (BUG-010) |
| CP-110 | Error de credenciales | `role=alert` con `aria-live=polite`, `aria-invalid=true` y `aria-describedby=error-email` | Igual | Pasó |
| CP-111 | Login con `test@example.com` / `password` | Redirect a `/comunidad` | Igual | Pasó |
| CP-112 | Combobox del grafo con “Juan” | `aria-expanded`, `role=option`, `aria-activedescendant` con ArrowDown y cierre con Enter | Igual | Pasó (BUG-010) |
| CP-113 | Combobox del grafo con Escape | Lista cerrada (`expanded=false`, 0 opciones) | Igual | Pasó |
| CP-114 | Nodo del grafo al recibir foco | `stroke` blanco y `stroke-width=3`; `<svg role="group">` con etiqueta | Igual | Pasó (BUG-010) |
| CP-115 | Formato inválido en `/api/bible/resolve` | 422 esperado y error anunciado con `role=alert` | Igual; el 422 en consola es la respuesta esperada | Pasó |
| CP-116 | `/buscar?q=Dios` sobre SQLite | 2 resultados, sin 500 ni errores de consola | Igual | Pasó (BUG-014) |
| CP-117 | Lighthouse sobre 11 páginas | 1,0 en accesibilidad, buenas prácticas y SEO, sin fallos | Igual | Pasó (BUG-015, BUG-016) |
| CP-118 | Combobox del compositor (Libro/Capítulo/Versículo) | Flechas/Enter/Escape, `aria-activedescendant` y campos dependientes habilitados | Igual | Pasó |
| CP-119 | Logout desde la navegación | Redirect a `/` | Igual | Pasó |
| CP-120 | Suites y build tras la ronda 3 | 77/77 QA, 54/54 heredados, Pint, PHPStan, lint/format/types y build en verde | Igual | Pasó |
| CP-121 | Tiempos de navegación en Chromium | `loadEventEnd` medido en 7 URLs con caché falsa | 297–896 ms; `responseEnd` 207–784 ms; 8 KB transferidos por página | Pasó (medición única) |
| CP-122 | Trace del renderer en 5 páginas | Sin tareas largas que bloqueen la interacción | 0 en `/login`, lectura, grafo, búsqueda y notas; 1 tarea de 3,2 ms en `/comunidad` | Pasó |
| CP-123 | Carga ligera con 15.000 versículos | 5 concurrentes × 10 peticiones en 5 rutas, sin errores | 50/50 respuestas 200; promedio 660–772 ms, p95 1114–1276 ms | Pasó |
| CP-124 | Pico corto de concurrencia | 20 concurrentes × 40 peticiones en 3 rutas, sin errores | 120/120 respuestas 200; promedio 2232–2389 ms, p95 4149–4375 ms (encolamiento del servidor monoprocreso) | Pasó |
| CP-125 | `PATCH /settings/profile` con campos extra | Solo se aplican `name` y `email`; sin `is_admin`, `role`, `church` ni `email_verified_at` manipulados | Igual; el slug se deriva del nombre, no del payload | Pasó |
| CP-126 | `POST /posts` con `user_id`, `parent_id`, `rebible_of_id` y `book_name` ajenos | Servidor impone autoría, relaciones nulas y nombre derivado | Igual | Pasó |
| CP-127 | `POST /posts` con `kind=rebibleada` | 422, tipo no elegible | Igual | Pasó |
| CP-128 | `PUT /api/notes` y `POST /api/favorites` con `user_id` ajeno | Fila creada para el usuario autenticado | Igual | Pasó |
| CP-129 | Upsert de nota con la misma referencia desde dos usuarios | Dos filas, cada una con su cuerpo | Igual | Pasó |
| CP-130 | Cuerpo de nota con `<script>` | Viaja como texto plano en la API | Igual; `book_name` derivado del servidor | Pasó |
| CP-131 | Búsqueda de `v-html`/`innerHTML` en `resources/js` | Ningún render de HTML crudo | 0 coincidencias | Pasó |
| CP-132 | Suites y build tras la ronda 6 | 85/85 QA y 54/54 heredados sin regresiones | Igual | Pasó |
| CP-133 | 500 esporádico en `bible:seed-text` | Reintenta y guarda el texto extraído | 2 intentos, 1 versículo con las partes textuales | Pasó |
| CP-134 | `ConnectionException` en `bible:seed-text` | Reintenta y guarda el texto | 2 intentos, 1 versículo | Pasó |
| CP-135 | 500 persistente en `bible:seed-text` | Error legible, `FAILURE`, BD vacía | Igual (antes lanzaba `RequestException`) | Pasó (BUG-017) |
| CP-136 | Respuesta 200 sin libros | `FAILURE`, sin versículos | Igual (antes decía “importado correctamente”) | Pasó (BUG-020) |
| CP-137 | 500 persistente en `bible:list-spanish` | `FAILURE` con mensaje de error | Igual | Pasó |
| CP-138 | Caída del índice de crossrefs | `FAILURE` con mensaje, BD vacía | Igual | Pasó (BUG-017, BUG-018) |
| CP-139 | Capítulos de crossrefs con 500 persistente | `FAILURE` con “No se pudieron importar N capítulos” | Igual (antes reportaba éxito) | Pasó (BUG-018) |
| CP-140 | `bible:seed-crossrefs` dos veces | Segunda ejecución idempotente, 1 fila | Igual (antes violaba el índice único) | Pasó (BUG-019) |
| CP-141 | Visita Inertia con XSRF vencido (`curl`) | 302 a `/login`, sin HTML “Page Expired” | Antes 419; igual tras el fix | Pasó (BUG-021) |
| CP-142 | Visita Inertia y API con sesión válida, luego logout | 200 / 200 / 204; invitado va a `/login` y `/api/notes` da 401 JSON | Igual | Pasó |
| CP-143 | Petición JSON con CSRF vencido | 419 JSON, sin redirección para clientes API | Igual | Pasó |
| CP-144 | `POST /posts` tipo Inertia con CSRF vencido, verificado con handler de excepciones | 302 con `Location` a `/login` | Igual (el test falló antes del fix) | Pasó (BUG-021) |
| CP-145 | Sesión invalidada en `/leer/GEN/1` y clic en favorito, en navegador | Las APIs responden 401 y la SPA navega a `/login` | Igual tras el interceptor de axios; `/api/favorites` y `/api/notes` registrados en `performance` | Pasó (BUG-022) |
| CP-146 | Sesión invalidada en `/comunidad` y clic en “Me gusta”, en navegador | Visita Inertia POST con XSRF vencido va al login, sin “Page Expired” | `POST /posts/1/like` respondió 302 y la SPA fue a `/login` | Pasó (BUG-021) |
| CP-147 | Guardar favorito en `/leer/GEN/1` y navegar por SPA a `/favoritos` | El versículo aparece en la lista | Antes: lista vacía y sin `GET /api/favorites`; después: 1 artículo | Pasó (BUG-023) |
| CP-148 | Activar dos veces el botón de favorito antes del primer `await` | Una sola entrada en la lista del cliente | Antes: dos `POST` y dos artículos; después: dos `POST` y un artículo | Pasó (BUG-024) |
| CP-149 | Envíos duplicados de favorito/nota y dos likes/follows secuenciales | Una fila por referencia y estado final igual al inicial | Igual (tests de `ClientStateRaceTest.php`) | Pasó |
| CP-150 | 9 páginas en viewports 375/768/1366/1920 emulados con CDP | Sin desbordamiento horizontal ni elementos fuera del ancho | 36/36 sin `scrollWidth > innerWidth` y 0 elementos fuera | Pasó |
| CP-151 | Menú lateral a 375 px: toggle, enlaces y navegación | `aria-expanded` cambia, 9 enlaces visibles y el menú se cierra al navegar | Igual, sin desbordamiento | Pasó |
| CP-152 | Caída de red al guardar favorito y reintento tras reconectar | Error visible, sin estado pendiente y favorito guardado al reintentar | `net::ERR_INTERNET_DISCONNECTED`; antes: sin mensaje; después: mensaje de error y luego éxito | Pasó (BUG-025) |
| CP-153 | Mensajes de `showToast()` | Visibles en contenedor `aria-live="polite"` | Antes: 0 contenedores y 0 textos; después: mensajes de error y éxito presentes | Pasó (BUG-025) |
| CP-154 | Interceptor de axios ante fallo de transporte | Sin redirección al login si no hay 401/419 | Igual; el botón quedó listo para reintentar | Pasó |
| CP-155 | Guardas estáticas del montaje del toast y del menú móvil en `app.js` / `BiblePageShell.vue` | Contenedor montado y toggle accesible | 4 tests en `ResponsiveFeedbackTest.php` | Pasó |
| CP-156 | Registro con notificaciones simuladas | Se envía `VerifyEmail` al usuario nuevo | Igual | Pasó |
| CP-157 | Cambio de correo en `/settings/profile` | Verification inválida y aviso nuevo enviado | Antes: sin aviso; después: `VerifyEmail` enviado | Pasó (BUG-026) |
| CP-158 | Verificación con el enlace del correo nuevo | Cuenta verificada y evento `Verified` | Igual tras el fix | Pasó (BUG-026) |
| CP-159 | Seis reenvíos de verificación en un minuto | Seis avisos y 429 en el séptimo | Igual | Pasó |
| CP-160 | Enlace de verificación vencido | 403, sin evento y cuenta sin verificar | Igual | Pasó |
| CP-161 | Reset con correo existente vs. inexistente | Mismo `status` y sin aviso para el inexistente | Antes: error que revelaba la cuenta; después: estado genérico en la vista Inertia | Pasó (BUG-027) |
| CP-162 | Token de reset con más de 60 minutos | No cambia la contraseña y expone error en `email` | Igual | Pasó |
| CP-163 | Reutilización de un token de reset ya usado | Segundo intento rechazado, contraseña vigente | Igual | Pasó |
| CP-164 | Código de recuperación 2FA | Ingresa, se consume y no sirve en un segundo challenge | Igual | Pasó |
| CP-165 | Once pedidos de passkey con la misma credencial | 429 en el undécimo | Igual | Pasó |
| CP-166 | Rollback de las 23 migraciones y re-migración | Sin error, esquema completo otra vez | Igual | Pasó |
| CP-167 | Duplicar email y slug por SQL directo | Unique rechaza ambos | Igual | Pasó |
| CP-168 | Duplicar favorito y nota del mismo versículo | Unique rechaza ambos | Igual | Pasó |
| CP-169 | Duplicar follow y like | Unique rechaza ambos | Igual | Pasó |
| CP-170 | Duplicar reacción del mismo tipo en un comentario | Unique rechaza el duplicado | Igual | Pasó |
| CP-171 | Duplicar versículo en una traducción | Unique rechaza, traducción alterna permitida | Igual | Pasó |
| CP-172 | Duplicar crossref de seis columnas | Unique rechaza el duplicado | Igual | Pasó |
| CP-173 | Borrar usuario con comentarios, reacciones, follows y likes | Todo se elimina por cascade | Igual | Pasó |
| CP-174 | Borrar comentario con respuestas y reacciones | Todo se elimina por cascade | Igual | Pasó |
| CP-175 | Borrar post con respuestas, rebibleos y likes | Todo se elimina por cascade | Igual | Pasó |
| CP-176 | Borrar libro bíblico con versículos | FK sin cascade bloquea el borrado | Igual | Pasó |
| CP-177 | Rollback y re-migración de `add_kind_to_posts_table` | Columna removida y default `reflexion` restaurado | Igual | Pasó |
| CP-178 | Rollback y re-migración de índices de integridad | Tres índices con nombre removidos y restaurados | Igual | Pasó |
| CP-179 | Backfill de slugs al re-migrar | Dos `juan-perez` distintos, ninguno nulo | Igual | Pasó |
| CP-180 | Backfill de traducción al re-migrar | Versículo viejo queda con `spa_rvg` | Igual | Pasó |
| CP-181 | Re-migrar con comentarios y reacciones legacy | 2 posts, 1 respuesta, 1 like y mapa con 2 filas | Igual | Pasó |
| CP-182 | Feed con 4 → 24 personas sugeridas | Consultas constantes | Antes: 9 → 23; después: constantes | Pasó (BUG-028) |
| CP-183 | Flag `following` tras bachear los IDs | `true` solo para el usuario seguido | Igual | Pasó (BUG-028) |
| CP-184 | Feed con 5 → 60 publicaciones | Consultas constantes | Igual | Pasó |
| CP-185 | `/api/favorites` y `/api/notes` con 150 filas | 1 consulta por endpoint | Igual | Pasó |
| CP-186 | Capítulo con 20 → 200 versículos | Consultas constantes | Igual | Pasó |
| CP-187 | Grafo con 1 → 61 crossrefs | Consultas constantes | Igual | Pasó |
| CP-188 | `/buscar?q=Dios` con 50 → 600 versículos | Consultas constantes | Igual | Pasó |
| CP-189 | Páginas protegidas sin sesión | Redirect a `/login` | Igual en 7 rutas | Pasó |
| CP-190 | Usuario sin verificar en settings | `verification.notice` en seguridad, acceso al resto | Igual | Pasó |
| CP-191 | Seguridad con contraseña sin confirmar | Redirect a `password.confirm`; con sesión confirmada, 200 | Igual | Pasó |
| CP-192 | Autenticado en login/registro/reset | Sale vía `/` hacia `/comunidad` | Igual | Pasó |
| CP-193 | Throttles declarados en rutas sensibles | Presentes en 9 rutas | Igual | Pasó |
| CP-194 | Nota con `body` de 5001 caracteres | 422 sin guardar | Igual | Pasó |
| CP-195 | Post y reply con 2001 caracteres | 422 sin guardar | Igual | Pasó |
| CP-196 | `book_code` de 11 caracteres en cuatro escrituras | 422 con error en `book_code` | Igual | Pasó |
| CP-197 | Capítulo o versículo 0 en cuatro escrituras | 422 con error en el campo | Igual | Pasó |
| CP-198 | Búsqueda con 55 coincidencias | 50 resultados | Igual | Pasó |
| CP-199 | Feed con 61 publicaciones | 60 en la prop `posts` | Igual | Pasó |
| CP-200 | Feed con 21 personas sugeridas | 20 en la prop `people` | Igual | Pasó |
| CP-201 | Hilo con 22 niveles | 20 respuestas serializadas | Igual | Pasó |
| CP-202 | Perfil con 61 publicaciones | 60 en la prop `posts` | Igual | Pasó |
| CP-203 | Nombre/email largos y email malformado | 422 en el campo correspondiente | Igual | Pasó |
| CP-204 | Grafo con 30 referencias entrantes | 25 aristas | Igual | Pasó |
| CP-205 | Pestañas inválidas en feed y perfil | Fallback a `para_ti` y `posts` | Igual | Pasó |
| CP-206 | `structure` sin libro y con capítulo no numérico | 422 en `book` y `chapter` | Igual | Pasó |
| CP-207 | Versículo sólo en `spa_pdt` o con texto vacío | 422 como referencia | Igual | Pasó |
| CP-208 | `seed-text` con versículo sin `number` | Omite el ítem sin romper | Antes: excepción; después: SUCCESS sin fila | Pasó (BUG-029) |
| CP-209 | `seed-text` con argumento `spa_pdt` | Guarda esa traducción | Igual | Pasó |
| CP-210 | `seed-crossrefs` con índice sin libros | FAILURE con mensaje | Igual | Pasó |
| CP-211 | `seed-crossrefs` con fallo de conexión en capítulo | FAILURE y 0 filas | Igual | Pasó |
| CP-212 | `seed-crossrefs` con entrada sin `verse` | Omite y 0 filas | Igual | Pasó |
| CP-213 | `seed-crossrefs` reimportado con otro `score` | Actualiza a 20 sin duplicar | Igual | Pasó |
| CP-214 | `bible:list-spanish` con traducciones mixtas | Sólo las `spa` | Igual | Pasó |
| CP-215 | `bible:list-spanish` sin traducciones `spa` | SUCCESS con aviso | Igual | Pasó |
| CP-216 | `_` y `\` en `books-search`; `D_o` en `/buscar` | Comodines literales, sin resultados | Igual | Pasó |
| CP-217 | `Génesis 0:1` y `Génesis 1:0` en `resolve` | 404 | Igual | Pasó |
| CP-218 | `%` y `_` en `resolve` | 422 por formato | Igual | Pasó |
| CP-219 | `structure` con capítulo 99 | 200 con `verses` vacío | Igual | Pasó |
| CP-220 | Capítulo y versículo sólo en `spa_pdt` | 404 en lectura y compartido | Igual | Pasó |
| CP-221 | Rebiblear un rebibleo | `rebible_of_id` apunta al original | Igual | Pasó |
| CP-222 | Rebiblear el propio rebibleo | Se rechaza sin duplicar | Igual | Pasó |
| CP-223 | DELETE sin sesión en notas y favoritos | 401 | Igual | Pasó |
| CP-224 | Borrar perfil sin `password` | 422 y usuario intacto | Igual | Pasó |
| CP-225 | Cambiar clave sin confirmación | 422 y clave intacta | Igual | Pasó |
| CP-226 | Usuario no verificado cambiando clave | Redirect a verificación y clave intacta | Igual | Pasó |
| CP-227 | 16ª publicación, respuesta, seguido y rebibleo | 429 en cada acción | Igual | Pasó |
| CP-228 | 31ª nota y favorito | 429 | Igual | Pasó |
| CP-229 | 31º borrado de nota y favorito | 429 | Igual | Pasó |
| CP-230 | `%` y `\` en `/buscar` | Comodines literales, una sola coincidencia | Igual | Pasó |
| CP-231 | Referencia parcial en post y respuesta | 422 en `book_code`, sin filas nuevas | Igual | Pasó |
| CP-232 | Grafo con scores 1..30 | 25 aristas, scores 6..30 | Igual | Pasó |
| CP-233 | `resolve` sin `ref` o con `ref` vacío | 422 con mensaje de formato | Igual | Pasó |
| CP-234 | Portada sin corpus y con versículo huérfano | `null` y luego `bookName` igual al código | Igual | Pasó |
| CP-235 | Feed `mios` y perfil `respuestas` | Sólo posts propios / sólo respuestas propias | Igual | Pasó |

Suite QA final: 188 tests, 188 pasan, 0 fallos, 0 omitidos, 1239 aserciones, 9,4 s (incluye la simulación de red de 50 usuarios).

<a id="casos-no-ejecutados"></a>

## Casos no ejecutados
| Área | Motivo |
| --- | --- |
| Passkeys (registro y autenticación WebAuthn) | Requiere emulador de autenticador; en la ronda 11 se cubrió el throttle por credencial, no el intercambio WebAuthn |
| E2E automatizado (Playwright/Dusk) | No hay runners instalados; el flujo login → navegación → logout se verificó manualmente en la ronda 3 |
| Tests de componentes Vue | No hay Vitest/jsdom en `package.json` |
| Carga con `ab`/`k6` y servidor real | La ronda 5 cubrió carga ligera y pico corto con `curl_multi` sobre el servidor integrado; sigue sin medirse con FPM/Apache ni de forma sostenida |
| Lector de pantalla dedicado (NVDA/VoiceOver) | Solo se revisó el árbol accesible del navegador |
| Firefox / WebKit | Solo se dispuso de Chromium |
| Búsqueda FULLTEXT (`q >= 3`) sobre MySQL real | `MATCH ... AGAINST` no existe en SQLite; se validó el fallback `LIKE` y el test quedó reactivado |
| Seeds contra `bible.helloao.org` real | La ronda 7 simuló la API con `Http::fake()`; no se comprobó latencia, TLS ni el formato actual del servicio |
| Concurrencia de escrituras sobre MySQL real | La ronda 9 sondeó tres servidores sobre SQLite y obtuvo `database is locked`; no es evidencia válida para MySQL: sigue **NO VERIFICADO** |

Los viewports 375/768/1366/1920 y la pérdida de red a mitad de flujo se cubrieron en la ronda 10 con emulación CDP (CP-150 a CP-154); la revisión manual con lector de pantalla sigue pendiente. Correos, verificación y recuperación se cubrieron en la ronda 11 (CP-156 a CP-165) con notificaciones simuladas y cola `sync`; no se usó un servidor SMTP real. La integridad de esquema y migraciones se cubrió en la ronda 12 (CP-166 a CP-181) con SQL directo sobre SQLite en memoria. Los presupuestos de consultas se midieron en la ronda 13 (CP-182 a CP-188) con el query log de Laravel, sin servidor web. El contrato de rutas y middleware se cubrió en la ronda 14 (CP-189 a CP-193) con peticiones Pest, sin servidor web. Los límites y validaciones, incluidas las ramas de los comandos con API simulada, se cubrieron en la ronda 15 (CP-194 a CP-215). Los paths negativos y los throttles reales se cubrieron en la ronda 16 (CP-216 a CP-229) con peticiones Pest. Las fronteras finales se cubrieron en la ronda 17 (CP-230 a CP-235); el caso del libro huérfano usó `PRAGMA defer_foreign_keys` dentro de `RefreshDatabase`.
