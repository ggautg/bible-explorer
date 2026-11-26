# 08 — Plan de remediación

Plan propuesto, **no ejecutado**. Esta auditoría no modificó código, migraciones, datos ni configuración de producción. Las acciones se ordenan por riesgo y por dependencia entre tareas.

Leyenda de esfuerzo: **S** = horas; **M** = días; **L** = semana o más (no aparece en este plan).

## Ola 0 — Contención inmediata (24-48 h)

| ID | Acción | Responsable sugerido | Esfuerzo | Criterio de aceptación |
|---|---|---|---|---|
| CFG-001 | Rotar la contraseña de MySQL; reemplazar `README.md:49` por placeholder; purgar `ba55c3f`/`030c9fb` del historial con `git filter-repo`/BFG; avisar a quienes clonaron; crear usuario de aplicación con privilegios mínimos | DevOps + dueño del repo | S (rotación) / M (historial) | `git log -S"<REDACTED>"` sin resultados en el repo reescrito; nueva credencial solo en `.env`/gestor de secretos; ningún informe contiene el valor real |
| CFG-002 | Fijar `APP_DEBUG=false` y `APP_ENV=production` en despliegue; quitar `|| config('app.debug')` de `SeedBibleText.php:120` | Backend + DevOps | S | `php artisan about` muestra `Debug Mode .. DISABLED` en el entorno desplegado; el importador ya no desactiva TLS por debug |
| OPS-001 | Actualizar dependencias npm con `npm audit fix` y revisar diffs; planificar `vue-tsc` 3.3.14 por separado | Frontend | M | `npm audit --omit=dev` y `npm audit` sin avisos `high`/`critical`, o excepciones documentadas |
| CFG-004 | Definir `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=lax`; forzar HTTPS | DevOps | S | Respuesta real con `Set-Cookie: ...; Secure; HttpOnly; SameSite=Lax` |
| OPS-002 | Agregar `composer audit --locked` y `npm audit --audit-level=high --omit=dev` al CI | DevOps | S | El workflow falla cuando se sube una dependencia con aviso alto/crítico |

**Verificación sugerida:** `composer audit --locked`, `npm audit --omit=dev`, `npm audit`, `php artisan about`, y revisión de cabeceras con `curl -I` en el entorno desplegado.

**Precaución:** la rotación de credenciales no se considera completa hasta confirmar que ningún sistema (CI, scripts locales, despliegue) sigue usando la anterior.

## Ola 1 — Exposición y abuso (1-2 semanas)

| ID | Acción | Responsable sugerido | Esfuerzo | Criterio de aceptación |
|---|---|---|---|---|
| CFG-003 | Middleware `AddSecurityHeaders` en grupo `web` o configuración equivalente en servidor; CSP en `report-only` primero | Backend/DevOps | S | Cabeceras presentes en respuesta real; ni un solo `frame` externo; sin regresión visual |
| BE-002 | `throttle:30,1` en las cuatro rutas de búsqueda; validación `q`/`ref` con `max:120`; escapar `%`/`_` en `LIKE` | Backend | S | Tests de 121 caracteres devuelven 422; 31 peticiones en un minuto devuelven 429 |
| BE-003 | Decidir: agregar `verified` al grupo `routes/web.php:23`, o documentar que la verificación es opcional y retirar la feature | Producto + Backend | S | Decisión documentada y cubierta por test de ruta |
| FE-001 | Resetear `useNotes`/`useFavorites` al cerrar sesión o al cambiar `auth.user.id`; alternativamente recarga completa en logout | Frontend | S | Test E2E: cuenta A guarda nota → logout → cuenta B → `/notas` sin datos de A |
| CFG-005 | `LOG_STACK=daily`, `LOG_LEVEL=info`, `LOG_DAILY_DAYS=30`, redacción de bindings sensibles | DevOps | S | Logs rotados, sin correos ni SQL bindings en producción; retención documentada |
| BE-004 | Antes de cualquier deduplicación en producción, volcar filas a tabla de respaldo y hacer `down()` restaurable; envolver `up()` en transacción | Backend + DBA | M | Migración con backup verificada en copia de producción; `rollback` restaura filas |

**Verificación sugerida:** `php artisan test`, `php artisan route:list`, revisión de logs, prueba E2E de cambio de cuenta y prueba de migración sobre un dump reciente.

## Ola 2 — Robustez de datos y operación (2-4 semanas)

