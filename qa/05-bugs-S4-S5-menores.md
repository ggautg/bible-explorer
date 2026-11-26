# 05 — Bugs S4 (Bajos) y S5 (Cosméticos / Mejora)

## Índice
- [BUG-015 — Hallazgos de axe en navegador: foco, contraste, nombres y landmark](#bug-015)
- [BUG-016 — Falta la meta descripción compartida](#bug-016)
- [BUG-010 — Controles inalcanzables por teclado](#bug-010)
- [BUG-011 — Toggles y pestañas sin estado accesible](#bug-011)
- [BUG-012 — Contenido sensible oculto solo por CSS y estados no anunciados](#bug-012)
- [BUG-013 — Botones placeholder y nombres ambiguos en listas](#bug-013)
- [BUG-008 — Aristas del grafo sin nodo correspondiente](#bug-008)
- [BUG-007 — `%` actúa como comodín en la búsqueda de libros](#bug-007)
- [BUG-006 — El borrado de notas y favoritos acepta capítulo o versículo 0](#bug-006)
- [BUG-005 — Una respuesta a una respuesta queda invisible en el hilo](#bug-005)
- [BUG-002 — El build falla en instalación limpia sin APP_KEY](#bug-002)
- [BUG-001 — Wayfinder ensucia el árbol con cambios de salto de línea](#bug-001)

> **Rondas de corrección 1, 2 y 3:** los 13 hallazgos originales y los dos nuevos de accesibilidad (BUG-015 y BUG-016) fueron corregidos y verificados. Las entradas siguientes conservan la evidencia original del hallazgo; el estado actual de cada uno se indica en su línea **Estado de verificación**. Ya no queda ningún defecto abierto.

<a id="bug-015"></a>

### [BUG-015] Hallazgos de axe en navegador: foco, contraste, nombres y landmark
- **Severidad:** S4
- **Prioridad:** P3
- **Frecuencia:** Siempre que se navega con teclado o se audita una página
- **Tipo:** Accesibilidad / Interacción
- **Módulo / Capa:** Frontend / Vue (auth,Chapter, NavHeader, Graph, Welcome, layouts)
- **Estado de verificación:** CONFIRMADO en navegador → **CORREGIDO y verificado** (ronda 3)
- **Corrección aplicada:** se eliminaron los `tabindex` positivos de Login/Register; se subió el contraste de `text-bible-muted/80` y `text-bible-muted-dim`; los `aria-label` de Chapter incluyen la referencia y el texto visible; `AuthLayout` usa `<main>`.
- **Ubicación original:** `resources/js/pages/auth/Login.vue`, `Register.vue`, `components/Bible/NavHeader.vue`, `pages/Bible/Graph.vue`, `pages/Bible/Chapter.vue`, `pages/Welcome.vue`, `layouts/AuthLayout.vue`
- **Entorno:** Chromium del navegador integrado, `artisan serve` sobre SQLite temporal
- **Precondiciones:** sesión iniciada o páginas públicas de auth según el caso
- **Pasos para reproducir:**
  1. En `/login`, tabular desde el correo: el foco pasaba del password a “Recordarme”, salteando el ojo.
  2. Correr Lighthouse sobre `/`, `/login`, `/register`, `/forgot-password`, `/comunidad`, `/leer/GEN/1`, `/buscar`, `/notas`, `/favoritos` y `/settings/appearance`.
  3. Revisar `aria-label` de “Grafo”, “Compartir” y favoritos en `/leer/GEN/1`.
- **Resultado esperado:** el foco sigue el orden del DOM, contraste ≥ 4.5:1, nombres que incluyen el texto visible y un único `<main>` por página.
- **Resultado actual:** foco desordenado, fallos `color-contrast`, `label-content-name-mismatch` y `landmark-one-main`; accesibilidad 0,96 en varias páginas.
- **Evidencia:** secuencia de foco medida con teclado real; informes Lighthouse con los IDs de regla anteriores; tras la corrección, 1,0 sin fallos en once páginas.
- **Causa raíz probable:** `tabindex` positivos heredados de las plantillas, token de color de bajo contraste y `aria-label` que reemplazaban —en vez de ampliar— el texto visible.
- **Impacto:** navegación confusa para usuarios de teclado y lector de pantalla; texto difícil de leer con tema oscuro.
- **Workaround:** usar mouse y evitar las zonas de bajo contraste.
- **Recomendación de corrección:** confiar en el orden del DOM, usar el token `text-bible-muted`, concatenar la referencia en los nombres accesibles y envolver el contenido de auth en `<main>`.
- **Test que lo reproduce:** `tests/QA/FrontendIntegrityRound2Test.php` (marcadores estáticos) y casos CP-108 a CP-118 en `07-casos-de-prueba.md`.
- **Esfuerzo estimado:** S

<a id="bug-016"></a>

### [BUG-016] Falta la meta descripción compartida
- **Severidad:** S5
- **Prioridad:** P4
- **Frecuencia:** Siempre (todas las páginas)
- **Tipo:** SEO / Accesibilidad
- **Módulo / Capa:** Frontend / Blade
- **Estado de verificación:** CONFIRMADO por Lighthouse → **CORREGIDO y verificado** (ronda 3)
- **Corrección aplicada:** `<meta name="description">` agregada en `resources/views/app.blade.php`.
- **Ubicación original:** `resources/views/app.blade.php:5`
- **Entorno:** Chromium del navegador integrado, Lighthouse sobre `/bible/GEN/1/1`
- **Precondiciones:** ninguna
- **Pasos para reproducir:**
  1. Correr Lighthouse sobre cualquier página.
  2. Revisar la auditoría SEO.
- **Resultado esperado:** el documento expone una descripción.
- **Resultado actual:** fallo `meta-description`, SEO 0,8.
- **Evidencia:** informe Lighthouse con `meta-description` en `failures`; tras la corrección SEO 1,0 en todas las páginas auditadas.
- **Causa raíz probable:** la plantilla compartida solo declaraba `charset` y `viewport`.
- **Impacto:** fragmentos pobres en resultados de búsqueda; sin efecto funcional.
- **Workaround:** ninguno desde la UI.
- **Recomendación de corrección:** agregar la meta descripción en la plantilla compartida, no por página.
- **Test que lo reproduce:** `tests/QA/FrontendIntegrityRound2Test.php` → `the shared document exposes a meta description`.
- **Esfuerzo estimado:** S

<a id="bug-010"></a>

### [BUG-010] Controles inalcanzables por teclado
- **Severidad:** S4
- **Prioridad:** P3
- **Frecuencia:** Siempre que se navega sin mouse por esos flujos
- **Tipo:** Accesibilidad / Interacción
- **Módulo / Capa:** Frontend / Vue
- **Estado de verificación:** CONFIRMADO por lectura de código y **confirmado con teclado real en navegador** (ronda 2 corregido, ronda 3 verificado). Sin lector de pantalla dedicado.
- **Corrección aplicada:** `PasswordInput` es tabulable y expone `aria-pressed`; `VerseReferencePicker` y el buscador del grafo implementan flechas/Enter/Escape con `aria-activedescendant`; el error del grafo usa `role=alert`, los nodos resaltan al recibir foco y el `<svg>` cambió de `role=img` a `role=group`.
- **Ubicación original:** `resources/js/components/PasswordInput.vue:31-43`; `resources/js/components/Social/VerseReferencePicker.vue:231-259`; `resources/js/pages/Bible/Graph.vue:188-205`; `resources/js/composables/useVerseSearch.js:16-72`; `resources/js/composables/useConstellationGraph.js:89-116`
- **Entorno:** revisión estática de `resources/js`, sin Playwright/axe autorizados
- **Precondiciones:** usuario que navega con Tab/Enter/Espacio
- **Pasos para reproducir (por código):**
  1. Tabular desde un campo de contraseña: el botón del ojo tiene `tabindex="-1"`, así que nunca recibe foco.
  2. Abrir el selector de versículo del compositor o las sugerencias del grafo: los `role="option"` solo se activan con clic; no hay `ArrowDown`/`ArrowUp`/`Enter`/`Escape` ni `aria-activedescendant`.
  3. Tabular a un nodo del grafo SVG: tiene `tabindex="0"` y `role="button"`, pero no hay estilo `:focus`/`focus-visible` para los `<g>` y el `<svg>` declara `role="img"`.
- **Resultado esperado:** todos los controles son alcanzables por teclado, anuncian su estado y muestran el foco.
- **Resultado actual:** la visibilidad de contraseñas y las sugerencias dependen del mouse; el foco en el grafo no tiene indicador propio y puede quedar oculto del árbol de accesibilidad.
- **Evidencia:** `PasswordInput.vue:31-43` (`tabindex="-1"`), `VerseReferencePicker.vue:231-259` (combobox sin manejo de teclas e id fijo `verse-book-suggestions`), `useVerseSearch.js:16-72` y `Graph.vue:188-205` (listbox sin `role="option"`/`aria-selected`), `useConstellationGraph.js:89-116` (nodos con `tabindex` pero sin CSS de foco).
- **Causa raíz probable:** los componentes declararon atributos ARIA sin implementar el patrón de interacción completo; el botón de contraseña se excluyó del orden de tabulación deliberadamente.
- **Impacto:** usuarios de teclado no pueden verificar contraseñas ni elegir referencias sugeridas; el grafo, funcionalidad diferencial del producto, queda degradado. No afecta a usuarios con mouse.
- **Workaround:** usar mouse; en el caso de contraseñas, confiar en el campo enmascarado.
- **Recomendación de corrección:** quitar `tabindex="-1"` y agregar `aria-pressed` al botón de contraseña; implementar navegación por flechas/Enter/Escape y `aria-activedescendant` en ambos combobox; generar IDs únicos por instancia; agregar `focus-visible` a los `<g>` del grafo y revisar el `role="img"` del `<svg>`.
- **Test que lo reproduce:** no hay runner de navegador. Caso estático CP-087 en `07-casos-de-prueba.md`; detalle en `11-anexos.md`.
- **Esfuerzo estimado:** M

<a id="bug-011"></a>

### [BUG-011] Toggles y pestañas no exponen su estado a tecnología asistiva
- **Severidad:** S4
- **Prioridad:** P4
- **Frecuencia:** Siempre que se usa un toggle o cambia de pestaña
- **Tipo:** Accesibilidad / Semántica
- **Módulo / Capa:** Frontend / Vue
- **Estado de verificación:** CONFIRMADO por lectura de código → **CORREGIDO y verificado con test estático** (ronda 2) y con axe/Lighthouse en navegador (ronda 3). Sin lector de pantalla dedicado.
- **Corrección aplicada:** `aria-pressed` en temas, notas, favoritos y toggle 2FA; `aria-expanded` en comparación de traducción y menú móvil; `aria-current="page"` en pestañas y navegación.
- **Ubicación original:** `resources/js/components/AppearanceTabs.vue:39-66,78-105`; `resources/js/pages/Bible/Chapter.vue:201-213,269-289,291-321`; `resources/js/pages/Community/Feed.vue:108-121`; `resources/js/pages/Profile/Show.vue:204-217`; `resources/js/layouts/SettingsLayout.vue:56-74`; `resources/js/components/Bible/NavHeader.vue:78-82`
- **Entorno:** revisión estática de `resources/js`
- **Precondiciones:** cualquiera de las vistas listadas
- **Pasos para reproducir (por código):**
  1. Seleccionar un tema, comparar traducciones, marcar nota/favorito o cambiar de pestaña.
  2. Observar que el cambio se indica con color/borde y texto visible, pero no hay `aria-pressed`, `aria-expanded` ni `aria-current` en los controles propios.
- **Resultado esperado:** el estado activo se anuncia y el enlace actual se marca con `aria-current`.
- **Resultado actual:** el estado existe solo en la capa visual; el único `aria-current` del proyecto está en el componente genérico `BreadcrumbPage.vue`.
- **Evidencia:** las líneas citadas muestran clases condicionales y texto cambiante sin atributos de estado. Sí hay `aria-pressed` correcto en `PostCard.vue:228,252`, `Feed.vue:248` y `Profile/Show.vue:167`, así que el patrón ya existe en otras partes.
- **Causa raíz probable:** se reutilizó el aspecto visual de botones y enlaces sin propagar el estado al DOM accesible; no hay una directiva o componente compartido que obligue a declararlo.
- **Impacto:** usuarios con lector de pantalla no saben qué tema, pestaña o toggle está activo. No bloquea la acción y no afecta a usuarios visuales.
- **Workaround:** ninguno desde la UI; deducir el estado por el contenido visible.
- **Recomendación de corrección:** agregar `aria-pressed` a botones de tema/nota/favorito, `aria-expanded`+`aria-controls` a comparación de traducción y `aria-current="page"` al enlace activo en pestañas y menú.
- **Test que lo reproduce:** caso estático CP-088 en `07-casos-de-prueba.md`.
- **Esfuerzo estimado:** S

<a id="bug-012"></a>

### [BUG-012] Contenido sensible oculto solo por CSS y estados no anunciados
- **Severidad:** S4
- **Prioridad:** P3
- **Frecuencia:** Siempre que se ocultan códigos 2FA o aparece un error/éxito
- **Tipo:** Accesibilidad / Privacidad
- **Módulo / Capa:** Frontend / Vue
- **Estado de verificación:** CONFIRMADO por lectura de código → **CORREGIDO y verificado con test estático** (ronda 2) y con axe/Lighthouse en navegador (ronda 3). Sin lector de pantalla dedicado.
- **Corrección aplicada:** los códigos 2FA se renderizan con `v-if`; `InputError` usa `role=alert`/`aria-live` y se asocia con `aria-describedby`/`aria-invalid`; los estados usan `role=status` y el botón de copiar tiene nombre y `<label>`.
- **Ubicación original:** `resources/js/components/TwoFactorRecoveryCodes.vue:57-89`; `resources/js/components/TwoFactorSetupModal.vue:182-192,228-243`; `resources/js/components/InputError.vue:7-11`; `resources/js/pages/auth/Login.vue:31-36`, `ForgotPassword.vue:26-31`, `VerifyEmail.vue:23-28`, `resources/js/pages/settings/Profile.vue:103-109`, `Security.vue:97-102`
- **Entorno:** revisión estática de `resources/js`
- **Precondiciones:** usuario con 2FA activado o formulario con errores/estado
- **Pasos para reproducir (por código):**
  1. Ocultar los códigos de recuperación: el bloque sigue en el DOM con `h-0 opacity-0`, sin `hidden` ni `aria-hidden`, y el toggle no declara `aria-expanded`/`aria-pressed`.
  2. Enviar un formulario inválido: `InputError` renders un `<p>` sin `role="alert"`, sin `aria-live` y sin `aria-describedby`/`aria-invalid` en el input.
  3. Guardar contraseña o perfil: el texto “Guardado.” se inyecta sin `role="status"`.
  4. Copiar la clave manual 2FA: el botón tiene solo el icono `Copy`/`Check`, sin nombre accesible ni `type="button"`; el input readonly tampoco tiene `<label>`.
- **Resultado esperado:** el contenido oculto no es alcanzable por lector de pantalla, los errores se anuncian y se asocian al campo, y los iconos tienen nombre.
- **Resultado actual:** los códigos secretos siguen siendo legibles por AT; los mensajes cambian sin anuncio; el botón de copiar queda sin nombre.
- **Evidencia:** líneas citadas. `PostComposer.vue:188,208-225` y `ToastContainer.vue:11-12` muestran que el patrón de región viva ya existe en otras partes.
- **Causa raíz probable:** se usó CSS para ocultar visualmente en lugar de quitar del árbol de accesibilidad, y no se centralizaron `role="alert"`/`aria-describedby` en el componente de error compartido.
- **Impacto:** riesgo de privacidad para el propio usuario con lector de pantalla y falta de feedback en formularios sensibles. No permite a terceros leer los códigos.
- **Workaround:** ninguno desde la interfaz; el usuario debe evitar ocultar la vista si necesita que se lea el estado.
- **Recomendación de corrección:** usar `v-if`/`hidden`+`aria-hidden` para los códigos, agregar estado al toggle, `role="alert"` e `id` compartido vía `aria-describedby`/`aria-invalid` en `InputError`, `role="status"` a los mensajes de éxito y nombre accesible al botón de copiar.
- **Test que lo reproduce:** caso estático CP-089 en `07-casos-de-prueba.md`.
- **Esfuerzo estimado:** S

<a id="bug-013"></a>

### [BUG-013] Botones placeholder y nombres ambiguos en listas
- **Severidad:** S5
- **Prioridad:** P4
- **Frecuencia:** Ocasional (páginas con	listas o maquetas)
- **Tipo:** Accesibilidad / UX
- **Módulo / Capa:** Frontend / Vue
- **Estado de verificación:** CONFIRMADO por lectura de código → **CORREGIDO y verificado con test estático** (ronda 2) y en navegador (ronda 3)
- **Corrección aplicada:** los botones sin handler quedaron `disabled`; se eliminaron los `href="#"` cuando no hay slug; cada ítem repetido de lista incluye referencia o llave en su `aria-label`.
- **Ubicación original:** `resources/js/pages/Welcome.vue:365-381`; `resources/js/pages/Community/Feed.vue:278-283`; `resources/js/components/Social/PostCard.vue:110-116,136-142`; `resources/js/pages/Bible/Book.vue:81-88`; `resources/js/pages/Bible/Search.vue:160-169`; `resources/js/pages/Bible/Favorites.vue:97-104`; `resources/js/pages/Bible/Notes.vue:97-104`; `resources/js/components/PasskeyItem.vue:64-71`
- **Entorno:** revisión estática de `resources/js`
- **Precondiciones:** páginas con acciones aún no implementadas o listas repetidas
- **Pasos para reproducir (por código):**
  1. Enviar el foco a “Iniciar conversación” o “Hablar con un pastor”: son `<button>` enfocables, pero no tienen `@click`.
  2. Recorrer los enlaces de capítulo (`1`, `2`, `3`), los “Ver conexiones” del buscador o los botones “Quitar”/“Eliminar”: el nombre no incluye libro, referencia ni llave.
  3. Encontrar enlaces con `href="#"` cuando el usuario no tiene slug.
- **Resultado esperado:** los controles no disponibles no se exponen como acciones, o lo hacen deshabilitados y con texto “próximamente”; cada enlace/botón de una lista se identifica de forma única.
- **Resultado actual:** hay acciones que no hacen nada y nombres repetidos que obligan a inferir el destino por el contexto visual.
- **Evidencia:** líneas citadas; las propias vistas marcan esas zonas como “maquetado de interfaz” o “próximamente”.
- **Causa raíz probable:** maquetas de producto que quedaron visibles y falta de contexto en los nombres accesibles generados por `v-for`.
- **Impacto:** confusión y ruido en la navegación por teclador/lector de pantalla. No hay pérdida de datos ni bloqueo de flujos principales.
- **Workaround:** evitar esos controles; usar el contexto visual para identificar la fila.
- **Recomendación de corrección:** deshabilitar u ocultar los botones sin handler, reemplazar `href="#"` por texto no enlazado y concatenar referencia/llave en `aria-label` de cada ítem.
- **Test que lo reproduce:** caso estático CP-090 en `07-casos-de-prueba.md`.
- **Esfuerzo estimado:** S

<a id="bug-007"></a>

### [BUG-007] Un `%` en la búsqueda de libros devuelve todos los libros
- **Severidad:** S4
- **Prioridad:** P3
- **Frecuencia:** Ocasional (solo si el usuario escribe `%` o `_` en el autocompletado)
- **Tipo:** Funcional / API — validación de entrada
- **Módulo / Capa:** Backend / Bíblicas / Autocompletado
- **Estado de verificación:** CONFIRMADO
- **Ubicación:** `app/Http/Controllers/Bible/SearchController.php:38-48` (`books()`)
- **Entorno:** PHP 8.3.36, Laravel 13.44.1, SQLite `:memory:`, usuario autenticado
- **Precondiciones:** existan libros en `bible_books`; sesión autenticada
- **Pasos para reproducir:**
  1. Autenticarse y pedir `GET /api/bible/books-search?q=%25` (un `%` literal codificado).
  2. Revisar la lista devuelta.
- **Resultado esperado:** `[]`, porque ningún nombre de libro contiene el carácter `%`.
- **Resultado actual:** devuelve los 3 libros sembrados (`GEN`, `EXO`, `JHN`), porque `%` se interpreta como comodín SQL dentro de `"%{$query}%"` y coincide con cualquier nombre. Lo mismo pasaría con `_`, que coincide con un carácter cualquiera.
- **Evidencia:** `tests/QA/BibleReadingSearchTest.php:116` — `Failed asserting that two strings are equal. --- Expected '[]' +++ Actual '[{ "code": "GEN", "name": "Génesis" }, { "code": "EXO", ... }, { "code": "JHN", ... }]'`.
- **Causa raíz probable:** el valor del usuario se interpola directamente en el patrón `LIKE` sin escapar `%`, `_` ni el carácter de escape, y no hay validación de caracteres permitidos en `q`.
- **Impacto:** el autocompletado muestra sugerencias irrelevantes y puede llenar el selector con libros al azar cuando el usuario escribe un carácter especial. No expone datos ajenos ni modifica información; el límite de 8 resultados sigue vigente.
- **Workaround:** escribir al menos una letra del nombre del libro; evitar `%` y `_` en el campo de búsqueda.
- **Recomendación de corrección:** escapar los metacaracteres (`addcslashes($query, '%_\\')`) y usar `where('name', 'like', '%'.escaped.'%')` con cláusula `escape` explícita, o reemplazar el `LIKE` por una búsqueda con `str_contains` sobre un catálogo acotado de 66 libros.
- **Test que lo reproduce:** `tests/QA/BibleReadingSearchTest.php` → `a percent sign in the book query is treated as a literal character`.
- **Esfuerzo estimado:** S

<a id="bug-008"></a>

### [BUG-008] El grafo genera aristas hacia versículos que no existen
- **Severidad:** S4
- **Prioridad:** P3
- **Frecuencia:** Ocasional (solo si hay referencias cruzadas a versículos ausentes del texto cargado)
- **Tipo:** Funcional / Integridad de datos
- **Módulo / Capa:** Backend / Bíblicas / Grafo
- **Estado de verificación:** CONFIRMADO
- **Ubicación:** `app/Services/BibleGraphService.php:72-95` (`fetchConnectedVerses`) y `:130-143` (`buildEdges`)
- **Entorno:** PHP 8.3.36, Laravel 13.44.1, SQLite `:memory:`, usuario autenticado
- **Precondiciones:** una referencia cruzada cuyo destino no tiene versículo en `spa_rvg`; en el test se usa `XXX 9:9`
- **Pasos para reproducir:**
  1. Insertar `bible_cross_references` de `GEN 1:1` hacia `XXX 9:9` sin crear ese versículo.
  2. Pedir `GET /bible/GEN/1/1` (o `BibleGraphService::buildGraph('GEN', 1, 1)`).
  3. Comparar los extremos de `edges` con los `id` de `nodes`.
- **Resultado esperado:** no se emite una arista apuntando a un nodo ausente: se descarta la referencia colgante o se crea un nodo marcado como “sin texto”.
- **Resultado actual:** `edges` incluye `XXX-9-9` como destino, pero `nodes` solo contiene el centro; la arista queda colgante.
- **Evidencia:** `tests/QA/BibleReadingSearchTest.php:196` — `Failed asserting that two arrays are identical. --- Expected Array &0 [] +++ Actual Array &0 [ 1 => 'XXX-9-9', ]`.
- **Causa raíz probable:** `buildEdges()` mapea todas las filas de `bible_cross_references` sin filtrar por los versículos que `fetchConnectedVerses()` sí pudo cargar. La integridad referencial de la tabla de cross-references no está garantizada por una FK (los extremos son códigos/texto, no claves foráneas).
- **Impacto:** el grafo puede mostrar conexiones que el frontend no puede dibujar o que apuntan a un nodo inexistente; si el corpus cargado está incompleto, se pierde trazabilidad de qué referencias fallaron. No afecta la lectura ni las notas.
- **Workaround:** ninguno desde la UI; revisar/cargar el texto faltante o limpiar la referencia huérfana en base de datos.
- **Recomendación de corrección:** construir primero el conjunto de IDs válidos (centro + conectados cargados) y filtrar `edges` por ambos extremos; opcionalmente registrar las referencias descartadas para detectar importaciones incompletas.
- **Test que lo reproduce:** `tests/QA/BibleReadingSearchTest.php` → `graph edges never point to a node that is missing`.
- **Esfuerzo estimado:** S

<a id="bug-005"></a>

### [BUG-005] Una respuesta a una respuesta queda invisible en el hilo
- **Severidad:** S4
- **Prioridad:** P3
- **Frecuencia:** Ocasional (solo si se responde a una respuesta)
- **Tipo:** Funcional / API
- **Módulo / Capa:** Backend / Social
- **Estado de verificación:** CONFIRMADO
- **Ubicación:** `app/Http/Controllers/PostController.php:125-130` (`show`) y `:148-176` (`reply`)
- **Entorno:** PHP 8.3.36, Laravel 13.44.1, SQLite `:memory:`, usuarios autenticados con slug
- **Precondiciones:** publicación raíz con una respuesta; segundo usuario con permisos para responder
- **Pasos para reproducir:**
  1. Crear una publicación raíz `R`.
  2. Crear una respuesta `A` con `parent_id = R.id`.
  3. Enviar `POST /posts/{A.id}/responder` con `body=Respuesta anidada`. La petición se acepta y crea el registro con `parent_id = A.id`.
  4. Abrir `GET /posts/{R.id}`.
- **Resultado esperado:** la respuesta anidada aparece en el hilo junto a `A`, o la API rechaza responder a una respuesta si el producto solo soporta un nivel.
- **Resultado actual:** el registro se crea pero no aparece: `show()` solo consulta `where('parent_id', $post->id)` sobre la raíz, así que el contenido queda huérfano desde la UI.
- **Evidencia:** `tests/QA/SocialFeedTest.php:157` — `Property [replies.1.id] does not exist.` El front (`resources/js/pages/Posts/Show.vue:61`) solo ofrece responder a la publicación raíz, pero el endpoint no valida el nivel.
- **Causa raíz probable:** el modelo permite profundidad arbitraria en `parent_id`, mientras la lectura del hilo está escrita para exactamente un nivel. No hay validación ni aplanado de la respuesta anidada hacia el padre raíz.
- **Impacto:** contenido publicado que nadie puede ver desde el detalle ni desde el feed (el feed filtra `parent_id IS NULL`). No afecta al flujo normal porque la UI no expone responder a respuestas, pero sí a peticiones directas o a un futuro botón en respuestas.
- **Workaround:** ninguno desde la UI; el autor debe republicar como respuesta directa a la raíz.
- **Recomendación de corrección:** en `reply()`, si `$post->parent_id` no es nulo, usar esa raíz como `parent_id` del nuevo registro (hilo plano de un nivel), o bien rechazar la petición con 422. Elegir una sola política y reflejarla en la UI.
- **Test que lo reproduce:** `tests/QA/SocialFeedTest.php` → `a reply to a reply is visible in the thread`.
- **Esfuerzo estimado:** S

<a id="bug-006"></a>

### [BUG-006] El borrado de notas y favoritos acepta capítulo o versículo 0
- **Severidad:** S4
- **Prioridad:** P3
- **Frecuencia:** Ocasional (solo peticiones con referencia inválida)
- **Tipo:** Funcional / API — validación
- **Módulo / Capa:** Backend / Notas y favoritos
- **Estado de verificación:** CONFIRMADO
- **Ubicación:** `app/Http/Controllers/Bible/NoteController.php:74-80` (`destroy`) y `app/Http/Controllers/Bible/FavoriteController.php:72-78` (`destroy`)
- **Entorno:** PHP 8.3.36, Laravel 13.44.1, SQLite `:memory:`, usuario autenticado
- **Precondiciones:** existir al menos una nota o un favorito del usuario para `GEN 1:1`
- **Pasos para reproducir:**
  1. Crear una nota con `PUT /api/notes` y un favorito con `POST /api/favorites` para `GEN 1:1`.
  2. Enviar `DELETE /api/notes` con `book_code=GEN, chapter=0, verse=1`.
  3. Enviar `DELETE /api/favorites` con `book_code=GEN, chapter=1, verse=0`.
- **Resultado esperado:** 422 con errores de validación en `chapter` / `verse` (`min:1`), igual que responden `upsert()` y `store()`.
- **Resultado actual:** ambos endpoints responden 200 con `{"ok": true}` y no borran nada.
- **Evidencia:** `tests/QA/BibleNotesFavoritesTest.php:121` y `:131` — `Expected response status code [422] but received 200.` La nota y el favorito originales siguen existiendo.
- **Causa raíz probable:** las reglas de `destroy()` son solo `required|string` / `required|integer`, sin `min:1` ni `max:10`, y no se reutilizan las de escritura. No hay un FormRequest compartido.
- **Impacto:** la API contradice su propio contrato y disfraza una referencia inválida como borrado exitoso, lo que dificulta detectar errores del cliente. No hay pérdida de datos ajenos: la consulta filtra por `user_id` y ningún registro válido tiene capítulo/versículo 0.
- **Workaround:** validar `chapter >= 1` y `verse >= 1` en el cliente antes de llamar al endpoint.
- **Recomendación de corrección:** extraer las reglas de referencia a un `FormRequest` o constante compartida y usar `required|integer|min:1` (y `max:10` para `book_code`) en los cuatro métodos de escritura y borrado.
- **Test que lo reproduce:** `tests/QA/BibleNotesFavoritesTest.php` → `deleting a note with a zero chapter is rejected` y `deleting a favorite with a zero verse is rejected`.
- **Esfuerzo estimado:** S

<a id="bug-002"></a>

### [BUG-002] El build falla en una instalación limpia si no existe `.env` con `APP_KEY`
- **Severidad:** S4
- **Prioridad:** P4
- **Frecuencia:** Rara (una vez por instalación nueva)
- **Tipo:** Regresión / Infra
- **Módulo / Capa:** Infra / Build
- **Estado de verificación:** CONFIRMADO → **CORREGIDO y verificado** (ronda 2)
- **Corrección aplicada:** el plugin Wayfinder se omite durante `vite build`; sigue activo en dev para regenerar tipos al cambiar rutas o controladores.
- **Ubicación original:** `vite.config.ts` (plugin `@laravel/vite-plugin-wayfinder`) y `.env.example:3` (`APP_KEY=`)
- **Entorno:** Windows 11, PHP 8.3.36, Node 26.13.0, Laravel 13.44.1, sin `.env`
- **Precondiciones:** `composer install` y `npm ci` ejecutados; archivo `.env` inexistente
- **Pasos para reproducir:**
  1. Partir de un checkout sin `.env`.
  2. Ejecutar `composer install` y `npm ci`.
  3. Ejecutar `npm run build`.
- **Resultado esperado:** el build termina correctamente sin pasos manuales adicionales.
- **Resultado actual:** el plugin invoca `php artisan wayfinder:generate --with-form` y falla con `Illuminate\Encryption\MissingAppKeyException: No application encryption key has been specified.`
- **Evidencia:** salida de `npm run build` con el stack del plugin; además aparece en `storage/logs/laravel.log`: `[2026-11-26 13:27:33] production.ERROR: No application encryption key has been specified.` Tras crear `.env` y ejecutar `php artisan key:generate`, el mismo build pasa en 2,61 s.
- **Causa raíz probable:** el plugin de Wayfinder ejecuta un comando Artisan que resuelve el servicio de cifrado, y ese servicio exige `APP_KEY`. `.env.example` deja la clave vacía y el script `build` no incluye la generación.
- **Impacto:** un desarrollador o pipeline que haga `npm ci && npm run build` desde cero queda trabado; suma un paso manual no documentado en el plan de QA.
- **Workaround:** copiar `.env.example` a `.env` y ejecutar `php artisan key:generate` antes del build.
- **Recomendación de corrección:** documentar el paso en el README de setup o hacer que el plugin no requiera cifrado cuando solo genera tipos (por ejemplo, usar una clave temporal del entorno de build).
- **Test que lo reproduce:** no aplica; se reproduce con comandos de shell.
- **Esfuerzo estimado:** S

<a id="bug-001"></a>

### [BUG-001] Wayfinder regenera archivos versionados cambiando CRLF por LF
- **Severidad:** S5
- **Prioridad:** P4
- **Frecuencia:** Ocasional (cada build en Windows)
- **Tipo:** Regresión / Infra
- **Módulo / Capa:** Frontend / Infra
- **Estado de verificación:** CONFIRMADO → **CORREGIDO y verificado** (ronda 2)
- **Corrección aplicada:** al no ejecutar Wayfinder durante el build, no se reescriben los artefactos versionados. El hash de archivos representativos queda estable antes/después de compilar; el árbol ya tenía cambios generados en la fase de hallazgos, pero el build no agrega nuevos.
- **Ubicación original:** `resources/js/actions/**/*.ts` y `resources/js/routes/**/*.ts` (generados por el plugin de Wayfinder en `vite.config.ts`)
- **Entorno:** Windows 11, Node 26.13.0, Git con conversión CRLF activa
- **Precondiciones:** esos directorios sin cambios en `git status`
- **Pasos para reproducir:**
  1. Verificar `git status --short` sin entradas bajo `resources/js/actions` ni `resources/js/routes`.
  2. Ejecutar `npm run build`.
  3. Volver a ejecutar `git status --short`.
- **Resultado esperado:** el build no modifica archivos versionados.
- **Resultado actual:** aparecen ~70 archivos modificados; `git diff` muestra únicamente cambios de salto de línea CRLF → LF, sin cambios funcionales.
- **Evidencia:** salida de `git status` antes/después del build y `git diff -- resources/js/actions/App/Http/Controllers/PostController.ts` sin diferencias de contenido. El build termina OK.
- **Causa raíz probable:** el generador escribe los archivos con LF mientras la configuración local de Git espera CRLF. No hay una regla que excluya o normalice estos artefactos generados.
- **Impacto:** ruido en el control de versiones, riesgo de incluir artefactos generados en un commit y revisiones más lentas. No afecta la ejecución de la aplicación.
- **Workaround:** después del build, ejecutar `git restore -- resources/js/actions resources/js/routes`.
- **Recomendación de corrección:** normalizar la salida del generador o ignorar esos directorios si se consideran artefactos derivados. La corrección aplicada evita la regeneración en el build; en CI conviene verificar que compilar no introduzca cambios nuevos.
- **Test que lo reproduce:** no aplica; se reproduce con comandos de git.
- **Esfuerzo estimado:** S
