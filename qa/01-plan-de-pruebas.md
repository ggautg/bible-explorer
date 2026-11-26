# 01 — Plan de pruebas

## Índice
- [Estrategia](#estrategia)
- [Entorno](#entorno)
- [Módulos y tipos de prueba](#módulos-y-tipos-de-prueba)
- [Criterios de entrada](#criterios-de-entrada)
- [Criterios de salida](#criterios-de-salida)
- [Herramientas y limitaciones](#herramientas-y-limitaciones)

## Estrategia
1. **Fase 0 — reconocimiento:** inventario de versiones, rutas, controladores, páginas Vue, procesos asíncronos, integraciones, roles y tests existentes. Entregado y confirmado.
2. **Fase 1 — preparación:** instalación desde cero, BD de testing, migraciones up/down, seeders, línea base de tests, lint, tipos y build. Entregado.
3. **Fase 2 a Fase 6 — ejecución por capas:** **un módulo por vez**, con resumen corto y espera del `continúa` del usuario. Cada hallazgo lleva reproducción, esperado/actual, evidencia, causa raíz, impacto y clasificación S1-S5 / P1-P4 / frecuencia.
4. **Cierre:** consolidación de los 12 informes y veredicto de liberación.

No se corrige código de aplicación. Los tests nuevos quedan en `tests/QA/`.

**Progreso:** Fase 2 ejecutada (autenticación, seguridad y throttles): 9 tests, 6 pasan y 3 fallan; dos causas raíz confirmadas (BUG-003 S2, BUG-004 S3). Fase 3 ejecutada (social): 11 tests, 10 pasan y 1 falla (BUG-005 S4). Fase 4 ejecutada (notas, favoritos y compartir): 14 tests, 12 pasan y 2 fallan (misma causa raíz, BUG-006 S4). Fase 5 ejecutada (lectura, búsqueda y grafo): 20 tests, 17 pasan, 2 fallan (BUG-007 y BUG-008, S4) y 1 omitido por depender de MySQL FULLTEXT. Fase 6 ejecutada (settings, frontend estático e integridad): 14 tests, 12 pasan y 2 fallan (misma causa raíz, BUG-009 S3), más revisión estática de accesibilidad con cuatro hallazgos agrupados (BUG-010 a BUG-013). Ciclo completado. Con autorización explícita se abrieron tres rondas de corrección: primero los siete defectos ejecutados, después los seis hallazgos restantes de frontend y build y por último la verificación en navegador (tres hallazgos más: BUG-014 a BUG-016). Los 16 quedaron corregidos y verificados con la suite QA en verde y Lighthouse 1,0 en once páginas. Después se perfiló el renderer en navegador (ronda 4), se corrió carga ligera con 15.000 versículos (ronda 5) y se cerró el hueco de seguridad funcional con 8 tests preventivos (ronda 6), sin nuevos hallazgos.

## Entorno
| Item | Valor |
| --- | --- |
| PHP | 8.3.36 |
| Laravel | 13.44.1 |
| Vue / Inertia | 3.5.40 / 3.8.0 |
| Fortify | 1.44.0 |
| MySQL del proyecto | 8.0.35 — **no utilizado en este ciclo** |
| BD de QA | SQLite `:memory:` (definida en `phpunit.xml`) |
| BD auxiliar para up/down | Archivo SQLite temporal en `%LOCALAPPDATA%\Temp\opencode\qa-migrations.sqlite` |
| Correo / cola / sesión / caché | `array` / `sync` / `array` / `array` |
| Node | 26.13.0 (CI 22) |

## Módulos y tipos de prueba
| Orden | Módulo | Riesgo | Tipos aplicables (sección 2.1-2.12) |
| --- | --- | --- | --- |
| 1 | BD, migraciones e integridad | Alto | 2.1, 2.2, 2.5, 2.10, 2.12 |
| 2 | Autenticación, seguridad y throttles | Alto | 2.1, 2.2, 2.3, 2.10, 2.12 |
| 3 | Social: feed, posts, likes, follows, privacidad | Alto | 2.1-2.3, 2.10, 2.12 |
| 4 | Notas, favoritos y compartir | Alto | 2.1-2.3, 2.10, 2.12 |
| 5 | Lectura, búsqueda y grafo bíblico | Medio | 2.1, 2.2, 2.3, 2.5 |
| 6 | Settings, temas, frontend y accesibilidad | Medio | 2.6, 2.8, 2.11 |
| 7 | Rendimiento y regresión final | Medio | 2.9, 2.12 |

Jobs propios: **ninguno**; correos: solo Fortify; integraciones externas: tres comandos Artisan contra la API de Bible.org/helloao (fuera del request path). Scheduler: sin tareas programadas.

## Criterios de entrada
- Dependencias instaladas y lockfiles sin cambios.
- BD de testing aislada; confirmado que no se apunta a datos reales.
- `migrate:fresh --seed` ejecuta sin errores.
- Línea base de tests, lint y build en verde.

## Criterios de salida
- Todos los casos de un módulo ejecutados con estado Pasó / Falló / Bloqueado.
- Todo Falló tiene BUG-xxx con evidencia reproducida.
- Sin regresiones en la suite completa.
- Informes `qa/` actualizados y veredicto final emitido.

## Herramientas y limitaciones
- Pest 4.7.8 vía `php artisan test`; tests QA con `php artisan test tests/QA`.
- ESLint, Prettier, `vue-tsc`, Vite, Pint, PHPStan con `--memory-limit=1G`.
- **Sin** Vitest, Playwright ni Dusk instalados: no hay tests de componentes ni E2E automatizados; el flujo de login/logout se verificó manualmente en el navegador integrado durante la ronda 3.
- **Sin** Xdebug/PCOV: cobertura por líneas no medida; se reporta cobertura por matriz de casos.
- No se instalaron `ab`, `wrk` ni `k6`: la ronda 5 usó `curl_multi` desde scripts PHP temporales. En la ronda 3 sí se ejecutaron axe/Lighthouse sobre once páginas, con 1,0 en accesibilidad, buenas prácticas y SEO; la ronda 4 perfiló el renderer y la ronda 5 simuló 170 peticiones con 15.000 versículos. No hay datos de carga sostenida con servidor web real.
- La búsqueda de texto con `q >= 3` usa FULLTEXT en MySQL; en SQLite se cubre con un fallback `LIKE` escapado. `MATCH ... AGAINST` sobre MySQL real queda NO VERIFICADO.
- La accesibilidad se revisó por lectura de código en Fase 6 y se confirmó en navegador en la ronda 3 con teclado, árbol accesible y axe/Lighthouse. No se usó un lector de pantalla dedicado.
