# Auditoría de seguridad

## Alcance

Autenticación Fortify, autorización de rutas, validación de entrada, salida HTML, límites de frecuencia, dependencias y configuración de transporte. No se desactivaron controles de seguridad durante la auditoría.

## Verificado

| Control | Evidencia | Resultado |
| --- | --- | --- |
| Rutas del área social y bíblica tras `auth` | `routes/web.php` (grupo `Route::middleware('auth')`) | Todas las escrituras sociales y APIs personales requieren sesión. |
| Protección CSRF | Middleware estándar Laravel activo; tests de interacción usan sesión | Correcto. |
| Validación de favoritos/notas | `FavoriteController::store`, `NoteController::upsert` | `book_code`, `chapter` y `verse` validados; la referencia debe existir en `spa_rvg`. |
| Derivación de metadatos | `App\Services\VerseMetadata` | `book_name` y `text` ya no se toman del cliente. |
| Contraseñas fuertes en producción | `AppServiceProvider::configureDefaults()` | `Password::min(12)` con reglas cuando `app()->isProduction()`. |
| Comandos destructivos | `DB::prohibitDestructiveCommands(app()->isProduction())` | Bloqueados en producción. |
| Límites de frecuencia | `routes/web.php`: `throttle:15,1` social, `throttle:30,1` bíblico | Test de regresión verifica HTTP 429. |
| Aislamiento de secretos | `.env` local gitignored | No se versionaron credenciales; no se reproducen en estos documentos. |

## Corregido

1. **Datos manipulables del cliente**: favoritos y notas aceptaban `book_name` y `text` arbitrarios. Ahora se derivan desde `bible_verses`/`bible_books`; una referencia inexistente devuelve 422. Tests: `BibleToolsIntegrityTest`.
2. **Toggles no atómicos**: like, seguir y reacciones usaban `exists()` seguido de escritura. Ahora borrar + `insertOrIgnore()` apoyados en claves únicas. Test: `SocialInteractionsTest`.
3. **Abuso de endpoints de escritura**: se agregaron throttles a posts, respuestas, like, rebibleo, seguir y APIs de favoritos/notas.
4. **Rollback incompleto**: la migración de `score`/`range` tenía `down()` vacío; ahora revierte ambas columnas.
5. **QR 2FA con `v-html`**: se reemplazó por un `<img>` con `data:image/svg+xml`; el SVG nunca se interpreta como HTML.
6. **`flashToast` sin lista blanca**: `resources/js/lib/flashToast.js` valida `data.type` y cae a `info` si el tipo no es conocido.
7. **Rebibleos duplicables**: se deduplicaron registros históricos y se creó `unique(user_id, rebible_of_id)`; el toggle usa `insertOrIgnore()`.
8. **Dependencias con avisos**: se actualizaron todas las dependencias dentro de las restricciones de `composer.json` (Laravel 13.20 → 13.44). `composer audit` quedó sin avisos; solo informa 2 paquetes abandonados.

## No corregido / riesgo conocido

| Hallazgo | Severidad | Detalle |
| --- | --- | --- |
| TLS de MySQL | Informativo | `config/database.php` expone `MYSQL_ATTR_SSL_CA` y `DB_SSLMODE=prefer`; no se verificó el valor real en producción. |
| Paquetes abandonados | Informativo | `dragon-code/contracts` y `dragon-code/support`; el primero no tiene reemplazo y el segundo sugiere `illuminate/support`. No se removieron porque no se usan directamente. |

## No verificable

- Exposición real de errores/depuración en producción (`APP_DEBUG`, cabeceras, logs): requiere entorno de producción.
- Permisos reales de la cuenta MySQL y usuarios con roles: no hay capa de roles en el código revisado.
- Cabeceras de seguridad HTTP (CSP, HSTS, X-Frame-Options): no se inspeccionó el servidor web.
- Render del QR ya corregido: no se validó visualmente en navegador.

## Estado de la recomendación prioritaria

La actualización de dependencias se aplicó y se validó con la suite completa (46/46), `phpstan`, `pint`, lint/format, `vue-tsc` y build. Queda pendiente repetir la verificación en el entorno de despliegue. Ver `RECOMMENDATIONS.md`.
