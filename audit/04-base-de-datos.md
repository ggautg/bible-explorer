# 04 — Base de datos

Auditoría del esquema, migraciones, índices, integridad referencial y datos locales de MySQL 8.0.35. Solo se ejecutaron consultas de lectura; los números se obtuvieron con `COUNT(*)` y `SHOW INDEX`, no con estimaciones de `information_schema.table_rows`.

## Instantánea local verificada

| Medición | Valor |
|---|---:|
| Libros (`bible_books`) | 66 |
| Versículos (`bible_verses`, `translation='spa_rvg'`) | 31.102 |
| Referencias cruzadas (`bible_cross_references`) | 344.799 |
| Usuarios (`users`) | 1 |
| Publicaciones (`posts`) | 5 |
| Seguimientos (`follows`) | 6 |
| Huérfanos `to_book` / `to_verse` | 0 / 0 |
| Referencias de publicaciones sin versículo | 0 |
| `legacy_comment_post_map` / `profile_comments` / `profile_comment_reactions` | 0 / 0 / 0 |

Nota: `information_schema.tables.table_rows` mostró 332.626 para referencias cruzadas; es una estimación del optimizador. El valor exacto de auditoría es el `COUNT(*)` de 344.799.

## Resumen de la capa

| Severidad | Cantidad | IDs |
|---|---:|---|
| Media | 1 | DB-001 |
| Baja | 2 | DB-002, DB-003 |
| Informativa | 1 | DB-004 |

---

## DB-001 El importador de referencias cruzadas no es idempotente

- **Severidad:** Media
- **Capa:** Base de datos / Carga de datos
- **Referencia:** OWASP A04:2021 / CWE-664
- **Ubicación:** `app/Console/Commands/SeedBibleCrossReferences.php:93-120`, `database/migrations/2026_11_26_000001_add_integrity_indexes_to_bible_explorer.php:62-69`
- **Descripción:** `storeChapterReferences()` usa `insert()` plano para cada lote. Hoy existe el índice único `bible_cross_refs_unique_reference`, así que una reejecución del comando choca con `duplicate key`; antes de esa migración, sí generaba duplicados. Tampoco hay transacción por capítulo.
- **Evidencia:**
  - `SeedBibleCrossReferences.php:118-120`: `if ($rows !== []) { DB::table('bible_cross_references')->insert($rows); }`
  - `SeedBibleCrossReferences.php:104-114`: cada fila incluye `to_verse_end` y `score`, que **no** forman parte de la clave única.
  - `2026_11_26_000001:64-67`: `$table->unique(['from_book','from_chapter','from_verse','to_book','to_chapter','to_verse'], 'bible_cross_refs_unique_reference')`.
  - `2026_11_26_000001:46-49`: la deduplicación previa conserva `MIN(id)`, sin considerar el `score` más alto.
- **Impacto:** Un operador que vuelva a correr `php artisan bible:seed-crossrefs` tras una interrupción obtiene un error SQL a mitad del proceso y deja carga parcial. Si se eliminara el índice, se duplicarían 344.799 filas. Además, al haber dos referencias con distinto `score`, se conserva la más antigua, no la más relevante.
- **Escenario práctico:** Se interrumpe la importación en el capítulo 20 y se reintenta → el `INSERT` falla por la restricción única; la tabla queda con capítulos 1-19 y el comando termina en error.
- **Corrección:** Usar `insertOrIgnore()` o `upsert()` con la clave única, envolver cada capítulo en `DB::transaction()` y, en la deduplicación, elegir la fila con `MAX(score)` en vez de `MIN(id)`. Registrar cuántas filas se ignoraron.
- **Ejemplo:**
  - Antes: `DB::table('bible_cross_references')->insert($rows);`
  - Después: `DB::table('bible_cross_references')->insertOrIgnore($rows);` dentro de `DB::transaction(fn () => ...)`.
- **Esfuerzo:** S
- **Estado:** Verificado en código y en el índice real de MySQL local. Comportamiento en producción `NO VERIFICADO`.

## DB-002 Referencias cruzadas sin claves foráneas hacia libros/versículos

