# 07 — Matriz de riesgos

Tabla de referencia de los 27 hallazgos. La probabilidad y el impacto son cualitativos y se basan en evidencia local; no se inventaron puntajes numéricos ni datos de producción.

## Conteo de referencia

| Severidad | Cantidad | IDs |
|---|---:|---|
| Crítica | 0 | — |
| Alta | 3 | CFG-001, CFG-002, OPS-001 |
| Media | 11 | CFG-003, CFG-004, CFG-005, BE-001, BE-002, BE-003, BE-004, DB-001, FE-001, OPS-002, OPS-003 |
| Baja | 7 | CFG-007, BE-005, BE-007, DB-002, DB-003, FE-002, OPS-004 |
| Informativa | 6 | CFG-006, BE-006, BE-008, DB-004, FE-003, OPS-005 |
| **Total** | **27** | |

## Mapa de calor cualitativo

| Impacto \ Probabilidad | Baja | Media | Alta |
|---|---|---|---|
| **Alto** | BE-004, OPS-003 | CFG-001, CFG-002, BE-002, OPS-001 | — |
| **Medio** | CFG-007, DB-001, DB-002, BE-008 | CFG-003, CFG-004, CFG-005, BE-001, BE-003, BE-005, FE-001, OPS-002 | — |
| **Bajo** | BE-007, DB-003, DB-004, FE-002, FE-003, OPS-004 | — | — |

Los hallazgos Informativos sin probabilidad útil (CFG-006, BE-006, OPS-005) se listan igual en la tabla detallada.

## Tabla detallada

