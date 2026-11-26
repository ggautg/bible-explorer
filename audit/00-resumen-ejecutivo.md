# 00 — Resumen ejecutivo

**Proyecto:** Explorador Bíblico (`bible-explorer`)
**Fecha de la auditoría:** 2026-11-26
**Alcance acordado:** solo entorno local + marcación explícita de `NO VERIFICADO` para producción, cabeceras reales, permisos, backups, monitoreo, concurrencia y datos remotos.
**Modalidad:** lectura y ejecución de comandos de solo lectura. No se modificaron código, migraciones ni datos del producto; la única escritura en el repositorio es esta carpeta `audit/`.

> Aclaración de avance: en el mensaje de cierre de la Fase 5 se indicó "1 Media"; la consolidación final corrige ese número a **2 Medias en esa capa** (`OPS-002` y `OPS-003`). En el total general hay 11 hallazgos Medios y 27 hallazgos en conjunto.

## Veredicto general

No se encontraron hallazgos **Críticos** confirmados dentro del alcance local. Sí hay **3 hallazgos Altos** que deben atendirse antes de cualquier exposición del proyecto: una credencial de base de datos versionada, depuración activa con una excepción que desactiva verificación TLS, y dependencias npm con avisos de seguridad conocidos (incluidas dependencias de producción).

La aplicación tiene bases sólidas: autorización por usuario, consultas con bindings, serializadores que omiten secretos, índices únicos reales, datos bíblicos íntegros en local, suite de tests en verde y CI con acciones fijadas por SHA. Los riesgos principales son de **configuración, exposición de información y mantenimiento de dependencias**, no de inyección SQL ni de ejecución remota de código confirmada.

## Distribución de hallazgos

| Severidad | Cantidad | IDs |
|---|---:|---|
| Crítica | 0 | — |
| Alta | 3 | CFG-001, CFG-002, OPS-001 |
| Media | 11 | CFG-003, CFG-004, CFG-005, BE-001, BE-002, BE-003, BE-004, DB-001, FE-001, OPS-002, OPS-003 |
| Baja | 7 | CFG-007, BE-005, BE-007, DB-002, DB-003, FE-002, OPS-004 |
| Informativa | 6 | CFG-006, BE-006, BE-008, DB-004, FE-003, OPS-005 |
| **Total** | **27** | Esta tabla es la fuente de verdad; se repite en `07-matriz-de-riesgos.md`. |

Distribución por capa:

| Capa | Hallazgos | Documento |
|---|---:|---|
| Configuración e infraestructura | 7 | `02-seguridad.md` |
| Backend Laravel | 8 | `03-backend.md` |
| Base de datos | 4 | `04-base-de-datos.md` |
| Frontend Vue/Inertia | 3 | `05-frontend.md` |
| Dependencias y DevOps | 5 | `06-dependencias-devops.md` |

## Los 5 riesgos que conviene atender primero

1. **CFG-001 (Alta)** — La credencial real de MySQL está en `README.md:49` y en dos commits del historial. No basta con borrarla del archivo: hay que rotarla y purgar el historial. El valor está censurado en todos los informes.
2. **CFG-002 (Alta)** — `APP_DEBUG=true` en el entorno local y `SeedBibleText.php:120-121` desactiva la verificación TLS cuando `config('app.debug')` es true. En producción eso combina fugas de stack trace con importación sin validación de certificado.
3. **OPS-001 (Alta)** — `npm audit` da 22 avisos (2 críticos, 9 altos, 11 moderados); `npm audit --omit=dev` da 8 (7 altos, 1 moderado). Afecta a `axios`, `vue`, `vite` y herramientas de desarrollo.
4. **CFG-004 / FE-001 (Media)** — La cookie de sesión no declara `Secure`, la sesión no se cifra y el caché de notas/favoritos del cliente sobrevive al cierre de sesión sin recarga. Riesgo de privacidad en equipos compartidos.
5. **BE-002 / BE-003 (Media)** — Búsquedas sin límite superior ni throttle y áreas sociales accesibles con correo sin verificar: combinación que facilita abuso y carga sostenida.

## Acciones inmediatas recomendadas

| Plazo | Acción | IDs relacionados |
|---|---|---|
| 24-48 h | Rotar la credencial de BD, reemplazarla por placeholder en `README.md` y purgar historial | CFG-001 |
| 24-48 h | Fijar `APP_DEBUG=false`/`APP_ENV=production` en despliegue y quitar el bypass TLS ligado a debug | CFG-002 |
| 24-48 h | `npm audit fix` y verificar `npm audit --omit=dev` | OPS-001 |
| 24-48 h | Definir `SESSION_SECURE_COOKIE=true` y forzar HTTPS | CFG-004 |
| 1 semana | Agregar `composer audit` y `npm audit` al CI | OPS-002 |
| 1-2 semanas | Headers HTTP, throttle en búsquedas, `verified` en área social y reset del caché del cliente | CFG-003, BE-002, BE-003, FE-001 |
| 2-4 semanas | Backup en la migración de deduplicación, importador idempotente y separación `ci:setup`/`deploy:setup` | BE-004, DB-001, OPS-003 |

El plan completo está en `08-plan-de-remediacion.md`.

## Lo que NO VERIFICADO limita

- No se consultó producción: cabeceras HTTP reales, `APP_ENV`/`APP_DEBUG` desplegados, `php.ini`, permisos de filesystem, visibilidad del repositorio remoto, datos reales y volumen de usuarios quedan `NO VERIFICADO`.
- No se ejecutaron pruebas de carga ni `EXPLAIN`; el impacto de BE-002 y del grafo se evalúa desde código, no desde medición.
- No se probó en navegador el cambio de cuenta sin recarga (FE-001).
- No se verificaron backups, RTO/RPO, rotación de logs en producción ni monitoreo.
- No se ejecutó el workflow de GitHub Actions; se auditó su definición.

## Cómo leer estos informes

- `01-arquitectura.md` — stack, rutas, middleware, flujo de datos y límites de confianza.
- `02-seguridad.md` — hallazgos de seguridad con evidencia y mapeo OWASP/CWE.
- `03-backend.md`, `04-base-de-datos.md`, `05-frontend.md`, `06-dependencias-devops.md` — detalle por capa.
- `07-matriz-de-riesgos.md` — tabla de referencia de los 27 hallazgos.
- `08-plan-de-remediacion.md` — orden de ejecución y criterios de aceptación.
- `09-buenas-practicas-encontradas.md` — controles que ya funcionan.
- `10-anexos.md` — metodología, comandos, entorno, métricas y glosario.

Convención: los identificadores y fragmentos de código están en inglés; las explicaciones están en español rioplatense. Ningún informe reproduce la credencial real.