- **Severidad:** Baja
- **Capa:** Base de datos / Integridad
- **Referencia:** OWASP A04:2021 / CWE-664
- **Ubicación:** `database/migrations/2026_07_21_171033_create_bible_cross_references_table.php:14-26`
- **Descripción:** La tabla define `from_book`, `to_book`, `from_chapter`, `from_verse` y `to_verse`, pero no declara `foreign()` hacia `bible_books` ni `bible_verses`. La integridad depende de que el importador y el grafo hagan la validación por aplicación.
- **Evidencia:**
  - `create_bible_cross_references_table.php:16-25`: columnas e índice `['from_book','from_chapter','from_verse']`; ninguna línea `foreign(...)`.
  - Consulta local de solo lectura: `select count(*) as orphan_to_book from bible_cross_references bcr left join bible_books b on b.code=bcr.to_book where b.code is null` → `0`; `orphan_to_verse` → `0`.
  - `BibleGraphService.php:84-94` solo consulta versículos conectados; si falta el destino, el nodo simplemente no aparece.
- **Impacto:** Una versión futura del dataset con códigos mal escritos insertaría aristas apuntando a versículos inexistentes; el grafo perdería nodos sin aviso y la unicidad no lo detectaría.
- **Escenario práctico:** El tercero publica `to_book='JHNx'` → se inserta la fila, `fetchConnectedVerses()` no encuentra el versículo y la UI dibuja una arista sin destino.
- **Corrección:** Agregar claves foráneas hacia `bible_books(code)` para `from_book`/`to_book` y validar la existencia de `(translation, book_code, chapter, verse)` en el sembrador antes de insertar. Si se teme el costo de claves foráneas en 344k filas, al menos agregar un test de integridad que falle con huérfanos.
- **Ejemplo:** `$table->foreign('to_book')->references('code')->on('bible_books');` (con `from_book` análogo).
- **Esfuerzo:** M
- **Estado:** Verificado en esquema y datos locales. Dataset de producción `NO VERIFICADO`.

## DB-003 Tablas del muro legacy quedan como esqueleto sin consumidor activo

- **Severidad:** Baja
- **Capa:** Base de datos / Limpieza
- **Referencia:** CWE-1164
- **Ubicación:** `database/migrations/2026_11_19_113255_create_profile_comments_table.php`, `database/migrations/2026_11_19_114153_create_profile_comment_reactions_table.php`, `database/migrations/2026_11_19_130759_create_posts_table.php:108-121`
- **Descripción:** `profile_comments`, `profile_comment_reactions` y `legacy_comment_post_map` siguen en el esquema y son leídas por los controladores huérfanos detectados en BE-006. Las consultas exactas locales devuelven 0 filas en las tres.
- **Evidencia:**
  - `create_profile_comments_table.php:14-27` y `create_profile_comment_reactions_table.php` crean las tablas con claves foráneas a `users`.
  - `create_posts_table.php:108-121`: crea `legacy_comment_post_map` para migrar "me gusta" y nunca la limpia en `up()`.
  - `SHOW INDEX`/`information_schema.tables` confirman que las tres tablas existen con engine InnoDB.
  - Consultas exactas: `legacy_comment_post_map = 0`, `profile_comments = 0`, `profile_comment_reactions = 0`.
  - `CommunityFeedController.php:36-52` sigue consultando `ProfileComment` aunque no está rutado (BE-006).
- **Impacto:** Esquema engañoso: un despliegue futuro podría migrar "me gusta" desde tablas vacías o activar el controlador viejo y mostrar un feed vacío. Ocupa espacio mínimo pero aumenta la superficie de mantenimiento.
- **Escenario práctico:** Se reactiva `CommunityFeedController` creyendo que es la fuente de datos real → el feed sale vacío porque `posts` tiene 5 filas y `profile_comments` 0.
- **Corrección:** Confirmar con el equipo y eliminar las tres tablas en una migración nueva con `down()` que las recree vacías; borrar también los modelos/controladores huérfanos (BE-006). Conservar el backup `bible_explorer_backup_20261126_093031.sql` antes de hacerlo.
- **Ejemplo:** `Schema::dropIfExists('legacy_comment_post_map'); Schema::dropIfExists('profile_comment_reactions'); Schema::dropIfExists('profile_comments');`
- **Esfuerzo:** S
- **Estado:** Verificado en local. Existencia de datos en producción `NO VERIFICADO`.

## DB-004 El tipo de publicación no tiene restricción a nivel de base de datos

