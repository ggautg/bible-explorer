# 10 — Anexos

Material de soporte: metodología, comandos ejecutados, instantánea del entorno, métricas de datos, vacíos de verificación y glosario.

## A. Metodología

1. **Fase 0 — Reconocimiento:** versiones, rutas, middleware, dependencias y terceros. Se mostró el resumen antes de continuar.
2. **Fase 1 — Configuración e infraestructura:** `.env.example`, `config/`, `bootstrap/app.php`, logging, sesión, filesystem y ausencia de servidor versionado.
3. **Fase 2 — Backend Laravel:** rutas, controladores, modelos, validación, autorización, serialización y migraciones.
4. **Fase 3 — Base de datos:** esquema, migraciones aplicadas, índices, claves foráneas, conteos y consultas de integridad en MySQL local de solo lectura.
5. **Fase 4 — Frontend Vue/Inertia:** composables, componentes, rutas del cliente, manejo de errores y estado compartido.
6. **Fase 5 — Dependencias y DevOps:** manifiestos, lockfiles, `composer audit`, `npm audit`, workflow de CI y reproducibilidad.
7. **Consolidación:** los 11 documentos de `audit/`, con la matriz de riesgos como tabla de referencia.

Reglas aplicadas:

- Todo hallazgo lleva `archivo:línea` y un fragmento citado. Sin evidencia, no se reporta como hallazgo.
- Lo que no se pudo comprobar se marca `NO VERIFICADO` y se explica qué acceso falta.
- No se inventaron datos de producción, cabeceras, permisos ni métricas de carga.
- No se reprodujo ninguna credencial real; los valores sensibles se muestran como `<REDACTED>`.
- Identificadores y fragmentos de código en inglés; explicaciones en español rioplatense.

## B. Comandos y consultas ejecutados (solo lectura)

### Entorno y framework

```
php -v
node -v
php artisan about
php artisan route:list --json
php artisan migrate:status
php artisan test
composer audit
composer show laravel/framework
composer show inertiajs/inertia-laravel
composer show laravel/fortify
npm audit --json
npm audit --omit=dev --json
npm ls vue axios vite concurrently --depth=0
npm run lint:check
npm run format:check
npm run types:check
npm run build
./vendor/bin/pint --dirty --test
phpstan analyse
```

### Calidad y búsquedas

```
git log --all --oneline -S"<REDACTED>"
git ls-files
grep -rn "v-html|innerHTML|eval|document.write|window.open|target=\"_blank\"" resources/js
grep -rn "X-Frame-Options|Content-Security-Policy|Strict-Transport-Security|Referrer-Policy|X-Content-Type-Options" app/ config/
```

### MySQL local

Se usó el cliente `mysql.exe` de MySQL 8.0.35. La contraseña se leyó desde `.env` en memoria para la invocación; **no se imprimió ni se copió a estos informes**.

```
SELECT translation, COUNT(*) AS n FROM bible_verses GROUP BY translation;
SELECT COUNT(*) FROM bible_books;
SELECT COUNT(*) FROM bible_cross_references;
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM posts;
SELECT COUNT(*) FROM legacy_comment_post_map;
SELECT COUNT(*) FROM profile_comments;
SELECT COUNT(*) FROM profile_comment_reactions;
SHOW INDEX FROM bible_verses;
SHOW INDEX FROM posts;
SHOW INDEX FROM bible_cross_references;
SHOW INDEX FROM favorites;
SHOW INDEX FROM notes;
SHOW INDEX FROM sessions;
SELECT @@sql_mode, @@character_set_database, @@collation_database;
SELECT table_name, engine, table_collation FROM information_schema.tables WHERE table_schema=DATABASE();
SELECT COUNT(*) AS orphan_to_book
  FROM bible_cross_references bcr
  LEFT JOIN bible_books b ON b.code=bcr.to_book
  WHERE b.code IS NULL;
SELECT COUNT(*) AS orphan_to_verse
  FROM bible_cross_references bcr
  LEFT JOIN bible_verses v
    ON v.book_code=bcr.to_book AND v.chapter=bcr.to_chapter
   AND v.verse=bcr.to_verse AND v.translation='spa_rvg'
  WHERE v.id IS NULL;
SELECT COUNT(*) AS orphan_post_reference
  FROM posts p
  LEFT JOIN bible_verses v
    ON v.book_code=p.book_code AND v.chapter=p.chapter
   AND v.verse=p.verse AND v.translation='spa_rvg'
  WHERE p.book_code IS NOT NULL AND v.id IS NULL;
```

