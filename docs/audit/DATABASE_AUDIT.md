# Auditoría de base de datos y migraciones

## Alcance

Esquema en `database/migrations/`, seeders, consultas de modelos/controladores y servicios. La auditoría estática se hizo sin MySQL; al final se conectó a MySQL 8.0.35 y se aplicaron las migraciones pendientes.

## Esquema relevante

| Tabla | Claves / índices verificados |
| --- | --- |
| `bible_verses` | `unique(book_code, chapter, verse)` y `unique(translation, book_code, chapter, verse)`; índice `(translation, book_code, chapter)`; FULLTEXT sobre `text` solo en MySQL. |
| `bible_cross_references` | índice `(from_book, from_chapter, from_verse)`; sin índice en dirección entrante; columnas `score` y `to_verse_end` agregadas después. |
| `posts` | índices `(user_id, created_at)`, `(parent_id, created_at)` y `rebible_of_id`; sin unicidad para `(user_id, rebible_of_id)`. |
| `post_likes` | `unique(user_id, post_id)` + índice `post_id`. |
| `follows` | `unique(follower_id, followed_id)` + índice `followed_id`. |
| `profile_comment_reactions` | `unique(profile_comment_id, user_id, type)` + índice `(profile_comment_id, type)`. |
| `favorites` / `notes` | `unique(user_id, book_code, chapter, verse)` en ambas. |

## Hallazgos y estado

| Hallazgo | Estado | Detalle |
| --- | --- | --- |
| `fullText()` falla bajo SQLite | **Corregido** | Guard por driver en la migración FULLTEXT; la búsqueda `LIKE` sigue disponible en otros drivers. |
| `down()` vacío en migración de `score`/`range` | **Corregido** | Ahora elimina `to_verse_end` y `score`. |
| Toggle de like con `exists()` + `attach()` | **Corregido** | `delete()` + `insertOrIgnore()` sobre la clave única. |
| Toggle de seguimiento con check-then-write | **Corregido** | Mismo patrón sobre `follows`. |
| Reacciones con check-then-write | **Corregido** | Mismo patrón sobre `profile_comment_reactions`. |
| Metadatos de favoritos/notas fiados al cliente | **Corregido** | `VerseMetadata` deriva nombre y texto desde la BD. |
| N+1 de textos bíblicos en feeds/muros | **Corregido** | `VerseMetadata::textsFor()` resuelve lote en una consulta. |
| Seed no idempotente | **Corregido** | `firstOrCreate()` en `DatabaseSeeder`. |
| `bible_cross_references` sin índice entrante | **Corregido** | Nueva migración agrega `bible_cross_refs_to_reference_index` sobre `(to_book, to_chapter, to_verse)`. |
| `bible_cross_references` sin unicidad | **Corregido** | La migración elimina duplicados exactos (conserva el menor `id`) y crea `bible_cross_refs_unique_reference`. |
| Rebibleos sin clave única | **Corregido** | La migración deduplica pares `(user_id, rebible_of_id)` y crea `posts_user_rebible_unique`; `toggleRebible()` usa `insertOrIgnore()`. |
| "Traducción" no propagada a favoritos/notas/posts | **No corregido** | Esas tablas guardan solo `book_code/chapter/verse`; si cambia la traducción por defecto, se resuelve `spa_rvg`. Requiere decisión de producto y migración de datos. |
| Lecturas sin `limit()` | **No corregido** | `User::query()->get()` en `CommunityFeedController` y listados de perfil; con muchos usuarios puede crecer. Se mitigó parcialmente con `limit(50/60)` en feeds. |
| Migración legacy sin niveles profundos | **No corregido** | La migración de `profile_comments` a `posts` solo maneja un nivel de `parent_id`. |
| `EXPLAIN` en MySQL real | **No verificable** | Se verificó que no hay duplicados, pero no se midió el plan de consultas. |

## Observaciones

- Las migraciones de datos usan `hasTable()` para adaptarse a instalaciones legacy; eso es correcto, pero hace que el resultado dependa del estado real de la BD, no verificable aquí.
- `2026_11_19_130759_create_posts_table.php` crea una tabla auxiliar `legacy_comment_post_map` para migrar likes. Es temporal y queda en el esquema; conviene eliminarla en una migración posterior cuando se confirme que no se necesita.
- Se aplicaron correcciones de estilo con Pint en 9 migraciones, sin cambios de comportamiento salvo los documentados.
- La nueva migración `2026_11_26_000001_add_integrity_indexes_to_bible_explorer.php` es reversible y usa `down()` para quitar los tres índices/unicidades creados. Se aplicó en MySQL 8.0.35 sobre 344.799 referencias y 0 rebibleos duplicados; no borró filas. Para MySQL se usa un `derived table` en el `DELETE` porque la subconsulta directa sobre la misma tabla falla con el error 1093.

## Recomendaciones

1. **Hecho:** agregar índice `(to_book, to_chapter, to_verse)` en `bible_cross_references` y verificar duplicados.
2. **Hecho:** deduplicar rebibleos y crear la clave única; `toggleRebible()` ya usa `insertOrIgnore()`.
3. Decidir si favoritos/notas/posts deben guardar `translation` explícita.
4. Ejecutar `migrate:status` y `EXPLAIN` sobre MySQL con datos reales para confirmar que los nuevos índices se usan.
