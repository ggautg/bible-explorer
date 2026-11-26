# 05 — Frontend Vue / Inertia

Auditoría de componentes Vue, composables, rutas del cliente y manejo de estado. Alcance: `resources/js` y el código de Inertia presente en `node_modules` cuando fue necesario confirmar un comportamiento.

## Resumen de la capa

| Severidad | Cantidad | IDs |
|---|---:|---|
| Media | 1 | FE-001 |
| Baja | 1 | FE-002 |
| Informativa | 1 | FE-003 |

---

## FE-001 El estado de notas y favoritos sobrevive al cambio de usuario en la misma pestaña

- **Severidad:** Media
- **Capa:** Frontend / Privacidad
- **Referencia:** OWASP A01:2021 / CWE-488
- **Ubicación:** `resources/js/composables/useNotes.js:7-9,24-30,37-39`, `resources/js/composables/useFavorites.js:8-10,25-31,38-40`, `resources/js/components/UserMenuContent.vue:19-22,43-53`, `resources/js/routes/index.ts:85`
- **Descripción:** `notes`, `favorites`, `loaded` y `loading` son variables de módulo (singleton del bundle). Solo se cargan una vez por sesión de JavaScript. El cierre de sesión se hace con un `Link` de Inertia (POST) y `router.flushAll()`, que según el core de Inertia solo limpia el caché de prefetch, no reinicia variables de módulo.
- **Evidencia:**
  - `useNotes.js:7-9`: `const notes = ref([]); const loaded = ref(false); const loading = ref(false);` a nivel de módulo.
  - `useNotes.js:37-39`: `if (typeof window !== 'undefined' && !loaded.value && !loading.value) { void loadNotes(); }`.
  - `useFavorites.js:8-10,38-40`: mismo patrón.
  - `UserMenuContent.vue:20-22`: `const handleLogout = () => { router.flushAll(); };`
  - `UserMenuContent.vue:44-50`: `<Link :href="logout()" @click="handleLogout" as="button">`.
  - `resources/js/routes/index.ts:85`: `logout` es una ruta `post` (`RouteDefinition<'post'>`), es decir, visita Inertia, no recarga completa.
  - `node_modules/@inertiajs/core/dist/index.js:3222-3224`: `flushAll() { prefetchedRequests.removeAll(); }`.
- **Impacto:** Si la persona A cierra sesión y la persona B inicia sesión **sin recargar la pestaña**, B puede ver las notas y favoritos privados de A hasta que haga una recarga completa (F5). El backend sigue sirviendo solo los datos de B en la API, pero el caché del cliente ya tiene los de A.
- **Escenario práctico:** En una computadora compartida, A guarda una nota médica, cierra sesión, entra B; la vista `/notas` muestra la nota de A porque `loaded=true` y el compositor no vuelve a pedir datos.
- **Corrección:** Reiniciar estos singletons al cerrar sesión y al detectar cambio de `page.props.auth.user.id` (watch global en `app.js`), o almacenar el `userId` junto al caché y recargar si cambia. Alternativamente, forzar `window.location.assign('/')` en el cierre de sesión para garantizar recarga completa.
- **Ejemplo:**
  - Antes: `if (!loaded.value) void loadNotes();`
  - Después: `if (!loaded.value || loadedUserId.value !== currentUserId.value) { notes.value = []; loaded.value = false; await loadNotes(); loadedUserId.value = currentUserId.value; }`
- **Esfuerzo:** S
- **Estado:** Verificado en código. Reproducción real en navegador `NO VERIFICADO` (requiere dos cuentas y sesión compartida).

## FE-002 Errores de las APIs de notas/favoritos quedan sin manejar

- **Severidad:** Baja
- **Capa:** Frontend / Robustez
- **Referencia:** OWASP A04:2021 / CWE-390
- **Ubicación:** `resources/js/composables/useNotes.js:24-30,47-70,73-86`, `resources/js/composables/useFavorites.js:25-31,48-78`
- **Descripción:** `loadNotes()`/`loadFavorites()` tienen `try/finally` sin `catch`. Las mutaciones (`saveNote`, `deleteNote`, `toggleFavorite`) tampoco capturan errores. Una respuesta 401/422/500 produce un `PromiseRejection` sin aviso visual y deja el estado local inconsistente.
- **Evidencia:**
  - `useNotes.js:24-30`: `try { const { data } = await axios.get('/api/notes'); ... } finally { loading.value = false; }` sin `catch`.
  - `useNotes.js:60`: `const { data } = await axios.put('/api/notes', payload);` sin `try/catch` y con actualización local inmediata.
  - `useFavorites.js:76-77`: `const { data } = await axios.post('/api/favorites', payload); favorites.value = [data, ...favorites.value];` sin verificación de éxito.