| ID | Sev. | Capa | Hallazgo y evidencia (`archivo:línea`) | Prob. | Impacto | OWASP / CWE | Esf. | Estado | Doc. |
|---|---|---|---|---|---|---|---|---|---|
| CFG-001 | Alta | Config | Credencial de BD en `README.md:49`: `DB_PASSWORD=<REDACTED>`; también en historial | Media | Alto | A07 / CWE-798 | S | Local verificado; visibilidad remota `NO VERIFICADO` | `02` |
| CFG-002 | Alta | Config | Debug + TLS: `SeedBibleText.php:120-121` usa `if (app()->environment('local') || config('app.debug'))` | Media | Alto | A05, A08 / CWE-489 | S | Local verificado; prod `NO VERIFICADO` | `02` |
| OPS-001 | Alta | Dependencias | `package-lock.json:2471` → `axios@1.18.1`; `npm audit`: 22 avisos (2 critical) | Alta | Alto | A06 / CWE-1104 | M | Árbol local verificado; prod `NO VERIFICADO` | `06` |
| CFG-003 | Media | Config | `bootstrap/app.php:22-26` solo registra 3 middlewares `web`; sin headers | Alta | Medio | A05 / CWE-693 | S | Ausencia en código verificada; headers reales `NO VERIFICADO` | `02` |
| CFG-004 | Media | Config | `config/session.php:172`: `'secure' => env('SESSION_SECURE_COOKIE')`; `.env.example:32` → `SESSION_ENCRYPT=false` | Media | Medio | A02, A05 / CWE-614 | S | Config local verificada; prod `NO VERIFICADO` | `02` |
| CFG-005 | Media | Config | `.env.example:21` → `LOG_LEVEL=debug`; `storage/logs/laravel.log` con correo censurado y binding SQL | Alta | Medio | A09 / CWE-532 | S | Local verificado; rotación real `NO VERIFICADO` | `02` |
| BE-001 | Media | Backend | `ProfileController.php:36-39`: `$likedIds = $user->likedPosts()->pluck('posts.id')` sin comparar `isOwn` | Media | Medio | A01 / CWE-359 | S | Código verificado; intención de producto `NO VERIFICADO` | `03` |
| BE-002 | Media | Backend | `routes/web.php:55-57,70`: rutas de búsqueda sin `throttle`; `TextSearchController.php:30-32` solo exige mínimo | Media | Alto | A04 / CWE-770 | S | Código verificado; carga real `NO VERIFICADO` | `03` |
| BE-003 | Media | Backend | `routes/web.php:23` → `middleware('auth')`; `config/fortify.php:166` habilita verificación | Media | Medio | A07 / CWE-287 | S | Código verificado; decisión de producto `NO VERIFICADO` | `03` |
| BE-004 | Media | Backend | `2026_11_26_000001...php:78-85` borra duplicados; `down()` en `:95-113` no restaura | Baja | Alto | A04 / CWE-664 | M | Código verificado; duplicados en prod `NO VERIFICADO` | `03`, `04` |
| DB-001 | Media | Base de datos | `SeedBibleCrossReferences.php:118-119`: `DB::table('bible_cross_references')->insert($rows)` | Media | Medio | A04 / CWE-664 | S | Índice local verificado; reintento en prod `NO VERIFICADO` | `04` |
| FE-001 | Media | Frontend | `useNotes.js:7-9`: singletons `notes`/`loaded`; `UserMenuContent.vue:20-22` → `router.flushAll()` | Media | Medio | A01 / CWE-488 | S | Código verificado; repro en navegador `NO VERIFICADO` | `05` |
| OPS-002 | Media | DevOps | `.github/workflows/tests.yml:37-38` → `run: composer ci:check`; sin `audit` | Alta | Medio | A06 / CWE-1104 | S | Definición verificada; ejecución real `NO VERIFICADO` | `06` |
| OPS-003 | Media | DevOps | `composer.json:49-52` copia `.env.example` y ejecuta `migrate --force` | Baja | Alto | A05 / CWE-16 | S | Archivos verificados; uso en despliegue `NO VERIFICADO` | `06` |
| CFG-007 | Baja | Config | `php artisan about`: `public/storage .. NOT LINKED`; `config/filesystems.php:41-45` → `visibility => public` | Baja | Medio | A05 / CWE-16 | S | Local verificado; permisos reales `NO VERIFICADO` | `10` |
| BE-005 | Baja | Backend | `PostController.php:56-59`: `->get(['id', 'name', 'email', 'slug', 'church'])` sin `limit()` | Media | Medio | A04 / CWE-770 | S | Código verificado; volumen real `NO VERIFICADO` | `03` |
| BE-007 | Baja | Backend | `User.php:183-196`: array `$known` con correos `*.example.test` quemados | Baja | Bajo | CWE-1188 | S | Código verificado; uso real `NO VERIFICADO` | `03` |
| DB-002 | Baja | Base de datos | `create_bible_cross_references_table.php:16-25` sin `foreign()`; 0 huérfanos en local | Baja | Medio | A04 / CWE-664 | M | Esquema y datos locales verificados | `04` |
| DB-003 | Baja | Base de datos | `create_posts_table.php:108-121` crea `legacy_comment_post_map`; 0 filas en local | Baja | Bajo | CWE-1164 | S | Local verificado; prod `NO VERIFICADO` | `04` |
| FE-002 | Baja | Frontend | `useNotes.js:24-30`: `try ... finally` sin `catch` | Media | Bajo | A04 / CWE-390 | S | Código verificado; frecuencia real `NO VERIFICADO` | `05` |
| OPS-004 | Baja | DevOps | `composer.json:52` → `"npm install"`; `.npmrc:1` → `ignore-scripts=true` | Media | Bajo | CWE-1104 | S | Config verificada; CI Linux `NO VERIFICADO` | `06` |
| CFG-006 | Informativa | Config | Sin `Dockerfile`/`nginx.conf`; `README.md:93-95` documenta `php artisan serve` | N/A | Bajo | A05 / CWE-16 | M | Ausencia verificada; servidor real `NO VERIFICADO` | `01`, `10` |
| BE-006 | Informativa | Backend | `routes/web.php:25` usa `PostController@index`; 0 rutas hacia `CommunityFeedController` | N/A | Bajo | CWE-1164 | S | Verificado en 72 rutas locales | `03` |
| BE-008 | Informativa | Backend | `AppServiceProvider.php:54-61`: política fuerte solo con `app()->isProduction()` | Baja | Medio | A07 / CWE-521 | S | Código verificado; HIBP en prod `NO VERIFICADO` | `03` |
| DB-004 | Informativa | Base de datos | `add_kind_to_posts_table.php:18`: `$table->string('kind', 24)->default('reflexion')` | Baja | Bajo | CWE-20 | S | Esquema verificado; inserts externos `NO VERIFICADO` | `04` |
| FE-003 | Informativa | Frontend | `VerseReferencePicker.vue:178-180`: `finally { loadingStructure.value = false; }` sin `requestId` | Media | Bajo | CWE-362 | S | Código verificado | `05` |
| OPS-005 | Informativa | DevOps | `composer.json:55-58` → `"php artisan serve"`; 0 archivos de despliegue versionados | N/A | Bajo | CWE-16 | M | Ausencia verificada; prod `NO VERIFICADO` | `06` |

## Criterios usados

- **Probabilidad Alta:** la condición está presente en cada ejecución o en cada instalación (por ejemplo, ausencia de headers, CI sin auditoría).
- **Probabilidad Media:** requiere una acción de una persona usuaria autenticada o un cambio de configuración.
- **Probabilidad Baja:** requiere acceso administrativo, un error operativo o una combinación de circunstancias.
- **Impacto Alto:** exposición de credenciales/secretos, control de base de datos, pérdida de datos o carga sostenida significativa.
- **Impacto Medio:** privacidad de terceras personas, abuso funcional o degradación moderada.
- **Impacto Bajo:** calidad, mantenibilidad o percepción de interfaz sin pérdida de datos.
- **Esfuerzo S:** horas; **M:** días; no se estimó ningún trabajo mayor (L) en esta auditoría.