No se ejecutó `EXPLAIN`, `ANALYZE TABLE`, ni ninguna sentencia de escritura sobre MySQL durante la auditoría.

## C. Instantánea del entorno

| Elemento | Valor |
|---|---|
| PHP | 8.3.36 |
| Laravel | 13.44.1 |
| Inertia Laravel | 3.8.0 |
| Fortify | 1.44.0 |
| Vue | 3.5.40 instalado (`package.json:51` → `^3.5.13`) |
| Inertia Vue | `^3.0.0` (`package.json:38`) |
| Tailwind CSS | `^4.1.1` (`package.json:49`) |
| Vite | 8.1.5 instalado (`package.json:33` → `^8.0.0`) |
| Node local | 26.13.0 |
| Node en CI | 22 (`.github/workflows/tests.yml:32`) |
| MySQL | 8.0.35, base `bible_explorer` |
| `APP_ENV` / debug local | `local` / `ENABLED` (`php artisan about`) |
| Caché de rutas / vistas | `NOT CACHED` / `CACHED` |
| `public/storage` | `NOT LINKED` |
| `composer audit` | 0 avisos de seguridad; 2 paquetes abandonados informativos |
| `npm audit` | 22 avisos: 2 críticos, 9 altos, 11 moderados |
| `npm audit --omit=dev` | 8 avisos: 7 altos, 1 moderado |
| Tests / aserciones | 52 / 196 |
| Lint, tipos y build | en verde |

## D. Inventario de rutas y datos

- `php artisan route:list --json`: **72 rutas**.
- Grupo autenticado principal: `routes/web.php:23-71`.
- Grupo con `verified`: `routes/settings.php:15-27`.
- Feed real: `routes/web.php:25` → `PostController@index`.
- Sin middleware `throttle`: `routes/web.php:54-58,70` (BE-002).
- Tercero externo único identificado: `bible.helloao.org` (`SeedBibleText.php:42`, `SeedBibleCrossReferences.php:42`).

Instantánea de datos locales (conteos exactos con `COUNT(*)`):

| Tabla | Filas |
|---|---:|
| `bible_books` | 66 |
| `bible_verses` (`spa_rvg`) | 31.102 |
| `bible_cross_references` | 344.799 |
| `users` | 1 |
| `posts` | 5 |
| `follows` | 6 |
| `notes` | 0 |
| `favorites` | 0 |
| `post_likes` | 0 |
| `legacy_comment_post_map` | 0 |
| `profile_comments` | 0 |
| `profile_comment_reactions` | 0 |

Las 5 publicaciones locales son de prueba creadas el 2026-11-26, con tipos `reflexion`, `versiculo`, `peticion_oracion`, `testimonio` y `agradecimiento`. No se modificaron durante la auditoría.

`information_schema.tables.table_rows` mostró 332.626 para `bible_cross_references`; es una estimación del optimizador. Se usó `COUNT(*)` como valor exacto.

## E. Manejo de credenciales

- La credencial real aparece en `README.md:49` y en los commits `ba55c3f` y `030c9fb`. En todos los informes se muestra como `DB_PASSWORD=<REDACTED>`.
- `.env` está gitignored (`.gitignore:19`) y no se versionó.
- Se usaron credenciales reales únicamente para acceso local a MySQL; el valor nunca se escribió en `audit/`.
- No se rotó la credencial: eso corresponde a la Ola 0 del plan (`08-plan-de-remediacion.md`).
- No se consultó la visibilidad del repositorio remoto: `NO VERIFICADO`.

## F. Estado del árbol de trabajo

- Se creó esta carpeta `audit/` en la raíz. No se sobrescribió `docs/audit/`, que contiene una auditoría previa de 9 archivos y se trata como referencia.
- Los cambios de producto existentes siguen sin commitear; no se hizo `push` ni PR.
- Durante la consolidación final no se cambió ningún archivo fuera de `audit/`.
- El backup de la migración social previa queda en `C:\Users\Desarrollo\AppData\Local\Temp\opencode\bible_explorer_backup_20261126_093031.sql`; no se modificó.
- Wayfinder generado durante el build se restauró a `HEAD`.

