# 10 — Plan de corrección

## Índice
- [Principios](#principios)
- [Orden recomendado](#orden-recomendado)
- [Quick wins](#quick-wins)
- [Dependencias entre bugs](#dependencias-entre-bugs)
- [Bloqueos para liberación](#bloqueos-para-liberación)

<a id="principios"></a>

## Principios
Este plan era una recomendación para el equipo de desarrollo, ordenada por riesgo y esfuerzo. Con autorización explícita se ejecutaron tres rondas de corrección sobre código de aplicación: los siete hallazgos detectados por ejecución (BUG-003 a BUG-009), los seis restantes de frontend y build (BUG-001, BUG-002 y BUG-010 a BUG-013) y los tres hallazgos del navegador (BUG-014 a BUG-016). La ronda 7 agregó cuatro hallazgos de integración con API externa (BUG-017 a BUG-020) y la ronda 8 dos de sesiones caducadas (BUG-021 y BUG-022); la ronda 9 agregó dos de estado del cliente (BUG-023 y BUG-024) y la ronda 10 uno de feedback de UI (BUG-025) y la ronda 11 dos de correos y recuperación (BUG-026 y BUG-027). Los 29 quedaron corregidos y verificados; no hay hallazgos abiertos. La ronda 12 agregó 16 tests de integridad de BD sin hallazgos nuevos; la ronda 13 agregó 7 tests de presupuesto de consultas y un defecto de rendimiento (BUG-028); la ronda 14 agregó 5 tests de contrato de rutas sin hallazgos nuevos; la ronda 15 agregó 22 tests de límites y comandos con un defecto corregido (BUG-029); la ronda 16 agregó 14 tests de paths negativos y throttles reales sin defectos nuevos; la ronda 17 agregó 6 tests de fronteras finales, también sin defectos.

<a id="orden-recomendado"></a>

## Orden recomendado
**Rondas 1, 2, 3, 7, 8, 9, 10, 11, 13 y 15 ejecutadas y verificadas:** los 29 hallazgos. Las rondas 16 y 17 no agregaron hallazgos. La ronda 12 no tuvo hallazgos. Las rondas 4 a 6 agregaron perfilado, carga y seguridad funcional sin nuevos defectos. El detalle de cada cambio está en `00-resumen-ejecutivo.md`.

| Orden | ID | Severidad | Prioridad | Corrección aplicada | Esfuerzo |
| ---: | --- | --- | --- | --- | --- |
| 1 | BUG-010 | S4 | P3 | `PasswordInput` tabulable con `aria-pressed`; combobox con flechas/Enter/Escape y `aria-activedescendant`; foco visible en el grafo | M |
| 2 | BUG-012 | S4 | P3 | Códigos 2FA con `v-if`; errores `role=alert`; estados `role=status`; botón de copiar con nombre | S |
| 3 | BUG-002 | S4 | P4 | Wayfinder no corre en `vite build`, así que no se exige `APP_KEY` en un checkout limpio | S |
| 4 | BUG-011 | S4 | P4 | `aria-pressed`, `aria-expanded` y `aria-current` en toggles, pestañas y menú | S |
| 5 | BUG-001 | S5 | P4 | Al no regenerarse en el build, los artefactos versionados quedan intactos | S |
| 6 | BUG-013 | S5 | P4 | Placeholders deshabilitados, sin `href="#"` y nombres únicos por ítem | S |
| 7 | BUG-014 | S3 | P2 | Fallback `LIKE` escapado en SQLite; FULLTEXT se conserva en MySQL | S |
| 8 | BUG-015 | S4 | P3 | Orden de foco por DOM, contraste, `aria-label` con referencia y `<main>` en auth | S |
| 9 | BUG-016 | S5 | P4 | Meta descripción en la plantilla compartida | S |
| 10 | — | — | P3 | Carga ligera y pico corto ejecutados en ronda 5; sin defectos nuevos | S |
| 11 | — | — | P3 | Seguridad funcional cubierta en ronda 6 con 8 tests preventivos; sin defectos nuevos | S |
| 12 | BUG-017 | S3 | P2 | `retry(..., null, false)` y captura de `ConnectionException` en los seeds | S |
| 13 | BUG-018 | S3 | P2 | Validación de índice/capítulos con conteo de fallos y `FAILURE` en `bible:seed-crossrefs` | S |
| 14 | BUG-019 | S4 | P3 | `insert()` → `upsert()` con la clave única de referencias cruzadas | S |
| 15 | BUG-020 | S5 | P4 | Rechazo de payload 200 sin libros en `bible:seed-text` | S |
| 16 | BUG-021 | S3 | P2 | Callback de `HttpException(419)` con `X-Inertia`: redirect a `/login` sin tocar el 419 de clientes JSON | S |
| 17 | BUG-022 | S3 | P2 | Interceptor global de axios: 401/419 redirigen al login una sola vez | S |
| 18 | BUG-023 | S3 | P2 | La primera visita Inertia inicializa `currentUserId` sin ejecutar el reset de composables | S |
| 19 | BUG-024 | S4 | P3 | Deduplicación por clave al insertar favoritos en el estado local del cliente | S |
| 20 | BUG-025 | S4 | P3 | Montar el contenedor `ToastContainer` en la raíz Inertia para que los `showToast()` se vean y se anuncien | S |
| 21 | BUG-026 | S3 | P2 | Enviar el aviso de verificación cuando cambia el correo del perfil | S |
| 22 | BUG-027 | S3 | P2 | Respuesta de Fortify que oculta si el correo del reset existe | S |
| 23 | BUG-028 | S4 | P3 | Reutilizar los IDs seguidos al serializar personas en el feed y el perfil, eliminando hasta 20 consultas por visita | S |
| 24 | BUG-029 | S4 | P3 | Omitir versículos sin `number` en `bible:seed-text` para no abortar la importación | S |
| 25 | — | — | P3 | Pendiente: FULLTEXT en MySQL aislado, lector de pantalla, WebAuthn real, carga sostenida con servidor real y concurrencia sobre MySQL | M |

<a id="quick-wins"></a>

## Quick wins
- BUG-003 y BUG-004 ya se resolvieron en un único punto de la lógica de slug (eventos `creating`/`updating` del modelo `User`), sin duplicar código entre registro y edición de perfil.
- BUG-006 se resolvió con el trait `VerseReferenceValidationRules`, reutilizado en escritura y borrado para que no haya divergencia.
- BUG-001 y BUG-002 se resolvieron juntos: el plugin Wayfinder se omite durante el build, con lo que no exige `.env` ni reescribe archivos generados.
- BUG-007 se resolvió escapando el patrón `LIKE`; BUG-008, filtrando `edges` contra los IDs de `nodes`.
- BUG-009 se resolvió descomentando el contrato en `App\Models\User` y agregándolo a `implements`, antes de tocar la UI de verificación.
- BUG-010, BUG-011 y BUG-012 se agruparon por componente compartido (`PasswordInput`, `InputError`, `AppearanceTabs`) para no repetir el parche en cinco páginas.
- BUG-014 se resolvió con una rama por driver en el controlador, sin cambiar la consulta FULLTEXT de MySQL.
- BUG-015 y BUG-016 se cerraron con cambios puntuales en plantilla, tokens de color y `aria-label`, cubiertos por tests estáticos y Lighthouse.
- La ronda 6 no requirió cambios de aplicación: los controles de autoría y validación ya existían. Solo se agregaron 8 tests en `tests/QA/SecurityFunctionalTest.php` para impedir regresiones de mass assignment y render de HTML crudo.
- La ronda 7 tocó dos comandos Artisan y sus tests: los reintentos ya no lanzan la última respuesta como excepción, los fallos se reportan y el import de referencias cruzadas es repetible.
- La ronda 8 tocó `bootstrap/app.php` y `resources/js/app.js`: el 419 de una visita Inertia se convierte en redirect al login y el interceptor de axios hace lo mismo con 401/419 de las APIs, con guardia contra navegaciones duplicadas.
- La ronda 9 tocó `resources/js/app.js` y `resources/js/composables/useFavorites.js`: el handler de `success` inicializa el usuario sin descartar estado, y el alta de favoritos deduplica por clave antes de actualizar el arreglo local.
- La ronda 10 tocó `resources/js/app.js`: se monta `ToastContainer` en la raíz para que la cola de `useToast()` se renderice con `aria-live="polite"`.
- La ronda 11 tocó `app/Http/Controllers/Settings/ProfileController.php`, `app/Providers/FortifyServiceProvider.php` y agregó `app/Http/Responses/FailedPasswordResetLinkRequestResponse.php`: se envía el aviso al cambiar el correo y se iguala la respuesta del reset para cuentas inexistentes.
- La ronda 12 no cambió código de aplicación: agregó `tests/QA/DatabaseIntegrityTest.php` con rollback de 23 migraciones, constraints, cascadas, backfills y migración legacy.
- La ronda 13 tocó `app/Http/Controllers/PostController.php` y `app/Http/Controllers/ProfileController.php`: `serializePerson()` usa los IDs seguidos ya cargados, sin consultar la relación por cada persona.
- La ronda 14 no tocó código de aplicación: agregó `tests/QA/RouteContractTest.php` con redirecciones, `verified`, confirmación de contraseña y throttles declarados.
- La ronda 15 tocó `app/Console/Commands/SeedBibleText.php`: los ítems `verse` sin `number` se omiten sin abortar la importación. También agregó `tests/QA/ValidationLimitsTest.php` y `tests/QA/CommandEdgeCasesTest.php`.
- La ronda 16 no tocó código de aplicación: agregó `tests/QA/NegativePathsTest.php` con escapes, referencias inválidas, guardas de traducción, rebibleos, autenticación y throttles reales.
- La ronda 17 no tocó código de aplicación: agregó `tests/QA/FinalBoundariesTest.php` con escapes de `%`/`\`, referencias parciales, ranking del grafo, portada y pestañas.
- La suite QA ya es reejecutable con `php artisan test tests/QA`: no hace falta ninguna config nueva en la raíz.

<a id="dependencias-entre-bugs"></a>

## Dependencias entre bugs
BUG-003 y BUG-004 compartían causa (no había lógica centralizada de slug) y se corrigieron juntos en los eventos `creating`/`updating` de `User`. BUG-009 era independiente: se resolvió en el contrato del modelo, antes de tocar la UI de verificación. BUG-005 requirió definir que el hilo social es multinivel y aplanar los descendientes en `show()`. BUG-006 compartía patrón con `store()`/`upsert()` y se unificó en un trait. BUG-007 y BUG-008 eran independientes; este último se corrigió antes de importar más cross-references. BUG-010 a BUG-013 se agruparon por componente compartido. BUG-001 y BUG-002 resultaron ser la misma causa (Wayfinder corriendo en el build) y se resolvieron con un único cambio en `vite.config.ts`.

<a id="bloqueos-para-liberación"></a>

## Bloqueos para liberación
**Sin bloqueos.** Los 29 hallazgos están corregidos y la revalidación quedó en verde: 198/198 tests QA, 54/54 heredados, Lighthouse 1,0 en once páginas y 170/170 respuestas 200 en la prueba de carga con 15.000 versículos. El panel admin y la simulación de 50 usuarios ya se verificaron en la BD MySQL dedicada `bible_simulation`. El veredicto es **GO condicionado a FULLTEXT y concurrencia en MySQL, y lector de pantalla**: sigue NO VERIFICADO `MATCH ... AGAINST` sobre MySQL aislado, un lector de pantalla dedicado, WebAuthn con authenticator real, carga sostenida con servidor web real, la concurrencia de escrituras sobre MySQL. Viewports y pérdida de red ya se cubrieron con emulación CDP en la ronda 10; correo y notificaciones se cubrieron con fakes en la ronda 11, pero el envío SMTP real y WebAuthn siguen sin verificarse. La ronda 13 cubrió el conteo de consultas; la latencia con servidor web real sigue pendiente. La simulación y el rol admin se ejecutaron luego en MySQL 8.0.35 (`bible_simulation`), sin modificar `.env`, con login de `admin@red.test` y superpoderes confirmados en navegador. El admin pasó a ser fantasma (invisible, sin follows, omnisciente) y `bible:seed-text spa_rvg` importó 66 libros y 31.102 versículos desde la API real; FULLTEXT y concurrencia siguen pendientes.
