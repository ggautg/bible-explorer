# 08 — Rendimiento y compatibilidad

## Índice
- [Tiempos medidos](#tiempos-medidos)
- [Medición en navegador (ronda 4)](#medición-en-navegador-ronda-4)
- [Volúmenes y bundle](#volúmenes-y-bundle)
- [Compatibilidad y responsive](#compatibilidad-y-responsive)
- [Carga (ronda 5)](#carga-ronda-5)
- [Limitaciones](#limitaciones)

<a id="tiempos-medidos"></a>

## Tiempos medidos
| Operación | Tiempo | Condiciones |
| --- | ---: | --- |
| Suite principal (54 tests) | 2,7 s | SQLite `:memory:`, BCRYPT_ROUNDS=4, medición final |
| Suite QA de entorno (2 tests) | 0,24 s | Sin peticiones HTTP |
| Suite QA completa (77 tests) | 3,5 s | 77 pasan, 0 fallos, 0 omitidos, 449 aserciones (tras las tres rondas de corrección) |
| Suite QA completa (85 tests) | 3,4 s | 85 pasan, 0 fallos, 0 omitidos, 488 aserciones (tras la ronda 6) |
| Suite QA completa (93 tests) | 5,9 s | 93 pasan, 0 fallos, 0 omitidos, 515 aserciones (tras la ronda 7; los reintentos simulados suman ~500 ms) |
| Suite QA completa (98 tests) | 6,3 s | 98 pasan, 0 fallos, 0 omitidos, 527 aserciones (tras la ronda 8) |
| Suite QA completa (103 tests) | 5,9 s | 103 pasan, 0 fallos, 0 omitidos, 542 aserciones (tras la ronda 9) |
| Suite QA completa (107 tests) | 6,1 s | 107 pasan, 0 fallos, 0 omitidos, 550 aserciones (tras la ronda 10) |
| Suite QA completa (117 tests) | 7,4 s | 117 pasan, 0 fallos, 0 omitidos, 625 aserciones (tras la ronda 11) |
| Suite QA completa (133 tests) | 7,5 s | 133 pasan, 0 fallos, 0 omitidos, 697 aserciones (tras la ronda 12) |
| Suite QA completa (140 tests) | 8,0 s | 140 pasan, 0 fallos, 0 omitidos, 733 aserciones (tras la ronda 13) |
| Suite QA completa (145 tests) | 8,5 s | 145 pasan, 0 fallos, 0 omitidos, 775 aserciones (tras la ronda 14) |
| Suite QA completa (167 tests) | 8,8 s | 167 pasan, 0 fallos, 0 omitidos, 914 aserciones (tras la ronda 15) |
| Suite QA completa (181 tests) | 9,2 s | 181 pasan, 0 fallos, 0 omitidos, 1139 aserciones (tras la ronda 16) |
| Suite QA completa (187 tests) | 9,3 s | 187 pasan, 0 fallos, 0 omitidos, 1214 aserciones (tras la ronda 17) |
| Suite QA completa (198 tests) | 10,1 s | 198 pasan, 0 fallos, 0 omitidos, 1363 aserciones (con la simulación de red, el rol admin y el modo fantasma) |
| Tests del feed con 24 personas antes del fix | 9,5 s | 23 consultas por visita (BUG-028) |
| Tests del feed con 24 personas tras el fix | 0,7 s | Consultas constantes, 7 tests de presupuesto en verde |
| Suite QA de Fase 6 (14 tests) | 0,7 s | 12 pasaban y 2 fallaban antes de la corrección de BUG-009; ahora 14 pasan |
| `migrate:fresh --seed` | < 1 s | 23 migraciones + seeder mínimo |
| `migrate:reset` | ~0,1 s | 23 migraciones en archivo SQLite temporal |
| `npm run build` | 2,17 s | Node 26.13.0, sin `.env`, sin regeneración Wayfinder (tras la ronda 3) |

<a id="medición-en-navegador-ronda-4"></a>

## Medición en navegador (ronda 4)
Mismo servidor y BD temporal de la ronda 3, Chromium integrado, `reload` con caché falsa. Dos mediciones complementarias: `PerformanceNavigationTiming` (carga de documento) y trace del renderer (tareas largas y eventos). No es prueba de carga ni mide Web Vitals.

| Página | `responseEnd` | DCL | `loadEventEnd` | Transferencia | Tareas largas (trace) |
| --- | ---: | ---: | ---: | ---: | ---: |
| `/` (invitado) | 241 ms | 343 ms | 344 ms | 8 KB | No medido en esa corrida |
| `/login` | 207 ms | 297 ms | 297 ms | 8 KB | 0 |
| `/comunidad` | 271 ms | 363 ms | 363 ms | ~8 KB | 1 (3,2 ms de bloqueo) |
| `/leer/GEN/1` | 784 ms | 896 ms | 896 ms | 8 KB | 0 (evento máximo 29 ms) |
| `/bible/GEN/1/1` | 331 ms | 426 ms | 426 ms | 8 KB | 0 (evento máximo 34 ms) |
| `/buscar?q=Dios` | 221 ms | 310 ms | 311 ms | 8 KB | 0 (evento máximo 47 ms) |
| `/notas` | 345 ms | 506 ms | 507 ms | 8 KB | 0 (evento máximo 49 ms) |

Las trazas completas duraron 394–639 ms y no quedaron incompletas. `/leer/GEN/1` mostró el valor más alto en una única medición: conviene repetirlo antes de concluir que es una regresión. El evento más caro de `/comunidad` fue una llamada de 42,7 ms dentro de la única tarea larga registrada. Para el resto, ningún evento superó los 50 ms.

<a id="volúmenes-y-bundle"></a>

## Volúmenes y bundle
| Métrica | Valor |
| --- | ---: |
| Assets generados | 77 archivos |
| Tamaño total de `public/build/assets` | 962.035 bytes (0,92 MB) |
| Asset más grande | `createLucideIcon-*.js` 153,57 kB (gzip 49,29 kB) |
| Registros sembrados por `DatabaseSeeder` | 1 usuario de prueba |
| Volumen de bible_verses / posts en testing | 0 corpus real; Fase 5 creó fixtures mínimas por test (5 versículos base + hasta 30 para el límite del grafo). La ronda 5 agregó 15.000 versículos sintéticos en un SQLite temporal separado |

El seeder es deliberadamente mínimo; los comandos `SeedBibleText` y `SeedBibleCrossReferences` descargan datos externos y no se ejecutaron para no depender de la red.

<a id="compatibilidad-y-responsive"></a>

## Compatibilidad y responsive
| Ítem | Estado | Detalle |
| --- | --- | --- |
| Chromium | **VERIFICADO** (ronda 3) | Navegador integrado sobre `artisan serve`; once páginas con Lighthouse 1,0/1,0/1,0 |
| Firefox / WebKit | **NO VERIFICADO** | Sin esos motores disponibles en el entorno |
| 375 px (móvil) | **VERIFICADO** (ronda 10) | CDP `setDeviceMetricsOverride`: sin overflow; toggle del menú abre 9 enlaces y se cierra al navegar |
| 768 px (tablet) | **VERIFICADO** (ronda 10) | CDP: `scrollWidth <= innerWidth` en las 9 páginas revisadas |
| 1366 px (laptop) | **VERIFICADO** (ronda 10) | CDP: `scrollWidth <= innerWidth` en las 9 páginas revisadas |
| 1920 px (escritorio) | **VERIFICADO** (ronda 10) | CDP: `scrollWidth <= innerWidth` en las 9 páginas revisadas |
| Impresión / PDF / exportaciones | **NO VERIFICADO** | No hay funcionalidad declarada |
| axe / Lighthouse | **VERIFICADO** (ronda 3) | Sin fallos en once páginas; accesibilidad, buenas prácticas y SEO 1,0 |
| Lector de pantalla dedicado | **NO VERIFICADO** | Se revisó el árbol accesible del navegador, no NVDA/VoiceOver |
| Búsqueda FULLTEXT en MySQL 8.0.35 | **NO VERIFICADO** | `MATCH ... AGAINST` no se ejecuta en SQLite; se validó solo el fallback `LIKE` |
| Carga ligera con 15.000 versículos | **VERIFICADO** (ronda 5) | 170/170 respuestas 200, sin 500 ni timeouts; servidor monoprocreso, no es un techo de capacidad |
| Carga sostenida / servidor con workers | **NO VERIFICADO** | Requiere FPM o Apache y MySQL con volumen representativo |
| Pérdida de red a mitad de flujo | **VERIFICADO** (ronda 10) | CDP `emulateNetworkConditions`: `ERR_INTERNET_DISCONNECTED`, sin estado pendiente y reintento exitoso |
| Correo y notificaciones | **VERIFICADO** (ronda 11) | `Notification::fake()` con cola `sync`: registro, cambio de correo, reenvíos, reset y consumo de códigos 2FA |
| Servidor SMTP real | **NO VERIFICADO** | El entorno de tests usa `MAIL_MAILER=array`; no se probó entrega real ni plantillas en un cliente de correo |
| WebAuthn con authenticator real | **NO VERIFICADO** | Solo se comprobó el throttle por credencial (ronda 11) y el well-known (ronda 6) |
| Rollback y backfill de migraciones | **VERIFICADO** (ronda 12) | 23 migraciones revertidas y reaplicadas, con constraints, cascadas y datos legacy comprobados por SQL directo |
| Presupuesto de consultas | **VERIFICADO** (ronda 13) | Conteo de queries en feed, notas/favoritos, capítulo, grafo y búsqueda; un N+1 corregido (BUG-028) |
| Latencia con FPM/Apache y MySQL | **NO VERIFICADO** | Solo se midió el servidor integrado monoprocreso y el conteo de queries en tests |
| Contrato de rutas y middleware | **VERIFICADO** (ronda 14) | 9 throttles, redirecciones de invitados, `verified` y confirmación de contraseña comprobados con Pest |
| Límites y validaciones de entrada | **VERIFICADO** (ronda 15) | Topes de longitud, referencias y listados comprobados por HTTP; sin defectos nuevos |
| Ramas de comandos con API simulada | **VERIFICADO** (ronda 15) | `seed-text`, `seed-crossrefs` y `list-spanish` con `Http::fake()`; un defecto corregido (BUG-029) |
| Paths negativos y throttles reales | **VERIFICADO** (ronda 16) | Escapes, referencias inválidas, guardas de traducción, rebibleos, autenticación y 429 por límite; sin defectos nuevos |
| Fronteras finales | **VERIFICADO** (ronda 17) | Escape de `%`/`\`, referencias parciales, ranking del grafo, portada y pestañas; sin defectos nuevos |

<a id="carga-ronda-5"></a>

## Carga (ronda 5)
Se sembraron **15.000 versículos sintéticos** en un SQLite temporal fuera del repo (`bible-explorer-load.sqlite`), con 3 libros × 100 capítulos × 50 versículos y texto que incluye “Dios” cada quinto versículo. No se usaron datos reales ni la BD de desarrollo. Se levantó un segundo `artisan serve` en el puerto 8001 y se hicieron peticiones concurrentes con `curl_multi` desde un script PHP temporal (sin instalar `ab`, `wrk` ni `k6`).

**Carga ligera** — 5 concurrentes × 10 peticiones por ruta (50 total), sesión autenticada:

| Ruta | 200 | Min | Promedio | p95 | Máx |
| --- | ---: | ---: | ---: | ---: | ---: |
| `/comunidad` | 10 | 246 ms | 730 ms | 1212 ms | 1212 ms |
| `/leer/GEN/1` | 10 | 241 ms | 772 ms | 1276 ms | 1276 ms |
| `/buscar?q=Dios` | 10 | 223 ms | 686 ms | 1155 ms | 1155 ms |
| `/bible/GEN/1/1` | 10 | 223 ms | 660 ms | 1114 ms | 1114 ms |
| `/notas` | 10 | 202 ms | 676 ms | 1183 ms | 1183 ms |

**Pico corto** — 20 concurrentes × 40 peticiones por ruta (120 total):

| Ruta | 200 | Min | Promedio | p95 | Máx |
| --- | ---: | ---: | ---: | ---: | ---: |
| `/buscar?q=Dios` | 40 | 221 ms | 2389 ms | 4375 ms | 4804 ms |
| `/leer/GEN/1` | 40 | 209 ms | 2232 ms | 4149 ms | 4494 ms |
| `/comunidad` | 40 | 220 ms | 2358 ms | 4299 ms | 4549 ms |

Sin errores: 170/170 respuestas 200, ningún 500 ni timeout. La subida brusca de latencia al pasar de 5 a 20 concurrentes refleja que **el servidor integrado de PHP en Windows es monoprocreso**: las peticiones se encolan, no se ejecutan en paralelo real. Por lo tanto estos números no son un techo de capacidad ni un SLA; solo confirman que no hay fallos funcionales con 15.000 filas y concurrencia moderada. La búsqueda `LIKE` sobre SQLite escanea las 15.000 filas sin degradarse más que las demás rutas.

Sigue **NO VERIFICADO**: rendimiento bajo servidor web real (FPM/Apache) con varios workers, MySQL con FULLTEXT sobre corpus grande, y sostenibilidad prolongada (más de unos segundos).

<a id="limitaciones"></a>

## Limitaciones
- Sin Xdebug/PCOV: no hay cobertura por líneas, solo la matriz de `06-matriz-de-cobertura.md`.
- Las medidas de tests corresponden a una ejecución única; no se promediaron varias corridas.
- La ronda 4 midió carga de documento y eventos del renderer, pero no LCP, INP, CLS, memoria ni consumo de CPU sostenido.
- Los tiempos son mediciones únicas: solo `/leer/GEN/1` pareció atípico (896 ms) y no se repitió lo suficiente para promediarlo.
- La revisión de accesibilidad de Fase 6 no medía render; la ronda 3 corroboró accesibilidad, la ronda 4 agregó perfilado ligero y la ronda 5 cubrió carga con volumen sintético.
- La carga se midió con el servidor integrado de PHP en Windows, que atiende una petición a la vez: el aumento de latencia con 20 concurrentes es encolamiento, no degradación de la aplicación. No se midió con FPM/Apache ni MySQL.
- El tamaño del bundle incluye los assets generados por Vite antes de restaurar los artefactos de Wayfinder; la restauración no cambia el bundle.
