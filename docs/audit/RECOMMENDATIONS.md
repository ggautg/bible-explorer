# Recomendaciones

Ordenadas por prioridad. Cada ítem indica por qué y cómo verificarlo. El estado se actualizó tras la segunda pasada de cambios.

## 1. Actualizar dependencias (alta) — ✅ Hecho

`composer audit` reportaba 80 avisos en 15 paquetes, incluido un bypass crítico de `TokenGuard` en `laravel/framework < 13.27.0`.

- Se actualizaron las dependencias dentro de las restricciones ya declaradas: Laravel 13.20 → 13.44, Fortify 1.37 → 1.44, passkeys/web-auth, Guzzle, CommonMark, Flysystem, Pest, Pint, Wayfinder, etc.
- Verificado: `composer audit` → **0 avisos**; suite 46/46; `phpstan`, `pint`, lint/format, `vue-tsc` y build en verde.
- Queda: repetir la comprobación en el entorno de despliegue.

## 2. Validar en MySQL real (alta) — ⏳ Pendiente

- Ejecutar `php artisan migrate:status` sobre la base real antes de aplicar la migración nueva.
- Tomar un backup: la migración deduplica rebibleos y referencias cruzadas.
- Correr `EXPLAIN` en `bible_cross_references` (direcciones `from_*` y `to_*`) y en las consultas de feed con versículos.
- Verificar: plan de consultas sin full table scans en las rutas más usadas.

## 3. Índices y unicidad (alta/media) — ✅ Hecho, pendiente validación en MySQL

- Agregado índice `(to_book, to_chapter, to_verse)` en `bible_cross_references`.
- Deduplicados rebibleos históricos y creada `unique(user_id, rebible_of_id)`; `toggleRebible()` usa `insertOrIgnore()`.
- Deduplicadas referencias cruzadas y creada unicidad sobre las seis columnas de la relación.
- Verificado: migración ejecutada en SQLite en los tests y suite en verde.

## 4. Seguridad frontend (media) — ✅ Hecho

- Reemplazado `v-html="qrCodeSvg"` en `TwoFactorSetupModal.vue` por `<img :src="data:image/svg+xml,...">`.
- `lib/flashToast.js` valida `data.type` contra `success`, `error`, `info` y `warning`, con fallback a `info`.
- Queda: prueba visual manual del QR en navegador.

## 5. Robustez de búsqueda y grafo (media) — ✅ Parcial

- Agregados `AbortController` y request id en `useVerseSearch.js`, con cancelación al desmontar.
- Reducido el import de D3 a `d3-drag`, `d3-force`, `d3-selection` y `d3-zoom`.
- Limpiados los timers de `PostCard.vue`, `Chapter.vue` y `Graph.vue`.
- Queda: gestos táctiles en el grafo y revisar el agotamiento de `alpha` a los 300 ticks.

## 6. Accesibilidad (media/baja) — ✅ Parcial

- Agregado `aria-pressed` a like, rebibleo y seguir.
- Los nodos del grafo tienen `tabindex`, `role="button"`, `aria-label` y atajos Enter/Espacio.
- Queda: verificar navegación con lector de pantalla en las pantallas principales.

## 7. Consolidación de serialización (media) — ⏳ Pendiente

- Extraer un presentador de publicaciones compartido entre `PostController`, `ProfileController`, `CommunityFeedController` y `ProfileWallController`.
- Centralizar `avatarFor`/`churchFor` (hoy duplicados y con mapeos de testimonios). `avatarFor()` ya reconoce slugs.
- Verificar: tests de feed/perfil/muro y `phpstan`.

## 8. Datos bíblicos (media) — ⏳ Pendiente

- Decidir si favoritos, notas y posts deben guardar la traducción explícita; si es así, migrar datos y actualizar las consultas que hoy fuerzan `spa_rvg`.
- Eliminar `legacy_comment_post_map` cuando se confirme que la migración de likes ya no la necesita.
- Migrar respuestas profundas de `profile_comments` a `posts` si existen en la base real.

## 9. Monitoreo continuo (baja) — ⏳ Pendiente

- Ejecutar lint, format, types, build, tests, Pint, PHPStan y `composer audit` en CI antes de cada merge.
- Mantener `composer audit` como paso programado, no solo manual.

## Estado de estas recomendaciones

Hecho: 1, 3, 4 y partes de 5 y 6. Parcial: 5 y 6. Pendiente: 2, 7, 8 y 9. Ninguna fue descartada sin registrarse.
