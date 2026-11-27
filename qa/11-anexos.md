# 11 — Anexos

## Índice
- [Comandos ejecutados](#comandos-ejecutados)
- [Revisión estática de accesibilidad](#revisión-estática-de-accesibilidad)
- [Verificación en navegador](#verificación-en-navegador)
- [Rendimiento medido en navegador](#rendimiento-medido-en-navegador)
- [Carga con volumen (ronda 5)](#carga-con-volumen-ronda-5)
- [Supuestos](#supuestos)
- [Limitaciones](#limitaciones)
- [Riesgos no cubiertos](#riesgos-no-cubiertos)
- [Estado del árbol](#estado-del-árbol)

<a id="comandos-ejecutados"></a>

## Comandos ejecutados
| Comando | Resultado resumido |
| --- | --- |
| `php -v`, `node -v`, `mysql --version` | PHP 8.3.36, Node 26.13.0, MySQL 8.0.35 |
| `composer install --no-interaction --no-progress` | 177 paquetes; lockfile sin cambios |
| `npm ci --no-audit --no-fund` | 453 paquetes; lockfile sin cambios |
| `composer validate --no-check-publish` | `composer.json is valid` |
| `composer audit --format=summary` | `No security vulnerability advisories found` |
| `composer show --direct`, `npm ls --depth=0` | Inventario de dependencias |
| `php artisan about --env=testing --only=environment,drivers` | Confirmó SQLite/mysql según fase; en este ciclo se fijó SQLite vía `phpunit.xml` |
| `php artisan migrate:fresh --seed` (SQLite `:memory:`) | 23 migraciones DONE + seeding |
| `php artisan migrate` / `migrate:reset` / `migrate` (SQLite archivo temporal) | 23 → 0 → 23, exit 0 |
| `php artisan test --compact` | 54 passed, 226 assertions, 2,8 s |
| `php artisan test tests/QA --compact` (Fase 1) | 2 passed, 7 assertions, 0,24 s |
| `php artisan test tests/QA --compact` (Fase 2) | 11 tests, 8 passed, 3 failed, 54 assertions, 1,7 s |
| `php artisan test --compact` tras Fase 2 | 54 passed, 226 assertions, 2,5 s — sin regresiones |
| `php artisan test tests/QA --compact` (Fase 3) | 22 tests, 18 passed, 4 failed, 112 assertions, 1,9 s |
| `php artisan test --compact` tras Fase 3 | 54 passed, 226 assertions, 2,5 s — sin regresiones |
| `php artisan test tests/QA --compact` (Fase 4) | 36 tests, 30 passed, 6 failed, 199 assertions, 2,3 s |
| `./vendor/bin/pint --dirty --test` tras Fase 4 | passed |
| `php artisan test --compact` tras Fase 4 | 54 passed, 226 assertions, 2,5 s — sin regresiones |
| `php artisan test tests/QA --compact` (Fase 5) | 56 tests, 47 passed, 8 failed, 1 skipped, 314 assertions, 2,7 s |
| `php artisan test --compact` tras Fase 5 | 54 passed, 226 assertions, 2,5 s — sin regresiones |
| `./vendor/bin/pint --dirty --test` tras Fase 5 | passed |
| `php artisan test tests/QA/SettingsFrontendIntegrityTest.php --compact` (Fase 6) | 14 tests, 12 passed, 2 failed, 58 assertions, 0,7 s — BUG-009 |
| `./vendor/bin/pint tests/QA/SettingsFrontendIntegrityTest.php` | fixed: `new_with_parentheses` |
| `php artisan test tests/QA --compact` (Fase 6) | 70 tests, 59 passed, 10 failed, 1 skipped, 372 assertions, 3,0 s |
| `php artisan test --compact` tras Fase 6 | 54 passed, 226 assertions, 2,6 s — sin regresiones |
| `./vendor/bin/pint --dirty --test` tras Fase 6 | passed |
| `./vendor/bin/pint --dirty --test` | passed |
| `php artisan test tests/QA/AuthSecurityTest.php tests/QA/SettingsFrontendIntegrityTest.php --compact` tras BUG-003/004/009 | 23 passed, 117 assertions |
| `php artisan test tests/QA --compact` tras ronda de corrección 1 | 70 tests, 69 passed, 0 failed, 1 skipped, 391 assertions, 3,1 s |
| `php artisan test --compact` tras ronda de corrección 1 | 54 passed, 226 assertions, 2,7 s — sin regresiones |
| `./vendor/bin/pint --dirty --test` tras ronda de corrección 1 | passed |
| `./vendor/bin/phpstan analyse --no-progress --memory-limit=1G` tras ronda de corrección 1 | 0 errores |
| `./vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | 0 errores |
| `php artisan test tests/QA/FrontendIntegrityRound2Test.php --compact` | 4 passed, 31 assertions, 0,48 s |
| `php artisan test tests/QA --compact` tras ronda de corrección 2 | 74 tests, 73 passed, 0 failed, 1 skipped, 429 assertions, 3,1 s |
| `php artisan test --compact` tras ronda de corrección 2 | 54 passed, 226 assertions, 2,7 s — sin regresiones |
| `./vendor/bin/pint --dirty --test` tras ronda de corrección 2 | passed |
| `./vendor/bin/phpstan analyse --no-progress --memory-limit=1G` tras ronda de corrección 2 | 0 errores |
| `npm run lint:check` / `format:check` / `types:check` | Sin hallazgos |
| `npm run build` con `.env` | OK, 77 assets, 0,92 MB (ronda 1) |
| `npm run build` sin `.env` tras ronda 2 | OK, 1,79 s, sin `MissingAppKeyException` |
| Hash de artefactos Wayfinder antes/después del build | Estable; el build no reescribe `resources/js/actions` ni `resources/js/routes` |
| `git status --short`, `git diff` | Revisión de artefactos generados |
| `Get-Content storage\logs\laravel.log -Tail 200` | 8 coincidencias antiguas de error, 0 nuevas |
| `php artisan tinker --execute=...qa-graph-seed.php` (SQLite temporal) | Génesis, Éxodo y Juan con 5 versículos para browser QA |
| Navegador integrado: Tab/Enter/Escape, snapshot accesible y consola | Orden de foco y combobox verificados; sin errores propios de consola |
| `tools.browser.lighthouse` sobre 11 URLs | 1,0 en accesibilidad, buenas prácticas y SEO en todas |
| `php artisan test tests/QA --compact` tras ronda 3 | 77 tests, 77 passed, 0 failed, 0 skipped, 449 assertions, 3,5 s |
| `php artisan test --compact` tras ronda 3 | 54 passed, 226 assertions, 3,2 s — sin regresiones |
| `npm run build` tras ronda 3 | OK, 2,17 s sin `.env` |
| `tools.browser.trace.start` / `reload` / `stop` / `analyze` | Trazas de renderer de 394–639 ms en 5 URLs; 0–1 tareas largas |
| `PerformanceNavigationTiming` vía `evaluate` | 7 URLs, `loadEventEnd` 297–896 ms, ~8 KB transferidos por página |
| `php artisan tinker --execute=...qa-load-seed.php` | 15.000 versículos sintéticos en `bible-explorer-load.sqlite` |
| `php artisan serve --port=8001` sobre la BD de carga | Segundo servidor temporal, fuera del repo |
| `php .../qa-load-run.php` | Login con `X-XSRF-TOKEN` y 50 peticiones (5 concurrentes × 10 en 5 rutas): 50/50 200 |
| `php .../qa-stress-run.php` | 120 peticiones (20 concurrentes × 40 en 3 rutas): 120/120 200, sin errores |
| `php artisan test tests/QA/SecurityFunctionalTest.php --compact` | 8 tests, 8 passed, 39 assertions — sin defectos nuevos |
| `php artisan test tests/QA --compact` tras ronda 6 | 85 tests, 85 passed, 0 failed, 0 skipped, 488 assertions, 3,4 s |
| `php artisan test --compact` tras ronda 6 | 54 passed, 226 assertions — sin regresiones |
| `php artisan test tests/QA/ExternalApiResilienceTest.php --compact` antes del fix | 2 passed, 4 failed, 4 errors — `RequestException`, falso éxito y `UNIQUE constraint failed` |
| `php artisan test tests/QA/ExternalApiResilienceTest.php --compact` tras el fix | 8 tests, 8 passed, 27 assertions |
| `php artisan test tests/QA --compact` tras ronda 7 | 93 tests, 93 passed, 0 failed, 0 skipped, 515 assertions, 5,9 s |
| `php artisan test --compact` tras ronda 7 | 54 passed, 226 assertions — sin regresiones |
| `php .../qa-session-probe.php` contra `artisan serve` | Login 200, `/comunidad` Inertia 200, logout 204, invitado 302 a `/login`, `/api/notes` 401 JSON; `POST /posts` con XSRF vencido: 419 HTML antes del fix y 302 a `/login` después |
| `php artisan test tests/QA/SessionExpiryTest.php --compact` antes del fix | 4 passed, 1 failed: esperaba 302 y devolvía 419 |
| `php artisan test tests/QA/SessionExpiryTest.php --compact` tras el fix | 5 tests, 5 passed, 12 assertions |
| Navegador: `/logout` por `fetch` en `/leer/GEN/1` y clic en favorito | `/api/favorites` y `/api/notes` registrados con 401 en `performance`; la SPA navegó a `/login` |
| Navegador: `/logout` por `fetch` en `/comunidad` y clic en “Me gusta” | `POST /posts/1/like` registrado con 302; la SPA navegó a `/login` |
| `php artisan test tests/QA --compact` tras ronda 8 | 98 tests, 98 passed, 0 failed, 0 skipped, 527 assertions, 6,3 s |
| `php artisan test --compact` tras ronda 8 | 54 passed, 226 assertions — sin regresiones |
| `./vendor/bin/pint --test`, `./vendor/bin/phpstan analyse --memory-limit=1G` | Pint aplicó `ordered_imports` en tests anteriores; tras la ronda 10, ambos en verde |
| `npm run lint:check`, `format:check`, `types:check`, `build` | En verde tras montar `ToastContainer`; build en 1,69 s |
| `php .../qa-concurrency-run.php` y `qa-toggle-race.php` contra tres `artisan serve` en SQLite | Envíos duplicados de favoritos/notas sin filas duplicadas; likes/follows con `500 SQLITE_BUSY` por bloqueo del driver, no extrapolable a MySQL |
| Navegador: guardar favorito en `/leer/GEN/1` y navegar a `/favoritos` | Antes: 0 artículos y sin `GET /api/favorites`; servidor con 1 fila. Después: 1 artículo |
| Navegador: dos activaciones del botón de favorito | Antes: dos `POST` y 2 artículos; después: dos `POST` y 1 artículo |
| `php artisan test tests/QA/ClientStateRaceTest.php --compact` | 5 tests, 5 passed, 15 assertions |
| `php artisan test tests/QA --compact` tras ronda 9 | 103 tests, 103 passed, 0 failed, 0 skipped, 542 assertions, 5,9 s |
| `php artisan test --compact` tras ronda 9 | 54 passed, 226 assertions — sin regresiones |
| `node .../qa-cdp-probe.mjs` con `Emulation.setDeviceMetricsOverride` | 36 combinaciones (9 páginas × 375/768/1366/1920) sin desbordamiento; a 375 px el toggle abre 9 enlaces y se cierra al navegar |
| `node .../qa-cdp-probe.mjs` con `Network.emulateNetworkConditions({offline: true})` | `net::ERR_INTERNET_DISCONNECTED` al guardar favorito; sin estado pendiente, reintento exitoso |
| Navegador CDP: mensajes de `showToast()` | Antes: `hasSuccessText: false`, `hasErrorText: false`, 0 nodos `aria-live` con texto; después: ambos textos visibles en `aria-live="polite"` |
| `php artisan test tests/QA/ResponsiveFeedbackTest.php --compact` | 4 tests, 4 passed, 8 assertions |
| `php artisan test tests/QA --compact` tras ronda 10 | 107 tests, 107 passed, 0 failed, 0 skipped, 550 assertions, 6,1 s |
| `php artisan test --compact` tras ronda 10 | 54 passed, 226 assertions — sin regresiones |
| `php artisan test tests/QA/MailAndVerificationTest.php --compact` antes del fix | 10 tests, 8 passed, 2 failed: sin `VerifyEmail` al cambiar el correo y `status` ausente en el reset con correo inexistente |
| `php artisan test tests/QA/MailAndVerificationTest.php --compact` tras el fix | 10 tests, 10 passed, 75 assertions, 1,7 s |
| `php artisan test tests/QA --compact` tras ronda 11 | 117 tests, 117 passed, 0 failed, 0 skipped, 625 assertions, 7,4 s |
| `php artisan test --compact` tras ronda 11 | 54 passed, 226 assertions — sin regresiones |
| `php artisan test tests/QA/DatabaseIntegrityTest.php --compact` | 16 tests, 16 passed, 72 assertions, 0,7 s |
| `php artisan test tests/QA --compact` tras ronda 12 | 133 tests, 133 passed, 0 failed, 0 skipped, 697 assertions, 7,5 s |
| `php artisan test --compact` tras ronda 12 | 54 passed, 226 assertions — sin regresiones |
| `php artisan test tests/QA/QueryBudgetTest.php --compact` antes del fix | 6 tests, 5 passed, 1 failed: feed pasó de 9 a 23 consultas al crecer de 4 a 24 personas |
| `php artisan test tests/QA/QueryBudgetTest.php --compact` tras el fix | 7 tests, 7 passed, 36 assertions, 0,7 s (antes 9,5 s con 6 tests) |
| `php artisan test tests/QA --compact` tras ronda 13 | 140 tests, 140 passed, 0 failed, 0 skipped, 733 assertions, 8,0 s |
| `php artisan test --compact` tras ronda 13 | 54 passed, 226 assertions — sin regresiones |
| `php artisan test tests/QA/RouteContractTest.php --compact` | 5 tests, 5 passed, 42 assertions, 0,4 s |
| `php artisan test tests/QA --compact` tras ronda 14 | 145 tests, 145 passed, 0 failed, 0 skipped, 775 assertions, 8,5 s |
| `php artisan test --compact` tras ronda 14 | 54 passed, 226 assertions — sin regresiones |
| `php artisan test tests/QA/ValidationLimitsTest.php --compact` | 14 tests, 14 passed, 117 assertions, 0,8 s |
| `php artisan test tests/QA/CommandEdgeCasesTest.php --compact` antes del fix | 8 tests, 7 passed, 1 error: `Undefined array key "number"` en `SeedBibleText.php:97` |
| `php artisan test tests/QA/CommandEdgeCasesTest.php --compact` tras el fix | 8 tests, 8 passed, 22 assertions, 0,8 s |
| `php artisan test tests/QA --compact` tras ronda 15 | 167 tests, 167 passed, 0 failed, 0 skipped, 914 assertions, 8,8 s |
| `php artisan test --compact` tras ronda 15 | 54 passed, 226 assertions — sin regresiones |
| `php artisan test tests/QA/NegativePathsTest.php --compact` | 14 tests, 14 passed, 225 assertions, 0,9 s |
| `php artisan test tests/QA --compact` tras ronda 16 | 181 tests, 181 passed, 0 failed, 0 skipped, 1139 assertions, 9,2 s |
| `php artisan test --compact` tras ronda 16 | 54 passed, 226 assertions — sin regresiones |
| `php artisan test tests/QA/FinalBoundariesTest.php --compact` | 6 tests, 6 passed, 75 assertions, 0,5 s |
| `php artisan test tests/QA --compact` tras ronda 17 | 187 tests, 187 passed, 0 failed, 0 skipped, 1214 assertions, 9,3 s |
| `php artisan test --compact` tras ronda 17 | 54 passed, 226 assertions — sin regresiones |
| `php artisan test tests/QA/SocialNetworkSimulationTest.php --compact` | 1 test, 1 passed, 25 assertions, 0,6 s |
| `php artisan test tests/QA/AdminRoleTest.php --compact` | 7 tests, 7 passed, 0 failed, 0 skipped, 86 assertions, 0,9 s |
| `php artisan test tests/QA/AdminGhostTest.php --compact` | 3 tests, 3 passed, 0 failed, 0 skipped, 35 assertions, 0,4 s |
| `php artisan test tests/QA --compact` con simulación, rol admin y modo fantasma | 198 tests, 198 passed, 0 failed, 0 skipped, 1363 assertions, 10,1 s |
| `php artisan test --compact` con la simulación | 54 passed, 226 assertions — sin regresiones |

<a id="revisión-estática-de-accesibilidad"></a>

## Revisión estática de accesibilidad
Revisión original de solo lectura sobre `resources/js` (ponentes propios y uso concreto de componentes `ui/`). Las observaciones de la tabla son el estado original, **CONFIRMADO por lectura de código**, corregidas en la ronda 2 y confirmadas en navegador con axe/Lighthouse en la ronda 3. No se usó un lector de pantalla dedicado. Los hallazgos se agruparon en `05-bugs-S4-S5-menores.md`.

| Archivo:línea | Observación | Esperado | Impacto | Grupo |
| --- | --- | --- | --- | --- |
| `components/PasswordInput.vue:31-43` | Botón ojo con `tabindex="-1"` y sin `aria-pressed`; afecta Login, Register, ResetPassword, ConfirmPassword, Security y DeleteUser | Tabulable y con estado anunciado | Medio-alto | BUG-010 |
| `components/Social/VerseReferencePicker.vue:231-259` | `role="combobox"` sin flechas/Enter/Escape ni `aria-activedescendant`; id fijo `verse-book-suggestions` | Patrón de combobox completo e ids únicos | Medio-alto | BUG-010 |
| `pages/Bible/Graph.vue:188-205`, `composables/useVerseSearch.js:16-72` | `<ul role="listbox">` con `<button>` sin `role="option"`/`aria-selected`; input sin `aria-expanded`; error sin `role="alert"` | Sugerencias navegables y error anunciado | Medio | BUG-010 |
| `pages/Bible/Graph.vue:274-281`, `composables/useConstellationGraph.js:89-116` | Nodos con `tabindex`/`role`/`aria-label`, pero sin CSS de foco; `<svg role="img">` puede ocultarlos (PROBABLE) | Foco visible y semántica coherente | Alto dentro del grafo | BUG-010 |
| `components/AppearanceTabs.vue:39-66,78-105`; `pages/Bible/Chapter.vue:201-213,269-289,291-321`; `pages/Community/Feed.vue:108-121`; `pages/Profile/Show.vue:204-217`; `layouts/SettingsLayout.vue:56-74`; `components/Bible/NavHeader.vue:78-82` | Estado activo solo por color/borde; sin `aria-pressed`/`aria-expanded`/`aria-current` | Estado anunciado | Medio | BUG-011 |
| `components/TwoFactorRecoveryCodes.vue:57-89` | Códigos “ocultos” con `h-0 opacity-0`, sin `hidden`/`aria-hidden`; toggle sin estado | Fuera del árbol accesible | Medio-alto | BUG-012 |
| `components/InputError.vue:7-11`; `pages/auth/Login.vue:31-36`; `ForgotPassword.vue:26-31`; `VerifyEmail.vue:23-28`; `pages/settings/Profile.vue:103-109`; `Security.vue:97-102` | `<p>` de error sin `role="alert"`, sin `aria-describedby`/`aria-invalid`; textos de estado sin `role="status"` | Errores asociados al campo y estados anunciados | Medio | BUG-012 |
| `components/TwoFactorSetupModal.vue:228-243` | Botón de copiar solo con icono `Copy`/`Check`, sin nombre ni `type="button"`; input readonly sin `<label>` | Nombre accesible y etiqueta | Medio-alto | BUG-012 |
| `pages/Welcome.vue:365-381`; `pages/Community/Feed.vue:278-283` | Botones “Iniciar conversación”, “Pedir oración” y “Hablar con un pastor” sin `@click` | Deshabilitar u ocultar mientras no estén listos | Medio | BUG-013 |
| `components/Social/PostCard.vue:110-116,136-142`; `Feed.vue:201-206,225-232`; `components/Profile/ProfileGoalsSection.vue:232-236` | `href="#"` cuando falta `slug` | No exponer enlace sin destino | Bajo | BUG-013 |
| `pages/Bible/Book.vue:81-88`; `pages/Bible/Search.vue:160-169`; `pages/Bible/Favorites.vue:97-104`; `Notes.vue:97-104`; `components/PasskeyItem.vue:64-71` | Enlaces “1”, “2” y “Ver conexiones”, botones “Quitar”/“Eliminar” repetidos sin contexto | Nombre único por ítem | Medio-bajo | BUG-013 |
| `components/Profile/ProfileGoalsSection.vue:168-206` | Progreso indicado solo por color y etiqueta flotante no asociada | Estado accesible por paso | Medio-bajo | BUG-013 |
| `components/DeleteUser.vue:70-79` | `PasswordInput` sin `autocomplete="current-password"` | Coherencia con el resto de formularios | Bajo-medio | BUG-013 |
| `pages/auth/TwoFactorChallenge.vue:100-113` | Botón que alterna código/recuperación sin estado de toggle | Estado coherente | Bajo | BUG-011 |
| `components/Social/PostComposer.vue:156-175` | Radios `sr-only` sin indicador de foco `:focus-within` | Foco visible | Medio | BUG-010 |
| `components/Social/PostCard.vue:62-75` | `navigator.clipboard` falla con fallback silencioso, sin toast | Feedback de fracaso | Bajo | BUG-013 |
| `components/Bible/BiblePageShell.vue:47-53` | `aria-label="Abrir menú…"` no cambia con el menú abierto (PROBABLE) | Nombre según acción disponible | Bajo | BUG-011 |
| `components/ManagePasskeys.vue:52-63` | Icono decorativo `KeyRound` sin `aria-hidden` (PROBABLE) | Marcar como decorativo | Muy bajo | BUG-013 |
| Múltiples enlaces (`Feed`, `Show`, `Devotional/Index`, `Favorites`, `Notes`, `Graph`, `Welcome`, `Search`) | Sin `focus-visible:ring-*`; el outline del UA sigue presente (PROBABLE) | Indicador uniforme | Bajo | BUG-013 |

Los ejemplos de lo que ya está bien (etiquetas, landmarks, `aria-pressed`, regiones vivas, foco visible, iconos ocultos, diálogos y ausencia de handlers en `div`/`span`) están en `09-lo-que-funciona-bien.md`.

<a id="verificación-en-navegador"></a>

## Verificación en navegador
La ronda 3 levantó la app con `artisan serve` sobre `C:\Users\Desarrollo\AppData\Local\Temp\opencode\bible-explorer-qa.sqlite`, sembrada con `migrate:fresh --seed` y 5 versículos de prueba insertados con Tinker. No se usó Vite dev, MySQL ni un runner JS nuevo.

| Superficie | Evidencia | Resultado |
| --- | --- | --- |
| `/login` | Tab real desde el correo y snapshot accesible | email → olvidé password → password → ojo → recordarme → enviar; `aria-pressed` y `type` cambian con Enter |
| `/login` con credenciales inválidas | `role=alert`, `aria-live=polite`, `aria-invalid` y `aria-describedby` | Error anunciado y asociado al campo; sin errores de consola |
| `/login` con `test@example.com` / `password` | Navegación posterior | Redirect a `/comunidad`; logout devuelve a `/` |
| `/bible/GEN/1/1` | Teclado sobre el combobox y los nodos SVG | `aria-activedescendant`, `role=option`, Escape cierra la lista, foco visible y `<svg role="group">` |
| `/api/bible/resolve?ref=Genesis%201` | 422 esperado | `role=alert` con “Formato no válido…”; el 422 de consola es la respuesta prevista |
| `/buscar?q=Dios` | Fallback `LIKE` sobre SQLite | 2 resultados; antes respondía 500 con `MATCH ... AGAINST` |
| 11 URLs | `tools.browser.lighthouse` | 1,0 en accesibilidad, buenas prácticas y SEO; `failures: []` |

<a id="rendimiento-medido-en-navegador"></a>

## Rendimiento medido en navegador
Se usó `tools.browser.trace.start({ durationMs: 8000 })`, `reload({ ignoreCache: true })`, `trace.stop` y `trace.analyze` en `/comunidad`, `/leer/GEN/1`, `/bible/GEN/1/1`, `/buscar?q=Dios`, `/notas` y `/login`. En paralelo se leyó `performance.getEntriesByType("navigation")[0]` en siete URLs. Todas las trazas terminaron completas (`incomplete: false`); el analyser advierte explícitamente que no calcula LCP, INP, CLS ni puntaje de performance de Lighthouse.

| Métrica | Valores observados |
| --- | --- |
| Duración de la traza | 394–639 ms |
| Eventos registrados | 1514–1845 por traza |
| Tareas largas | 0 en cinco páginas; 1 en `/comunidad` (3,2 ms de bloqueo) |
| Evento individual más caro | 53 ms en `/comunidad`, 29–49 ms en el resto |
| `loadEventEnd` | 297–896 ms; el máximo fue `/leer/GEN/1` en una sola medición |
| Transferencia por documento | ~8 KB |

El detalle por página y las condiciones están en `08-rendimiento-y-compatibilidad.md`.

<a id="carga-con-volumen-ronda-5"></a>

## Carga con volumen (ronda 5)
Se creó un SQLite temporal separado con `migrate:fresh --seed` y se poblaron 15.000 versículos sintéticos con `BibleBook::firstOrCreate` e inserciones por lote en `bible_verses` (3 libros × 100 capítulos × 50 versículos; “Dios” aparece cada quinto versículo). El login para las peticiones autenticadas se hizo con `curl_multi` enviando el valor de la cookie `XSRF-TOKEN` —URL-decodificado— en el header `X-XSRF-TOKEN`, porque el `_token` plano no está presente en una app Inertia. Sin esto, Laravel responde 419.

| Escenario | Concurrencia | Peticiones | 200 | Promedio | p95 | Máx |
| --- | ---: | ---: | ---: | ---: | ---: | ---: |
| Ligero, 5 rutas | 5 | 50 | 50 | 660–772 ms | 1114–1276 ms | 1212–1276 ms |
| Pico corto, 3 rutas | 20 | 120 | 120 | 2232–2389 ms | 4149–4375 ms | 4494–4804 ms |

Rutas del escenario ligero: `/comunidad`, `/leer/GEN/1`, `/buscar?q=Dios`, `/bible/GEN/1/1` y `/notas`. Del pico: `/buscar?q=Dios`, `/leer/GEN/1` y `/comunidad`. Ningún 500 ni timeout. El servidor integrado de PHP en Windows es monoprocreso, por lo que la subida de latencia al aumentar la concurrencia es encolamiento de peticiones, no una medida de capacidad real.

<a id="supuestos"></a>

## Supuestos
- “BD de testing” significa SQLite en memoria según `phpunit.xml`; la MySQL local con datos de desarrollo quedó fuera de alcance.
- No existen roles de negocio: solo invitado, autenticado, verificado por correo y confirmación de password, según lo deducido de rutas y middleware.
- Los 8 errores del log pertenecen a la configuración inicial del ciclo anterior (Vite manifest ausente y `APP_KEY` faltante), no a la aplicación en estado estable.
- La Fase 6 se realizó sin credenciales de BD reales: los metadatos de passkeys se construyeron con un AAGUID conocido del catálogo de `laravel/passkeys`, sin registrar credenciales WebAuthn.
- La accesibilidad se evaluó por lectura de código y luego se confirmó en navegador con axe/Lighthouse y teclado; no se usó un lector de pantalla dedicado.

<a id="limitaciones"></a>

## Limitaciones
- Sin Xdebug/PCOV: no se mide cobertura por líneas.
- Sin Vitest, Playwright ni Dusk: no hay tests de componentes ni E2E automatizados; el flujo login/logout se verificó manualmente en la ronda 3.
- El navegador se usó recién en la ronda 3: se revisaron consola, teclado y axe/Lighthouse. Los viewports 375/768/1366/1920 se cubrieron en la ronda 10 con emulación CDP, no con revisión manual.
- La ronda 5 midió listados con 15.000 registros, pero solo con el servidor integrado monoprocreso: no hay datos de FPM/Apache, MySQL ni carga sostenida.
- `q >= 3` en `/buscar` usa FULLTEXT de MySQL; en SQLite se validó el fallback `LIKE` y el test se reactivó. `MATCH ... AGAINST` sigue NO VERIFICADO.
- La ronda 8 simuló expiración de sesión y CSRF; la pérdida del transporte se cubrió en la ronda 10 con emulación CDP, no con una desconexión física de red.
- La ronda 11 usó `Notification::fake()` y cola `sync`: no se verificó entrega SMTP real ni el render de plantillas en un cliente de correo.
- La ronda 12 operó `migrate:rollback`/`migrate` dentro de la transacción de `RefreshDatabase`; no se usó una BD MySQL ni se verificó el índice FULLTEXT real.
- La ronda 13 contó consultas con `DB::enableQueryLog()` sobre SQLite en memoria; no midió latencia de red ni ejecución con FPM/Apache y MySQL.
- La ronda 14 comprobó middleware con peticiones Pest; no levantó servidor web ni verificó el comportamiento detrás de un proxy real.
- La ronda 15 ejecutó los comandos con `Http::fake()` sobre SQLite; no validó el formato actual de la API real ni su latencia.
- La ronda 16 provocó los throttles con peticiones Pest y caché en memoria; no midió su comportamiento detrás de un servidor web con varios workers.
- La ronda 17 insertó un versículo huérfano con `PRAGMA defer_foreign_keys` dentro de `RefreshDatabase`; ese estado no es alcanzable en producción por la FK.
- La ronda 9 corrió escrituras paralelas contra SQLite con tres servidores: los `500 database is locked` son del driver, no una medición válida de MySQL.
- El log se revisó por coincidencias de texto; no se auditó su contenido completo.

<a id="riesgos-no-cubiertos"></a>

## Riesgos no cubiertos
- Concurrencia real sobre likes, rebibleos y follows en MySQL: **parcialmente cubierta en la ronda 9** con envíos duplicados y dos likes/follows secuenciales sobre SQLite; el sondeo paralelo dio bloqueos del driver, por lo que las condiciones de carrera sobre MySQL siguen **NO VERIFICADAS**.
- Estado del cliente ante envíos duplicados y primera visita Inertia: **cubierto en la ronda 9** con navegador; dos defectos corregidos (BUG-023 y BUG-024).
- Feedback visual y anunciado de guardados/errores: **cubierto en la ronda 10** con CDP; un defecto corregido (BUG-025).
- Passkeys/WebAuthn con authenticator real: **NO VERIFICADO**; solo se revisaron configuración, throttles (ronda 11) y el well-known.
- Correo real y notificaciones entregadas: **NO VERIFICADO**; la ronda 11 cubrió los flujos con fakes sobre la cola `sync`.
- Consultas N+1: **cubierto en la ronda 13** con presupuestos por endpoint; un defecto corregido (BUG-028). La latencia bajo carga real con MySQL sigue **NO VERIFICADA**.
- Contrato de rutas y middleware: **cubierto en la ronda 14** con 5 tests; ejecución detrás de servidor web real sigue **NO VERIFICADA**.
- Límites de validación y ramas de comandos: **cubiertos en la ronda 15** con 22 tests y un defecto corregido (BUG-029). La API externa real sigue **NO VERIFICADA**.
- Paths negativos y throttles reales: **cubiertos en la ronda 16** con 14 tests; sin defectos. Detrás de un servidor web con workers siguen **NO VERIFICADOS**.
- Fronteras finales: **cubiertas en la ronda 17** con 6 tests; sin defectos. La FK real impide el versículo huérfano que se simuló para cubrir el fallback de la portada.
- Comportamiento de la API ante sesiones caducadas y 401/403 a mitad de flujo: **cubierto en la ronda 8** con `curl` y navegador; dos defectos corregidos. La pérdida de red se cubrió en la ronda 10 con emulación CDP; un defecto corregido (BUG-025).
- XSS almacenado/reflejado y mass assignment en endpoints de publicaciones, notas y perfil: **cubierto en la ronda 6** por `tests/QA/SecurityFunctionalTest.php`, sin defectos. Queda como riesgo latente que `user_id` siga siendo *fillable* en `Post`, `Note` y `Favorite`; los tests fallarían si se pasara a `create($request->all())`.
- Integración con la API externa de traducciones: timeouts, reintentos y respuestas 500: **cubierto en la ronda 7** con `Http::fake()`; cuatro defectos corregidos. Después `bible:seed-text` sí se ejecutó contra la API real (formato actual y TLS); latencia y los otros dos comandos siguen sin medirse en condiciones reales.
- Compatibilidad con MySQL real (FULLTEXT, índices, `dropColumn`) más allá de la verificación de migraciones en SQLite; en Fase 5 quedó pendiente el comportamiento de `MATCH ... AGAINST` con relevancia y rank.

<a id="estado-del-árbol"></a>

## Estado del árbol
- Los borrados de `AGENTS.md`, `README.md`, `audit/`, `docs/audit/` y los `audit-*.txt` fueron declarados intencionales por el usuario y **no fueron restaurados**.
- Se eliminaron los artefactos del ciclo anterior que quedaban fuera de `tests/`: `phpunit.qa.xml` y la modificación de `.gitignore`. `.env.testing` también fue eliminado para no conservar credenciales; hoy la BD de QA se define únicamente en `phpunit.xml`.
- Se creó `.env` local (ignorado por git) solo con `APP_KEY` durante la fase de hallazgos; no contiene credenciales de BD. Tras la ronda 2 el build ya funciona sin ese archivo.
- Tests QA: `tests/QA/EnvironmentTest.php`, `tests/QA/AuthSecurityTest.php`, `tests/QA/SocialFeedTest.php`, `tests/QA/BibleNotesFavoritesTest.php`, `tests/QA/BibleReadingSearchTest.php`, `tests/QA/SettingsFrontendIntegrityTest.php`, `tests/QA/FrontendIntegrityRound2Test.php`, `tests/QA/SecurityFunctionalTest.php`, `tests/QA/ExternalApiResilienceTest.php`, `tests/QA/SessionExpiryTest.php`, `tests/QA/ClientStateRaceTest.php`, `tests/QA/ResponsiveFeedbackTest.php`, `tests/QA/MailAndVerificationTest.php`, `tests/QA/DatabaseIntegrityTest.php`, `tests/QA/QueryBudgetTest.php`, `tests/QA/RouteContractTest.php`, `tests/QA/ValidationLimitsTest.php`, `tests/QA/CommandEdgeCasesTest.php`, `tests/QA/NegativePathsTest.php`, `tests/QA/FinalBoundariesTest.php` y `tests/QA/SocialNetworkSimulationTest.php`; además la carpeta `qa/` con los 12 informes.
- Con autorización explícita para corregir código de aplicación se modificaron en la ronda 1 `app/Models/User.php`, `app/Http/Controllers/PostController.php`, `app/Http/Controllers/Bible/SearchController.php`, `app/Http/Controllers/Bible/NoteController.php`, `app/Http/Controllers/Bible/FavoriteController.php` y `app/Services/BibleGraphService.php`, y se creó `app/Concerns/VerseReferenceValidationRules.php`.
- En la ronda 2 se modificaron `vite.config.ts` y componentes/vistas de `resources/js`: `PasswordInput.vue`, `InputError.vue`, `AlertError.vue`, `AppearanceTabs.vue`, `TwoFactorRecoveryCodes.vue`, `TwoFactorSetupModal.vue`, `VerseReferencePicker.vue`, `useConstellationGraph.js`, `Chapter.vue`, `Feed.vue`, `Profile/Show.vue`, `SettingsLayout.vue`, `NavHeader.vue`, `BiblePageShell.vue`, `Welcome.vue`, `Book.vue`, `Search.vue`, `Favorites.vue`, `Notes.vue`, `PostCard.vue`, `ProfileGoalsSection.vue`, `PasskeyItem.vue`, `Login.vue`, `ForgotPassword.vue`, `VerifyEmail.vue`, `TwoFactorChallenge.vue` y las páginas de settings. No se tocó `package.json`.
- Los 10 fallos originales (3 de Fase 2, 1 de Fase 3, 2 de Fase 4, 2 de Fase 5 y 2 de Fase 6) representaban siete causas raíz y ya no fallan: los tests se conservaron activos, sin desactivarlos ni marcarlos como skip.
- Los seis hallazgos restantes (BUG-001, BUG-002 y BUG-010 a BUG-013) también están corregidos y los de accesibilidad se confirmaron en navegador; no se usó lector de pantalla dedicado.
- En la ronda 3 se modificaron `app/Http/Controllers/Bible/TextSearchController.php`, `resources/views/app.blade.php` y componentes/vistas de `resources/js`: `auth/Login.vue`, `auth/Register.vue`, `components/Bible/NavHeader.vue`, `pages/Bible/Graph.vue`, `pages/Bible/Chapter.vue`, `pages/Bible/Search.vue`, `pages/Welcome.vue`, `pages/Community/Feed.vue`, `components/Social/PostCard.vue`, `components/Social/VerseReferencePicker.vue` y `layouts/AuthLayout.vue`. No se tocó `package.json` ni `phpunit.xml`.
- En la ronda 7 se modificaron `app/Console/Commands/SeedBibleText.php` y `app/Console/Commands/SeedBibleCrossReferences.php`: reintentos sin `throw` automático, captura de `ConnectionException`, validación de respuestas y `upsert()` de referencias cruzadas. No se tocó `package.json` ni `phpunit.xml`.
- En la ronda 8 se modificaron `bootstrap/app.php` y `resources/js/app.js`: callback para `HttpException(419)` en visitas Inertia y interceptor global de axios para 401/419, con guardia de navegación única. No se tocó `package.json` ni `phpunit.xml`.
- En la ronda 9 se modificaron `resources/js/app.js` y `resources/js/composables/useFavorites.js`: la primera visita Inertia inicializa el usuario sin descartar notas/favoritos y el alta de favoritos deduplica por referencia en el estado local. No se tocó `package.json` ni `phpunit.xml`.
- En la ronda 10 se modificó `resources/js/app.js`: se importa y monta `ToastContainer` en la raíz Inertia. No se tocó `package.json` ni `phpunit.xml`; los scripts CDP y la BD auxiliar vivieron en `%LOCALAPPDATA%\Temp\opencode`.
- En la ronda 11 se modificaron `app/Http/Controllers/Settings/ProfileController.php` y `app/Providers/FortifyServiceProvider.php`, y se crearon `app/Http/Responses/FailedPasswordResetLinkRequestResponse.php` y `tests/QA/MailAndVerificationTest.php`. No se tocó `package.json` ni `phpunit.xml`.
- La ronda 12 solo agregó `tests/QA/DatabaseIntegrityTest.php`; no se tocó código de aplicación, `package.json` ni `phpunit.xml`.
- La ronda 13 modificó `app/Http/Controllers/PostController.php` y `app/Http/Controllers/ProfileController.php` y agregó `tests/QA/QueryBudgetTest.php`. No se tocó `package.json` ni `phpunit.xml`.
- La ronda 14 solo agregó `tests/QA/RouteContractTest.php`; no se tocó código de aplicación, `package.json` ni `phpunit.xml`.
- La ronda 15 modificó `app/Console/Commands/SeedBibleText.php` y agregó `tests/QA/ValidationLimitsTest.php` y `tests/QA/CommandEdgeCasesTest.php`. No se tocó `package.json` ni `phpunit.xml`.
- La ronda 16 solo agregó `tests/QA/NegativePathsTest.php`; no se tocó código de aplicación, `package.json` ni `phpunit.xml`.
- La ronda 17 solo agregó `tests/QA/FinalBoundariesTest.php`; no se tocó código de aplicación, `package.json` ni `phpunit.xml`.
- La simulación agregó `tests/QA/SocialNetworkSimulationTest.php`: 50 usuarios de 10 iglesias con `mt_srand(20261126)`; genera 235 seguimientos entre miembros (el admin fantasma no sigue ni es seguido), 75 posts raíz, 96 respuestas, 40 rebibleos, 136 me gusta, 8 rupturas y 3 reconciliaciones, y deja un snapshot JSON en `%LOCALAPPDATA%\Temp\opencode\social-network-simulation.json`. Después el rol admin marcó al usuario `admin@red.test` en el seeder, agregó el panel `/admin`, lo convirtió en fantasma (invisible, sin follows, omnisciente) y migró/sembró todo en la BD MySQL dedicada `bible_simulation` sin tocar `.env`; `bible:seed-text spa_rvg` cargó después 66 libros y 31.102 versículos desde la API real.
- No queda ningún hallazgo abierto ni ningún test omitido. Este módulo no agregó commit, push ni PR. El repositorio tiene commits locales creados fuera de estas sesiones de QA: `906da37` y `1ad3d5e` (este último incluye el trabajo de las rondas 1 a 11). Los 12 informes quedaron incluidos en `8850d52`; el rol admin (controller, middleware, migración, panel Vue y seeder) y `tests/QA/AdminRoleTest.php` siguen sin commitear, junto con los ajustes a tests QA preexistentes.
- El caso de búsqueda FULLTEXT de Fase 5 se reactivó con un fallback para SQLite; `MATCH ... AGAINST` sobre MySQL real queda como verificación pendiente, no como fallo de aplicación.