## G. Lista de `NO VERIFICADO`

| Tema | Qué falta para verificarlo |
|---|---|
| Producción | Acceso al entorno desplegado, variables reales y datos reales |
| Cabeceras HTTP | Respuesta real de un servidor web, no solo código |
| `php.ini` y PHP-FPM | Configuración del servidor o contenedor |
| Permisos de filesystem | Acceso al servidor y a `storage/` |
| Logs en producción | Rotación, retención y centralización reales |
| Backups y recuperación | Política de copias, RTO/RPO y prueba de restauración |
| Monitoreo y alertas | Dashboard, umbrales y canales de aviso |
| Concurrencia y rendimiento | Prueba de carga y `EXPLAIN` sobre búsquedas/grafo |
| Repositorio remoto | Visibilidad pública/privada y permisos de colaboradores |
| Reproducción de FE-001 | Dos cuentas reales compartiendo pestaña de navegador |
| Árbol npm en producción | Bundle/artefacto desplegado y eventual SSR |
| Intención de producto | Privacidad de `me-gusta`, verificación de correo y datos demo |
| Duplicados en producción | Volcado reciente previo a la migración de integridad |
| HIBP | Disponibilidad de la validación `uncompromised()` en producción |

## H. Leyendas

### Severidad

| Valor | Significado usado |
|---|---|
| Crítica | Compromiso directo y confirmado de confidencialidad, integridad o disponibilidad sin intervención del atacante además de su acceso normal |
| Alta | Exposición importante o dependencia con aviso grave; requiere atención antes de exponer el sistema |
| Media | Riesgo real que requiere una acción adicional o una decisión de producto |
| Baja | Degradación limitada, calidad o defensa en profundidad |
| Informativa | Observación de mantenimiento o audibilidad sin impacto explotable confirmado |

### Referencias

- **OWASP A01-A10:** categorías del OWASP Top 10 (2021).
- **CWE-16, 20, 1104, 1164, 1188, 1194, 287, 359, 362, 390, 488, 521, 532, 614, 664, 693, 770, 798, 489:** debilidades citadas en los hallazgos. Los números se usan tal como aparecen en la evidencia; no se agregaron CWE nuevos sin respaldo.

## I. Glosario

| Término | Significado en este informe |
|---|---|
| Rebiblear | Acción de compartir una publicación como rebibleo; genera la etiqueta automática `rebibleada`, no es una opción manual |
| Feed / publicación | "Feed" se mantiene por ser término de producto ya instalado; se prefiere "publicación" para el objeto |
| Singleton del cliente | Variable de módulo compartida por todos los componentes de la pestaña |
| `NO VERIFICADO` | No se cuenta con evidencia suficiente dentro del alcance acordado; no afirma que el problema exista o no exista |
| `SHOW INDEX` | Consulta de solo lectura que confirma índices reales en MySQL |
| `COUNT(*)` | Conteo exacto usado en lugar de estimaciones de `information_schema` |

## J. Índice de entregables

| Archivo | Contenido |
|---|---|
| `00-resumen-ejecutivo.md` | Veredicto, conteos, prioridades y límites |
| `01-arquitectura.md` | Stack, rutas, middleware, fronteras de confianza y despliegue |
| `02-seguridad.md` | Hallazgos de seguridad y mapeo OWASP/CWE |
| `03-backend.md` | BE-001 a BE-008 |
| `04-base-de-datos.md` | DB-001 a DB-004 y métricas MySQL |
| `05-frontend.md` | FE-001 a FE-003 |
| `06-dependencias-devops.md` | OPS-001 a OPS-005 y resultados de auditorías |
| `07-matriz-de-riesgos.md` | Tabla de referencia de 27 hallazgos |
| `08-plan-de-remediacion.md` | Olas de corrección y criterios de aceptación |
| `09-buenas-practicas-encontradas.md` | Controles ya presentes |
| `10-anexos.md` | Este documento |