| ID | Acción | Responsable sugerido | Esfuerzo | Criterio de aceptación |
|---|---|---|---|---|
| DB-001 | `insertOrIgnore()`/`upsert()` en `SeedBibleCrossReferences`, transacción por capítulo y deduplicación por `MAX(score)` | Backend | S | Ejecutar el comando dos veces sobre copia sin error ni duplicados |
| DB-002 | Claves foráneas a `bible_books(code)` y validación de versículos en el sembrador, o test de integridad que falle con huérfanos | DBA + Backend | M | `orphan_to_book` y `orphan_to_verse` = 0 tras reimportación; test de integridad en CI |
| OPS-003 | Separar `ci:setup` de `deploy:setup`; este último exige `.env` completo y rechaza `APP_DEBUG=true` | DevOps | S | `composer setup` queda documentado como solo CI/desarrollo; deploy falla con plantilla insegura |
| OPS-004 | `npm ci` en setup/CI y chequeo de `git diff --exit-code package-lock.json`; documentar `ignore-scripts=true` | DevOps | S | CI falla si lock y manifiesto difieren |
| BE-001 | Aplicar decisión de privacidad de `me-gusta` y ocultar pestaña cuando `!profile.isOwn` | Backend + Frontend | S | Test de perfil ajeno sin preferencias; UI coherente |
| BE-005 | `->limit(20)` en sugerencias de personas y mover búsqueda a endpoint con throttle | Backend | S | Payload del feed acotado; endpoint nuevo con límite |

**Verificación sugerida:** importadores sobre copia de base, `php artisan migrate:status`, tests de integridad SQL de solo lectura, build y lint.

## Ola 3 — Limpieza y deuda informativa (backlog)

| ID | Acción | Responsable sugerido | Esfuerzo | Criterio de aceptación |
|---|---|---|---|---|
| CFG-006 / OPS-005 | Versionar configuración real de servidor/contenedor con `public/` como document root, o documentar "solo local" | DevOps | M | Artefacto de despliegue reproducible o nota explícita de alcance |
| CFG-007 | `php artisan storage:link` en release; contenido privado en disco `local` | DevOps | S | `php artisan about` muestra link activo en despliegue |
| BE-006 / DB-003 | Confirmar con el equipo y eliminar controladores huérfanos y tablas legacy con backup previo | Backend + DBA | S | 0 referencias en código; migración de borrado con `down()`; backup conservado |
| BE-007 | Mover mapas demo a `config/demo_profiles.php` con flag por entorno; eliminar copias duplicadas | Backend | S | Sin correos fijos en controladores; demo desactivado por default |
| BE-008 | Política de contraseña fuerte siempre; test explícito de reglas de producción; documentar dependencia de HIBP | Backend | S | Test de política pasa con `APP_ENV=testing` y con env forzado a production |
| DB-004 | `CHECK`/`ENUM` para `posts.kind` o mutador que normalice valores | Backend + DBA | S | Insert directo con valor inválido rechazado |
| FE-002 | `try/catch` + toast + reversión de cambios optimistas; interceptor central de axios | Frontend | S | 401/422/500 muestran mensaje y no dejan estado falso |
| FE-003 | Patrón `requestId` en `loadStructure()` | Frontend | S | Cambio rápido de libro no apaga el indicador prematuramente |

## Criterios generales de cierre

1. No quedarán hallazgos `Alta` o `Media` sin una decisión documentada de aceptación de riesgo.
2. Cada corrección tendrá al menos un test o una comprobación reproducible en el mismo informe de cambio.
3. Ninguna corrección destructiva (purga de historial, borrado de tablas, migraciones con `DELETE`) se ejecutará sin backup y verificación previa.
4. Se volverán a ejecutar: `php artisan test`, Pint, PHPStan, ESLint, Prettier, `vue-tsc`, `npm run build`, `composer audit --locked` y `npm audit`.
5. Se documentarán los `NO VERIFICADO` que sigan abiertos, con responsable y fecha prevista.

## Orden recomendado de ejecución (resumen)

```
CFG-001 → CFG-002 → OPS-001 → CFG-004 → OPS-002
        ↓
CFG-003 → BE-002 → BE-003 → FE-001 → CFG-005 → BE-004
        ↓
DB-001 → DB-002 → OPS-003 → OPS-004 → BE-001 → BE-005
        ↓
CFG-006/OPS-005 → CFG-007 → BE-006/DB-003 → BE-007 → BE-008 → DB-004 → FE-002 → FE-003
```

No se ejecutó ninguno de estos comandos de remediación durante la auditoría.
