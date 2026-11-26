# 06 — Matriz de cobertura

## Índice
- [Leyenda](#leyenda)
- [Matriz módulo × tipo de prueba](#matriz-módulo--tipo-de-prueba)
- [Lectura](#lectura)

<a id="leyenda"></a>

## Leyenda
- ✅ probado
- ⚠️ probado parcialmente
- ❌ no probado
- **Func** = 2.1 funcional / CRUD · **Neg** = 2.2 negativa y límites · **API** = 2.3 backend · **Jobs** = 2.4 · **BD** = 2.5 · **UI** = 2.6 · **E2E** = 2.7 · **Resp** = 2.8 · **Perf** = 2.9 · **Seg** = 2.10 · **A11y** = 2.11 · **Reg** = 2.12 regresión.

<a id="matriz-módulo--tipo-de-prueba"></a>

## Matriz módulo × tipo de prueba
| Módulo | Func | Neg | API | Jobs | BD | UI | E2E | Resp | Perf | Seg | A11y | Reg | Cobertura estimada |
| --- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | ---: |
| Entorno y setup | ⚠️ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ⚠️ | ❌ | ❌ | ✅ | 35 % |
| Migraciones e integridad | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | 50 % |
| Autenticación y verificación | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ | ⚠️ | ✅ | 72 % |
| Seguridad (password, 2FA, passkeys) | ✅ | ✅ | ✅ | ❌ | ⚠️ | ❌ | ❌ | ❌ | ❌ | ✅ | ⚠️ | ✅ | 70 % |
| Social (posts, likes, follows) | ✅ | ✅ | ⚠️ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ | ⚠️ | ✅ | 65 % |
| Bíblicas (lectura, búsqueda, grafo) | ✅ | ✅ | ⚠️ | ❌ | ⚠️ | ❌ | ❌ | ❌ | ❌ | ✅ | ⚠️ | ✅ | 75 % |
| Notas, favoritos y compartir | ✅ | ✅ | ⚠️ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ | ⚠️ | ✅ | 68 % |
| Settings, perfil y temas | ✅ | ✅ | ✅ | ❌ | ✅ | ⚠️ | ❌ | ❌ | ❌ | ✅ | ⚠️ | ✅ | 75 % |
| Jobs, colas y correos | ✅ | ✅ | ✅ | ⚠️ | ⚠️ | ❌ | ⚠️ | ❌ | ❌ | ✅ | ❌ | ✅ | 65 % |
| Frontend / UI / accesibilidad | ❌ | ❌ | ❌ | ❌ | ❌ | ⚠️ | ❌ | ✅ | ❌ | ❌ | ⚠️ | ❌ | 40 % |
| Rendimiento y volumen | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ⚠️ | ❌ | ❌ | ❌ | 10 % |

<a id="lectura"></a>

## Lectura
La cobertura es alta en el “esqueleto” técnico (instalación, migraciones, suite base, lint/build) y, tras las Fases 2 a 6, en autenticación, throttles, 2FA, social, notas/favoritos/compartir, lectura/grafo bíblico y settings. La Fase 6 agregó casos de rutas, Inertia props, throttle de password, well-known de passkeys, tema y restricciones de integridad. Sigue baja en passkeys completos (WebAuthn real), búsqueda FULLTEXT sobre MySQL, lector de pantalla dedicado y rendimiento con volumen real (la ronda 5 cubrió carga sintética con servidor integrado). El ⚠️ de API en social refleja BUG-005 (anidamiento de respuestas), el de notas/favoritos BUG-006 (validación del borrado) y el de bíblicas BUG-007 (escape de `%`/`_` en autocompletado); los tres ya están corregidos. El ⚠️ de BD en bíblicas refleja BUG-008 (referencias cruzadas colgantes), también corregido. El ⚠️ de A11y en varios módulos y en “Frontend / UI / accesibilidad” significa revisión **estática** de Vue: los hallazgos (BUG-010 a BUG-013) están corregidos, cubiertos por tests estáticos y confirmados en navegador en la ronda 3; no se usó lector de pantalla. El porcentaje estimado es medida de amplitud de casos, no de cobertura de líneas (no hay Xdebug/PCOV). En la ronda 3 se ejecutó la app en navegador real con teclado, árbol de accesibilidad y Lighthouse: los hallazgos de accesibilidad quedaron confirmados en ejecución, se corrigieron tres defectos nuevos (BUG-014 a BUG-016) y las once páginas auditadas dieron 1,0 en accesibilidad, buenas prácticas y SEO. La ronda 5 midió carga con 15.000 versículos sintéticos (170/170 respuestas 200), sin medir con servidor web real. La ronda 6 agregó cobertura de mass assignment, propiedad de notes/favorites/posts y render seguro de HTML: 8 tests nuevos sin hallazgos. La ronda 7 cubrió los comandos que consumen la API externa con respuestas simuladas: 8 tests, cuatro defectos (BUG-017 a BUG-020) corregidos; la API real sigue sin contactarse. La ronda 8 agregó cobertura de sesión caducada, CSRF vencido y redirección del cliente ante 401/419: 5 tests, dos defectos (BUG-021 y BUG-022) corregidos y verificación en navegador. La ronda 9 cubrió envíos duplicados y el estado local del cliente: 5 tests, dos defectos (BUG-023 y BUG-024) corregidos y verificados en navegador; el sondeo paralelo con SQLite mostró `database is locked`, así que la concurrencia sobre MySQL sigue sin ejecutarse. La ronda 10 cubrió responsive y transporte con CDP: 36 combinaciones viewport × página sin desbordamiento, corte de red con `ERR_INTERNET_DISCONNECTED` y recuperación al reintentar; un defecto (BUG-025: los `showToast()` no se mostraban) corregido con 4 tests nuevos. Sigue sin ejecutarse `MATCH ... AGAINST` en MySQL real, lector de pantalla dedicado ni WebAuthn con authenticator real. La ronda 11 cubrió correos y recuperación: notificación al registrar y al cambiar el correo, seis reenvíos por minuto, enlaces vencidos, tokens de reset expirados o reutilizados, códigos de recuperación 2FA y throttle de passkeys; dos defectos corregidos (BUG-026 y BUG-027) con 10 tests nuevos. El ⚠️ en Jobs refleja que la cola se ejecuta en `sync` y no en un worker real. Ciclo cerrado con veredicto **GO condicionado a MySQL y lector de pantalla**.
