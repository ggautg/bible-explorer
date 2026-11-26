# 10 — Plan de corrección

## Índice
- [Principios](#principios)
- [Orden recomendado](#orden-recomendado)
- [Quick wins](#quick-wins)
- [Dependencias entre bugs](#dependencias-entre-bugs)
- [Bloqueos para liberación](#bloqueos-para-liberación)

<a id="principios"></a>

## Principios
Este plan era una recomendación para el equipo de desarrollo, ordenada por riesgo y esfuerzo. Con autorización explícita se ejecutaron tres rondas de corrección sobre código de aplicación: los siete hallazgos detectados por ejecución (BUG-003 a BUG-009), los seis restantes de frontend y build (BUG-001, BUG-002 y BUG-010 a BUG-013) y los tres hallazgos del navegador (BUG-014 a BUG-016). Los 16 quedaron corregidos y verificados; no hay hallazgos abiertos.

<a id="orden-recomendado"></a>

## Orden recomendado
**Rondas 1, 2 y 3 ejecutadas y verificadas:** los 16 hallazgos. Las rondas 4 a 6 agregaron perfilado, carga y seguridad funcional sin nuevos defectos. El detalle de cada cambio está en `00-resumen-ejecutivo.md`.

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
| 12 | — | — | P3 | Pendiente: FULLTEXT en MySQL aislado, lector de pantalla, viewports y carga sostenida con servidor real | M |

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
- La suite QA ya es reejecutable con `php artisan test tests/QA`: no hace falta ninguna config nueva en la raíz.

<a id="dependencias-entre-bugs"></a>

## Dependencias entre bugs
BUG-003 y BUG-004 compartían causa (no había lógica centralizada de slug) y se corrigieron juntos en los eventos `creating`/`updating` de `User`. BUG-009 era independiente: se resolvió en el contrato del modelo, antes de tocar la UI de verificación. BUG-005 requirió definir que el hilo social es multinivel y aplanar los descendientes en `show()`. BUG-006 compartía patrón con `store()`/`upsert()` y se unificó en un trait. BUG-007 y BUG-008 eran independientes; este último se corrigió antes de importar más cross-references. BUG-010 a BUG-013 se agruparon por componente compartido. BUG-001 y BUG-002 resultaron ser la misma causa (Wayfinder corriendo en el build) y se resolvieron con un único cambio en `vite.config.ts`.

<a id="bloqueos-para-liberación"></a>

## Bloqueos para liberación
**Sin bloqueos.** Los 16 hallazgos están corregidos y la revalidación quedó en verde: 85/85 tests QA, 54/54 heredados, Lighthouse 1,0 en once páginas y 170/170 respuestas 200 en la prueba de carga con 15.000 versículos. El veredicto es **GO condicionado a MySQL y lector de pantalla**: sigue NO VERIFICADO `MATCH ... AGAINST` sobre MySQL aislado, un lector de pantalla dedicado, viewports manuales y carga sostenida con servidor web real.
