# Informe de rendimiento

## Alcance

Consultas N+1, carga de relaciones, agregación de datos en servidor, tamaño de bundle y frecuencia de peticiones. No se tomaron mediciones de producción ni `EXPLAIN` en MySQL real.

## Corregido

| Hallazgo | Antes | Después |
| --- | --- | --- |
| Textos bíblicos en feed social | Una consulta por post con versículo (`serializePostCore`) | `VerseMetadata::textsFor()` resuelve todos los textos en una consulta por lote. |
| Textos bíblicos en feed de comunidad | Una consulta por comentario (`verseTextFor`) | Misma resolución por lote. |
| Textos bíblicos en muro de perfil | Una consulta por comentario y por respuesta | Misma resolución por lote. |
| Likes/rebibleos del visor | Consulta por post en `serializePost` | `viewerStateFor()` ya precargaba ambos conjuntos; se mantuvo y se documentó. |
| Peticiones de escritura sin límite | Sin throttle | `throttle:15,1` en acciones sociales y `throttle:30,1` en APIs de favoritos/notas. |
| Búsqueda con debounce sin cancelación | `setTimeout` + `fetch` sin abortar | `useVerseSearch.js` usa `AbortController` + `requestId` y cancela al desmontar. |
| Import completo de D3 | `import * as d3 from 'd3'` | Se importan solo `d3-drag`, `d3-force`, `d3-selection` y `d3-zoom`. |
| Timers de componentes sin cleanup | `setTimeout` sin limpiar | `PostCard.vue`, `Chapter.vue` y `Graph.vue` limpian su timer en `onUnmounted`. |
| Grafo sin acceso por teclado | Solo mouse | Los nodos tienen `tabindex`, `role="button"`, `aria-label` y atajos Enter/Espacio. |
| Referencias entrantes del grafo sin índice | Solo `from_*` indexado | Nuevo índice `bible_cross_refs_to_reference_index`. |

Detalle: `App\Services\VerseMetadata::textsFor()` construye un único `SELECT` con condiciones `OR` agrupadas por `(book_code, chapter, verse)` y filtra por `translation = spa_rvg`. Las claves se normalizan con `VerseMetadata::key()`, de modo que la serialización no necesita consultas adicionales.

## No corregido

| Hallazgo | Impacto | Detalle |
| --- | --- | --- |
| `fetchConnectedVerses()` con muchas `orWhere` | Medio | `BibleGraphService` construye una condición por referencia; con 300 conexiones la consulta crece. Ya hay límite de conexiones por dirección. |
| Grafo sin soporte táctil y con 300 ticks | Bajo | Ya se navega por teclado, pero no hay gestos táctiles y `alpha` se agota tras 300 ticks. |
| Timers globales de `useToast.js` | Bajo | Los temporizadores viven en el módulo compartido durante toda la sesión; son intencionales, pero no se cancelan al ocultar la app. |
| Lecturas sin límite en listados de personas | Bajo | `CommunityFeedController` y perfiles obtienen usuarios con `get()` sin `limit()`. |

## Verificado

- `npm run build`: exitoso, con chunks divididos por página. Los archivos más grandes son `createLucideIcon` (~153 kB), `runtime-core` (~85 kB) y `Graph` (~74 kB), todos gzip < 50 kB.
- `npm run types:check`, `npm run lint:check` y `npm run format:check` pasan; no hay errores que bloqueen el build.
- Tests de regresión cubren los cambios de lote y de throttling de forma funcional, no de rendimiento.

## No verificable

- Tiempos de respuesta reales, `EXPLAIN`, hit ratio de caché y comportamiento bajo concurrencia: requieren MySQL con datos representativos y entorno de prueba de carga.
- Impacto real del tamaño de bundle en usuarios con conexiones lentas: requiere medición con Lighthouse o similar.

## Recomendaciones

1. **Hecho:** `AbortController`/request id en `useVerseSearch.js`.
2. **Hecho:** import de D3 reducido a submódulos y timers de componentes limpiados en `onUnmounted`.
3. **Hecho:** índice `bible_cross_references.to_*`; queda medir con `EXPLAIN` en MySQL real.
4. Paginar listados de personas si el número de usuarios crece.
5. Agregar gestos táctiles al grafo si se usa en mobile.
