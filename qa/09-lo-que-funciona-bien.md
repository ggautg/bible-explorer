# 09 — Lo que funciona bien

## Índice
- [Instalación y configuración](#instalación-y-configuración)
- [Base de datos](#base-de-datos)
- [Suite automatizada](#suite-automatizada)
- [Calidad estática y build](#calidad-estática-y-build)
- [Notas, favoritos y compartir (Fase 4)](#notas-favoritos-y-compartir-fase-4)
- [Lectura, APIs y grafo (Fase 5)](#lectura-apis-y-grafo-fase-5)
- [Settings, apariencia e integridad (Fase 6)](#settings-apariencia-e-integridad-fase-6)
- [Accesibilidad: lo que ya está bien (revisión estática)](#accesibilidad-lo-que-ya-está-bien-revisión-estática)
- [Correcciones verificadas (ronda 1)](#correcciones-verificadas-ronda-1)
- [Correcciones verificadas (ronda 2)](#correcciones-verificadas-ronda-2)
- [Verificación en navegador (ronda 3)](#verificación-en-navegador-ronda-3)
- [Seguridad funcional (ronda 6)](#seguridad-funcional-ronda-6)
- [Integración con API externa (ronda 7)](#integración-con-api-externa-ronda-7)
- [Sesiones caducadas y CSRF (ronda 8)](#sesiones-caducadas-y-csrf-ronda-8)
- [Estado del cliente ante envíos duplicados (ronda 9)](#estado-del-cliente-ante-envíos-duplicados-ronda-9)
- [Responsive y caída de red (ronda 10)](#responsive-y-caída-de-red-ronda-10)
- [Correos, verificación y recuperación (ronda 11)](#correos-verificación-y-recuperación-ronda-11)
- [Integridad de migraciones y datos (ronda 12)](#integridad-de-migraciones-y-datos-ronda-12)
- [Presupuestos de consultas (ronda 13)](#presupuestos-de-consultas-ronda-13)
- [Contrato de rutas y middleware (ronda 14)](#contrato-de-rutas-y-middleware-ronda-14)
- [Validaciones, límites y comandos (ronda 15)](#validaciones-límites-y-comandos-ronda-15)
- [Paths negativos y throttles reales (ronda 16)](#paths-negativos-y-throttles-reales-ronda-16)
- [Fronteras finales (ronda 17)](#fronteras-finales-ronda-17)
- [Cobertura funcional heredada](#cobertura-funcional-heredada)

<a id="instalación-y-configuración"></a>

## Instalación y configuración
- `composer install` y `npm ci` funcionan desde los lockfiles; ningún lockfile quedó modificado.
- `composer validate` y `composer audit` pasan (0 avisos de seguridad).
- El entorno de testing queda aislado: `APP_ENV=testing`, SQLite `:memory:`, correo `array`, cola `sync`, sesión y caché `array`, timezone `America/Argentina/Buenos_Aires`, locale `es`.
- No se ejecutó ninguna operación contra la BD de desarrollo ni contra producción.

<a id="base-de-datos"></a>

## Base de datos
- `migrate:fresh --seed` completa las 23 migraciones y el seeder sin errores.
- El ciclo `migrate` → `migrate:reset` → `migrate` funciona sin residuos: 23 aplicadas → 0 aplicadas → 23 aplicadas, con código de salida 0.
- La migración de FULLTEXT tiene guard para SQLite y no rompe la suite.
- La migración de índices de integridad (unicidad de rebibleos y referencias entrantes) se aplica y se revierte correctamente en SQLite.

<a id="suite-automatizada"></a>

## Suite automatizada
- Línea base de Fase 1: 56 tests pasan (54 heredados + 2 de entorno de QA), 233 aserciones, 0 fallos y 0 omitidos. Al cerrar la Fase 6 los tests QA eran 70, con 10 fallos deliberados que documentaban los siete bugs confirmados por ejecución y 1 omisión explícita por MySQL FULLTEXT. Tras la ronda 1: 69 pasan, 0 fallos y 1 omitido, 391 aserciones. Tras la ronda 2: 73 pasan, 0 fallos y 1 omitido, 429 aserciones. Tras la ronda 3: 77 pasan, 0 fallos, 0 omitidos y 449 aserciones. Tras la ronda 6: 85 pasan, 0 fallos, 0 omitidos y 488 aserciones. Tras la ronda 7: 93 pasan, 0 fallos, 0 omitidos y 515 aserciones. Tras la ronda 8: 98 pasan, 0 fallos, 0 omitidos y 527 aserciones. Tras la ronda 9: 103 pasan, 0 fallos, 0 omitidos y 542 aserciones. Tras la ronda 10: 107 pasan, 0 fallos, 0 omitidos y 550 aserciones. Tras la ronda 11: 117 pasan, 0 fallos, 0 omitidos y 625 aserciones. Tras la ronda 12: 133 pasan, 0 fallos, 0 omitidos y 697 aserciones. Tras la ronda 13: 140 pasan, 0 fallos, 0 omitidos y 733 aserciones. Tras la ronda 14: 145 pasan, 0 fallos, 0 omitidos y 775 aserciones. Tras la ronda 15: 167 pasan, 0 fallos, 0 omitidos y 914 aserciones. Tras la ronda 16: 181 pasan, 0 fallos, 0 omitidos y 1139 aserciones. Tras la ronda 17: 187 pasan, 0 fallos, 0 omitidos y 1214 aserciones. Tras la simulación de red: 188 pasan, 0 fallos, 0 omitidos y 1239 aserciones.
- Los tests QA viven en `tests/QA/` y se ejecutan con `php artisan test tests/QA`, sin configuración adicional en la raíz. Los 54 tests heredados siguen pasando solos con `php artisan test --compact`.
- La suite heredada funciona sobre SQLite y, en el ciclo anterior, también sobre MySQL: no hay dependencia de un único driver para los tests actuales.

<a id="calidad-estática-y-build"></a>

## Calidad estática y build
- Pint sin diferencias, PHPStan con 0 errores usando `--memory-limit=1G`, ESLint sin hallazgos, Prettier sin diferencias y `vue-tsc` sin errores.
- `npm run build` genera `public/build/manifest.json` y 77 assets (0,92 MB) en 2,17 s sin `.env`.
- No hay regresiones nuevas en el log tras la línea base.

<a id="autenticación-y-seguridad-fase-2"></a>

## Autenticación y seguridad (Fase 2)
- El registro rechaza correos duplicados aunque difieran solo en mayúsculas: Fortify canonicaliza a minúsculas y `Rule::unique` detecta la colisión.
- El throttle de login responde 429 al sexto intento, con la clave compuesta por correo normalizado + IP.
- El challenge 2FA acepta un TOTP válido calculado sobre el secreto real del usuario y redirige a `/comunidad` con la sesión autenticada.
- El throttle del challenge 2FA responde 429 tras cinco códigos inválidos, usando `login.id` de la sesión.
- Activar y desactivar 2FA desde settings persiste y limpia `two_factor_secret` correctamente.
- Borrar la cuenta elimina por cascada publicaciones, notas y favoritos, sin errores de claves foráneas.
- Los 54 tests heredados siguen pasando después de agregar los tests QA: no hay regresiones.

## Social (Fase 3)
- La validación `required` rechaza cuerpos compuestos solo por espacios, saltos y tabulaciones, tanto en publicaciones como en respuestas.
- El límite de 2000 caracteres acepta el valor exacto con caracteres multibyte y el backend conserva el contenido sin normalizarlo (Unicode, emoji y etiquetas HTML se guardan tal cual; Vue se encarga de escaparlos al renderizar).
- El auto-seguimiento y el auto-rebibleo se bloquean en el servidor.
- El rebibleo alterna sin duplicados y la restricción única de la migración respalda la idempotencia.
- El feed solo lista publicaciones raíz; la pestaña `siguiendo` incluye las propias y las de usuarios seguidos, y excluye al resto.
- Las rutas sociales redirigen a login para invitados y no crean registros con peticiones anónimas.
- La pestaña “me gusta” del perfil sigue siendo privada (verificado por el test heredado).

<a id="notas-favoritos-y-compartir-fase-4"></a>

## Notas, favoritos y compartir (Fase 4)
- Las notas y los favoritos están aislados por usuario: consultar o borrar desde otra sesión no afecta los registros ajenos.
- `PUT /api/notes` hace upsert real y `POST /api/favorites` es idempotente: no se duplican filas al repetir la misma referencia.
- Los textos y nombres de libros se derivan en el servidor desde `spa_rvg`; un payload con `book_name` o `text` manipulados es ignorado (verificado también por el test heredado).
- La validación rechaza notas con cuerpo en blanco, referencias inexistentes y tipos incorrectos en `book_code` o `chapter`.
- Las escrituras de notas están limitadas a 30 por minuto y las cuatro operaciones de las APIs devuelven 401 para invitados.
- Borrar una nota elimina solo el versículo indicado y deja intactas las demás.
- La página pública de compartir funciona sin sesión, muestra el texto servido por el backend y responde 404 para referencias inexistentes.

<a id="lectura-apis-y-grafo-fase-5"></a>

## Lectura, APIs y grafo (Fase 5)
- El listado de libros respeta el orden canónico y las páginas de capítulos/versículos entregan la traducción alterna y los flags de navegación correctos.
- Las referencias inexistentes devuelven 404 tanto en las páginas de lectura como en el grafo; el centro del grafo aparece aunque no tenga conexiones.
- `/api/bible/structure` valida `book` y `chapter`, y `/api/bible/resolve` distingue formato inválido (422) de libro o versículo ausente (404), incluso con nombres acentuados.
- El grafo incluye aristas entrantes y salientes, deduplica conexiones y respeta el tope de 25 por dirección ordenando por `score`.
- Las rutas bíblicas y las APIs exigen sesión: páginas redirigen a login y las APIs responden 401 para invitados.
- Los 54 tests heredados siguen pasando: la Fase 5 no introdujo regresiones.

<a id="settings-apariencia-e-integridad-fase-6"></a>

## Settings, apariencia e integridad (Fase 6)
- Las tres páginas de settings redirigen a login para invitados y `/settings` lleva al perfil cuando hay sesión.
- `settings/Profile` y `settings/Appearance` renderizan los componentes Inertia esperados y el middleware de apariencia respeta `theme` por encima de `appearance`, con fallback a `getsemani`.
- El cambio de contraseña rechaza la actual incorrecta, una contraseña débil y la confirmación distinta, y se corta en el sexto intento por minuto.
- Un correo ya usado por otra cuenta se rechaza sin modificar el propio.
- El endpoint público `/.well-known/passkey-endpoints` publica las URL de enrolamiento y gestión, y la página de seguridad expone el nombre y el *authenticator* derivado del AAGUID sin emular WebAuthn.
- Los índices de integridad están presentes: referencia entrante y unicidad en cross-references, unicidad de rebibleos; los duplicados se rechazan y las passkeys se borran por cascada con el usuario.
- Los 54 tests heredados siguen pasando: la Fase 6 no introdujo regresiones.

<a id="accesibilidad-lo-que-ya-está-bien-revisión-estática"></a>

## Accesibilidad: lo que ya está bien (revisión estática y navegador)
- Las siete `<img>` del JS propio tienen `alt` descriptivo (`PostCard`, `Feed`, `Profile/Show`, `TestimonialCard`, `ProfileGoalsSection`, `TwoFactorSetupModal`, `testimonials`).
- `aria-pressed` está bien aplicado en rebiblear/me gusta (`PostCard`), seguir (`Feed`, `Profile/Show`) y en los controles correctos; no es un patrón ausente en todo el proyecto.
- `sr-only` se usa con criterio en `PostCard`, `PasskeyItem`, `DeleteUser`, `PostComposer`, `TwoFactorChallenge`, `Graph` y `AppHeader`.
- Los landmarks de navegación están etiquetados en `NavHeader`, `BiblePageShell`, `Welcome`, `Feed`, `Profile/Show`, `Posts/Show`, `SettingsLayout`, `PostComposer` y `ProfileGoalsSection`.
- El toggle de menú móvil de `BiblePageShell` combina `aria-expanded`, `aria-controls`, `aria-label` y `focus-visible`.
- `VerseReferencePicker` declara correctamente `role="combobox"`, `aria-autocomplete`, `aria-expanded` y `aria-controls`; la interacción de teclado se verificó en navegador en la ronda 3.
- Hay regiones vivas en `LoadingState`, `ToastContainer`, `Spinner`, `PostComposer` y `VerseReferencePicker`.
- Los formularios de auth y settings asocian `Label` y `autocomplete` en Login, Register, ConfirmPassword, ResetPassword, ForgotPassword, Security y Profile.
- El foco visible explícito está extendido en `NavHeader`, `Welcome`, `PostCard`, `AppearanceTabs`, `VerseReferencePicker`, `PostComposer` y casi todos los controles de `Chapter`.
- Los iconos decorativos se ocultan con `aria-hidden` en `NavHeader`, `StarfieldBackground`, `Welcome`, `EmptyState`, `PostCard`, `Chapter` y `Graph`.
- Los diálogos tienen título y descripción (`DeleteUser`, `PasskeyItem`, `TwoFactorSetupModal`) y `DeleteUser` enfoca el input al haber error.
- Los nodos del grafo tienen `tabindex="0"`, `role="button"`, `aria-label` y responden a Enter/Espacio en `useConstellationGraph.js`; el indicador de foco visible se verificó en navegador en la ronda 3.
- No hay `onclick` inline ni `@click` sobre `div`/`span`/`li`: los handlers están en `<button>` o `<Link>` nativos.

<a id="correcciones-verificadas-ronda-1"></a>

## Correcciones verificadas (ronda 1)
- El slug se genera de forma centralizada en el modelo `User`: se crea con el nombre cuando viene vacío y se regenera al cambiarlo, con sufijo para evitar colisiones.
- `User` implementa el contrato `MustVerifyEmail`, así que el middleware `verified` y el aviso de reenvío del perfil funcionan como estaba configurado.
- El detalle de un post incluye todos los descendientes: una respuesta a una respuesta ya aparece en el hilo.
- Notas y favoritos comparten una única fuente de reglas de referencia: borrar con capítulo o versículo 0 ahora devuelve 422.
- El autocompletado de libros trata `%`, `_` y `\` como caracteres literales mediante `ESCAPE`.
- El grafo descarta aristas cuyos extremos no tienen nodo, con lo que no se dibujan conexiones hacia versículos inexistentes.
- Los siete tests que documentaban esos fallos pasan sin haber sido desactivados; la suite heredada, Pint y PHPStan siguen en verde.

<a id="correcciones-verificadas-ronda-2"></a>

## Correcciones verificadas (ronda 2)
- El botón de mostrar contraseña es tabulable, anuncia su estado y no se excluye con `tabindex="-1"`.
- Los selectores de referencia bíblica del compositor y del grafo soportan flechas, Enter y Escape con `aria-activedescendant`; el error del grafo se anuncia, los nodos muestran indicador de foco y el `<svg>` ya no los oculta con `role=img`.
- Temas, notas, favoritos, comparación de traducción, menú, pestañas y toggle 2FA exponen su estado con ARIA.
- Los códigos de recuperación 2FA salen del árbol accesible cuando están ocultos; los errores se anuncian y se asocian al campo, y los mensajes de éxito usan `role=status`.
- Las acciones placeholder están deshabilitadas, no quedan enlaces `href="#"` y cada ítem repetido se identifica con su referencia o llave.
- `npm run build` funciona sin `.env` y no introduce cambios nuevos en los artefactos Wayfinder: el plugin solo se ejecuta en desarrollo.
- Se agregaron 4 tests estáticos en `tests/QA/FrontendIntegrityRound2Test.php`; `lint:check`, `format:check`, `types:check`, Pint y PHPStan siguen en verde.

<a id="verificación-en-navegador-ronda-3"></a>

## Verificación en navegador (ronda 3)
- El flujo login → navegación → logout funciona en navegador real, sin errores propios de consola.
- El orden de foco sigue el DOM y el ojo de contraseña se alcanza con Tab y se activa con Enter.
- Los errores de formulario se anuncian con `role=alert` y se asocian al campo con `aria-describedby`/`aria-invalid`.
- Los combobox del grafo y del compositor soportan flechas, Enter y Escape con `aria-activedescendant` e IDs únicos.
- Los nodos del grafo muestran foco visible y el SVG expone `role="group"` con etiqueta.
- `/buscar` devuelve resultados con SQLite mediante el fallback `LIKE` escapado; MySQL conserva FULLTEXT.
- Lighthouse dio 1,0 en accesibilidad, buenas prácticas y SEO en once páginas, sin fallos de axe.
- Las once páginas auditadas incluyen auth, inicio, comunidad, lectura, búsqueda, notas, favoritos, ajustes y grafo.

<a id="seguridad-funcional-ronda-6"></a>

## Seguridad funcional (ronda 6)
- `ProfileUpdateRequest` solo valida `name` y `email`: `is_admin`, `role`, `slug` y `church` enviados en el payload no se persisten. El slug se deriva del nombre, no del cliente.
- `PostController` construye el array de creación de forma explícita: la autoría es siempre el usuario autenticado, `parent_id` y `rebible_of_id` quedan nulos y `book_name` se deriva del servidor.
- `kind=rebibleada` se rechaza con 422: es un tipo automático reservado a la rebibleada real.
- `NoteController` y `FavoriteController` trabajan sobre la relación del usuario autenticado, así que un `user_id` ajeno no cambia la propiedad de la fila.
- Dos usuarios pueden tener su propia nota para el mismo versículo sin sobrescribirse (índice único por `user_id` + referencia).
- El cuerpo con `<script>` se conserva como texto literal en la API y no hay `v-html` ni `innerHTML` en `resources/js`: Vue lo renderiza con interpolación.
- Riesgo latente documentado: `user_id` sigue siendo *fillable* en `Post`, `Note` y `Favorite`. Los 8 tests nuevos (`tests/QA/SecurityFunctionalTest.php`) funcionan como guardia de regresión.

<a id="integración-con-api-externa-ronda-7"></a>

## Integración con API externa (ronda 7)
- `bible:seed-text` reintenta un 500 esporádico o un timeout y guarda el texto extraído de los fragmentos; con fallos persistentes informa el error y devuelve `FAILURE` sin insertar datos.
- Un payload 200 sin libros ya no se acepta como importación correcta.
- `bible:list-spanish` ya respondía con error legible ante un 500; se conservó ese comportamiento.
- `bible:seed-crossrefs` valida el índice del conjunto de datos, cuenta los capítulos que fallan y ya no imprime “importadas correctamente” cuando hay pérdida de datos.
- La segunda ejecución de `bible:seed-crossrefs` es idempotente: `upsert()` mantiene una fila por referencia en lugar de chocar con el índice único.
- Los 8 tests usan `Http::fake()`: la API real de `bible.helloao.org` no fue contactada.

<a id="sesiones-caducadas-y-csrf-ronda-8"></a>

## Sesiones caducadas y CSRF (ronda 8)
- La visita Inertia autenticada responde 200; luego del logout, una visita de invitado responde 302 hacia `/login` y `/api/notes` responde 401 JSON.
- Un `POST /posts` tipo Inertia con XSRF vencido ya no muestra “Page Expired” dentro del modal: responde 302 a `/login`. En navegador, un like con la sesión invalidada siguió ese mismo camino.
- Una petición JSON con CSRF vencido conserva el 419 del framework, para no confundir a clientes API.
- En navegador, al invalidar la sesión y tocar un favorito, las peticiones `/api/favorites` y `/api/notes` respondieron 401 y la SPA navegó sola al login; el interceptor evita redirecciones duplicadas.
- Los 5 tests nuevos viven en `tests/QA/SessionExpiryTest.php`. La pérdida de red sigue fuera de alcance.

<a id="estado-del-cliente-ante-envíos-duplicados-ronda-9"></a>

## Estado del cliente ante envíos duplicados (ronda 9)
- Guardar un favorito y navegar por SPA a `/favoritos` muestra el versículo recién guardado: la primera visita Inertia ya no descarta el estado local.
- Dos activaciones rápidas del botón de favorito siguen generando dos `POST` idempotentes, pero el cliente muestra una sola entrada.
- El servidor conserva una fila por usuario/referencia para favoritos y notas, y en notas gana el último cuerpo enviado.
- Dos likes y dos follows secuenciales devuelven el estado al punto de partida, sin filas duplicadas.
- Los 5 tests nuevos viven en `tests/QA/ClientStateRaceTest.php`. La concurrencia real sobre MySQL sigue fuera de alcance: con SQLite aparecieron bloqueos del driver, no un defecto de la aplicación.

<a id="responsive-y-caída-de-red-ronda-10"></a>

## Responsive y caída de red (ronda 10)
- Las nueve páginas revisadas no tienen desbordamiento horizontal en 375, 768, 1366 ni 1920 px (36 combinaciones).
- A 375 px, el toggle `aria-controls="mobile-nav"` abre el menú con nueve enlaces, expone `aria-expanded=true` y se cierra al navegar.
- Una desconexión forzada durante el alta de un favorito produce `net::ERR_INTERNET_DISCONNECTED`; la interfaz no queda en espera y el reintento con la red restaurada persiste el favorito.
- Los mensajes de `showToast()` pasaron a renderizarse en el contenedor `aria-live="polite"` montado en la raíz: antes no se veían; ahora se muestran el error y el éxito.
- Los 4 tests nuevos viven en `tests/QA/ResponsiveFeedbackTest.php`. La verificación fue con emulación CDP, no con revisión manual ni lector de pantalla.

<a id="correos-verificación-y-recuperación-ronda-11"></a>

## Correos, verificación y recuperación (ronda 11)
- El alta de usuarios envía el aviso de verificación; el cambio de correo ahora también lo envía y el enlace nuevo verifica la cuenta.
- El reenvío manual respeta el límite de seis por minuto y devuelve 429 en el séptimo pedido, sin notificaciones extra.
- Los enlaces firmados vencidos se rechazan con 403 y no marcan la cuenta como verificada.
- Los tokens de reset vencen a la hora y no se pueden reutilizar; los códigos de recuperación 2FA se consumen tras un login correcto.
- El pedido de reset con un correo inexistente muestra el mismo mensaje que con uno registrado, sin distinción en HTML ni en JSON.
- El throttle de passkeys se aplica por credencial: el undécimo pedido devuelve 429.
- Los 10 tests nuevos viven en `tests/QA/MailAndVerificationTest.php`. No se usó un servidor SMTP ni un authenticator real.

<a id="integridad-de-migraciones-y-datos-ronda-12"></a>

## Integridad de migraciones y datos (ronda 12)
- Las 23 migraciones se revierten y reaplican sin errores, incluidas las que usan `dropColumn`, `dropUnique` y `change()`.
- Los unique y las foreign keys están en la base: rechazan duplicados y borrados inválidos, y propagan cascadas al borrar usuarios, comentarios o publicaciones.
- Los backfills generan slugs únicos y asignan `spa_rvg` a versículos preexistentes; los índices con nombre se removieron y reaplicaron correctamente.
- Los comentarios y reacciones legacy se migran a publicaciones y likes con el mapa persistido.
- Los 16 tests nuevos viven en `tests/QA/DatabaseIntegrityTest.php`. No se encontraron defectos ni se tocó código de aplicación.

<a id="presupuestos-de-consultas-ronda-13"></a>

## Presupuestos de consultas (ronda 13)
- El feed mantiene constante el número de consultas al crecer de 5 a 60 publicaciones; las relaciones y conteos ya estaban bacheados.
- Se detectó y corrigió el único N+1 medido: el serializador consultaba `isFollowing()` por cada persona sugerida. Ahora reutiliza los IDs ya cargados y el flag `following` sigue siendo correcto.
- Las APIs de notas y favoritos hacen una sola consulta con 150 filas; capítulo, grafo y búsqueda no aumentan sus consultas con 200, 61 y 600 registros respectivamente.
- Los 7 tests nuevos viven en `tests/QA/QueryBudgetTest.php`. No se midió latencia real con servidor web ni MySQL.

<a id="contrato-de-rutas-y-middleware-ronda-14"></a>

## Contrato de rutas y middleware (ronda 14)
- Las páginas protegidas redirigen a `/login`; el devocionario es privado y el welcome público.
- Un usuario sin verificar entra al feed pero no a `security.edit`; con la sesión confirmada por contraseña, esa página responde 200.
- Las páginas `guest` devuelven a `/` a quien ya inició sesión, y desde ahí se va a `/comunidad`.
- Los throttles declarados están presentes en nueve rutas sensibles, incluidas las cinco acciones sociales.
- Los 5 tests nuevos viven en `tests/QA/RouteContractTest.php`. Los dos fallos iniciales fueron expectativas incorrectas del test, no defectos de la aplicación.

<a id="validaciones-límites-y-comandos-ronda-15"></a>

## Validaciones, límites y comandos (ronda 15)
- Los topes de 5000/2000 caracteres, 10 caracteres de `book_code`, capítulo/versículo ≥ 1 y 255 caracteres en perfil se respetan en todas las rutas de escritura.
- Feed, perfil, hilo, búsqueda y grafo respetan sus topes de 60, 60, 20 niveles, 50 y 25 elementos.
- Los comandos de importación reportan fallos sin stack trace, omiten entradas incompletas y actualizan el `score` al reimportar.
- Se corrigió BUG-029: `bible:seed-text` ya no revienta si un versículo no trae `number`.
- Los 22 tests nuevos viven en `tests/QA/ValidationLimitsTest.php` y `tests/QA/CommandEdgeCasesTest.php`. La API real sigue sin contactarse.

<a id="paths-negativos-y-throttles-reales-ronda-16"></a>

## Paths negativos y throttles reales (ronda 16)
- Los comodines del usuario son literales en el autocompletado y en el fallback SQLite de búsqueda de texto.
- Las referencias con capítulo o versículo 0 no se resuelven y los comodines se rechazan por formato.
- Los versículos que sólo existen en `spa_pdt` no se muestran en lectura ni en compartidos.
- Rebiblear un rebibleo apunta al post original y nadie puede rebiblear su propio rebibleo.
- Las rutas DELETE sin sesión, el borrado sin contraseña y el cambio de clave sin confirmación se rechazan; un usuario no verificado no cambia su clave.
- Los límites de 15 y 30 peticiones por minuto devuelven 429 en la práctica. Laravel usa el usuario como clave del contador, no la ruta, así que las acciones con el mismo límite comparten cubo.
- Los 14 tests nuevos viven en `tests/QA/NegativePathsTest.php`; no hubo defectos nuevos.

<a id="fronteras-finales-ronda-17"></a>

## Fronteras finales (ronda 17)
- `%` y `\` son literales en la búsqueda SQLite, igual que `_` y `%` en el autocompletado.
- Una referencia incompleta se rechaza en posts y respuestas antes de crear filas.
- Al recortar el grafo a 25 conexiones se conservan las de mayor `score`, no las primeras insertadas.
- `resolve` sin parámetro o con texto en blanco informa el formato esperado.
- La portada sigue respondiendo sin corpus y usa el código del libro cuando falta su metadata.
- Las pestañas `mios` y `respuestas` no mezclan publicaciones ajenas ni respuestas propias con posts raíz.
- Los 6 tests nuevos viven en `tests/QA/FinalBoundariesTest.php`; no hubo defectos nuevos.

<a id="cobertura-funcional-heredada"></a>

## Cobertura funcional heredada
- Autenticación, registro, verificación de correo, reset de password, confirmación de password y settings: flujos nominales cubiertos y verdes.
- Límite de frecuencia de login con cobertura existente.
- `BibleToolsIntegrityTest`: los nombres y textos se derivan en el servidor, y se rechazan referencias incompletas o inexistentes.
- `SocialInteractionsTest`: alternancia sin duplicados de me gusta y seguimiento, opciones del compositor, etiquetado de rebibleos, privacidad de la pestaña “me gusta”, ausencia de correos ajenos en el feed y throttle de interacciones.
- Seeder idempotente: sembrar dos veces no duplica el usuario de prueba.