- **Severidad:** Informativa
- **Capa:** Base de datos / Integridad
- **Referencia:** CWE-20
- **Ubicación:** `database/migrations/2026_11_26_100000_add_kind_to_posts_table.php:17-19`, `app/Http/Controllers/PostController.php:355`, `app/Models/Post.php:30-46`
- **Descripción:** `posts.kind` es `string(24)` con default `reflexion`. La lista de valores válidos vive solo en `Post::KINDS`/`SELECTABLE_KINDS` y en la validación del controlador; MySQL no tiene `CHECK` ni `ENUM`.
- **Evidencia:**
  - `add_kind_to_posts_table.php:18`: `$table->string('kind', 24)->default('reflexion')->after('rebible_of_id');`
  - `PostController.php:355`: `in_array($post->kind, Post::KINDS, true) ? $post->kind : 'reflexion'` (fallback en serialización).
  - `PostController.php:82`: `'kind' => ['nullable', Rule::in(Post::SELECTABLE_KINDS)]` (validación solo en `store()`).
- **Impacto:** Un insert directo o un script de mantenimiento puede escribir `kind='otro'`; la UI lo muestra como `reflexion`, por lo que no hay ruptura visual, pero las estadísticas por tipo quedarían incorrectas.
- **Escenario práctico:** Un seeder escribe `kind='rebibleada'` manualmente en una publicación sin `rebible_of_id`; el serializador lo respeta porque está en `KINDS`, y la publicación aparece etiquetada como rebibleo sin serlo.
- **Corrección:** Agregar un `CHECK` en MySQL 8 (`CHECK (kind IN (...))`) o un `ENUM`, o centralizar la normalización en un mutador del modelo que fuerce valores válidos antes de guardar.
- **Ejemplo:** `$table->enum('kind', Post::KINDS)->default('reflexion');` (si se acepta acoplar la migración al modelo).
- **Esfuerzo:** S
- **Estado:** Verificado en esquema y código. Inserciones externas `NO VERIFICADO`.

## Integridad y buenas prácticas verificadas

- Todas las tablas usan InnoDB y `utf8mb4_unicode_ci` (`information_schema.tables`); `sql_mode` local incluye `ONLY_FULL_GROUP_BY, STRICT_TRANS_TABLES, NO_ZERO_IN_DATE, NO_ZERO_DATE, ERROR_FOR_DIVISION_BY_ZERO, NO_ENGINE_SUBSTITUTION`.
- Unicidad real verificada con `SHOW INDEX`: `bible_verses_translation_book_code_chapter_verse_unique`, `bible_cross_refs_unique_reference`, `posts_user_rebible_unique`, `favorites_user_id_book_code_chapter_verse_unique`, `notes_user_id_book_code_chapter_verse_unique`, `post_likes(user_id,post_id)` y `follows(follower_id,followed_id)`.
- Índices para el grafo en ambas direcciones: `bible_cross_references_from_book_from_chapter_from_verse_index` y `bible_cross_refs_to_reference_index`.
- Índice FULLTEXT presente en `bible_verses.text` y consultas con binding (`add_fulltext_index...php:16-22`, `TextSearchController.php:44-45`).
- Claves foráneas con `cascadeOnDelete()` en datos de usuario: publicaciones, "me gusta", seguimientos, favoritos, notas, comentarios y sesiones (`create_posts_table.php:18-20`, `create_post_likes_table.php:17-18`, `create_follows_table.php:16-17`, etc.).
- `jobs`, `job_batches` y `failed_jobs` existen (`create_jobs_table.php:14-47`), coherente con `QUEUE_CONNECTION=database`.
- El sembrador de texto bíblico usa `upsert()` por lotes de 500 con clave `(translation, book_code, chapter, verse)` (`SeedBibleText.php:91-97`), por lo que sí es reanudable.
- Verificación real de contenido: Juan (`JHN`) tiene 21 capítulos; Juan 3 tiene 36 versículos e incluye el 16.
- `php artisan migrate:status` confirma las 21 migraciones aplicadas, incluidas `2026_11_26_000001_add_integrity_indexes...` y `2026_11_26_100000_add_kind_to_posts_table`.
- `AppServiceProvider.php:50-52` prohíbe comandos destructivos de base de datos cuando `app()->isProduction()`.

## Límites de la verificación

- No se ejecutó `EXPLAIN` sobre las consultas del grafo ni sobre la búsqueda FULLTEXT; el rendimiento real `NO VERIFICADO`.
- No se consultó un dataset de producción; los 0 huérfanos aplican solo a la base local.
- No se verificaron copias de seguridad, RTO/RPO ni políticas de retención de MySQL.