- **Impacto:** Si expira la sesión o el servidor devuelve 422 (por ejemplo, versículo inexistente), la UI puede mostrar una nota o favorito que no se guardó, o simplemente no dar feedback. Dificulta el diagnóstico y genera rechazos en consola.
- **Escenario práctico:** La persona escribe una nota, hay un 500, `saveNote()` lanza, el componente no muestra error y el contador local ya cambió.
- **Corrección:** Envolver cada llamada en `try/catch`, mostrar un toast con `error.response?.data?.message ?? 'No se pudo guardar'` y revertir el cambio optimista si falla. Centralizar el manejo en un interceptor de axios.
- **Ejemplo:**
  - Antes: `const { data } = await axios.put('/api/notes', payload);`
  - Después: `try { const { data } = await axios.put(...); ... } catch (e) { toast.error(...); }`
- **Esfuerzo:** S
- **Estado:** Verificado en código. Frecuencia real `NO VERIFICADO`.

## FE-003 La carrera de `loadStructure()` puede apagar el indicador de carga antes de tiempo

- **Severidad:** Informativa
- **Capa:** Frontend / Corrección
- **Referencia:** CWE-362
- **Ubicación:** `resources/js/components/Social/VerseReferencePicker.vue:144-181`
- **Descripción:** `loadStructure()` cancela la petición anterior con `AbortController`, pero el bloque `finally` siempre escribe `loadingStructure.value = false`, incluso cuando la petición vigente sigue en curso. A diferencia de la búsqueda de libros, acá no hay `requestId`.
- **Evidencia:**
  - `VerseReferencePicker.vue:145-149`: `structureController?.abort(); structureController = new AbortController(); loadingStructure.value = true;`
  - `VerseReferencePicker.vue:178-180`: `} finally { loadingStructure.value = false; }`
  - Comparación: la búsqueda sí usa `requestId === suggestRequestId` en `VerseReferencePicker.vue:92,97,103`.
- **Impacto:** Al cambiar rápido de libro o capítulo, la UI puede dejar de mostrar "cargando" mientras la segunda respuesta todavía llega. Es un problema de percepción, no de seguridad ni de datos.
- **Escenario práctico:** Elegir Juan, cambiar a Génesis antes de que responda Juan → el spinner se apaga con la respuesta abortada y los capítulos pueden quedar desactualizados por un instante.
- **Corrección:** Usar el mismo patrón `requestId` que en `watch(bookQuery)` y solo actualizar el indicador/estado si el id sigue siendo el vigente.
- **Ejemplo:** `const id = ++structureRequestId; ... finally { if (id === structureRequestId) loadingStructure.value = false; }`
- **Esfuerzo:** S
- **Estado:** Verificado en código.

## Prácticas correctas verificadas en esta capa

- No hay `v-html`, `innerHTML`, `outerHTML`, `document.write`, `new Function`, `eval` ni `target="_blank"` en `resources/js` (`grep` → 0 coincidencias). Vue escapa por defecto las interpolaciones `{{ }}`.
- Las URLs de compartir se construyen desde `window.location.origin` más códigos validados en servidor: `PostCard.vue:64`, `Bible/Chapter.vue:66`, `Bible/Graph.vue:69`.
- Las peticiones `fetch` usan `encodeURIComponent`/`URLSearchParams` y `AbortController` con debounce: `useVerseSearch.js:42-45,86-88`, `VerseReferencePicker.vue:86-89,151-160`.
- `useAppearance.js:45-65` valida el tema con un conjunto de ids permitidos antes de escribirlo en `data-theme`; cualquier valor manipulado cae a `getsemani`.
- Las cookies de tema usan `SameSite=Lax` y están explícitamente excluidas del cifrado de Inertia (`useAppearance.js:80`, `bootstrap/app.php:20`), coherente con su naturaleza no sensible.
- Axios (usado por `useNotes`/`useFavorites`) agrega automáticamente `X-XSRF-TOKEN` desde la cookie `XSRF-TOKEN` que emite Laravel; las mutaciones van por POST/PUT/DELETE con middleware `web`.
- El cliente envía solo `book_code`/`chapter`/`verse`; `book_name` y `text` se derivan en servidor (`VerseMetadata`). Aunque el cliente también incluye esos campos en notas/favoritos, el backend los ignora de forma segura.
- `useExplorationTrail.js:9-27` guarda el recorrido del grafo en `sessionStorage` con manejo de errores, sin persistir datos personales.

## Límites de la verificación

- No se probó el flujo completo de dos cuentas en un navegador real; FE-001 se infiere del código y del comportamiento documentado de Inertia.
- No se revisaron cabeceras HTTP reales ni CSP efectiva en un despliegue.
- No se ejecutaron pruebas de accessibility ni de rendimiento de renderizado.
