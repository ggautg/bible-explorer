# 03 — Bugs S2 (Altos / Mayores)

## Índice
- [Estado](#estado)
- [BUG-003 — Usuarios nuevos sin slug y perfil en 404](#bug-003)

## Estado
No hay defectos S2 abiertos. BUG-003 (Fase 2) fue corregido en la ronda de corrección 1 y verificado con la suite QA en verde. Las Fases 3 a 6 no agregaron defectos S2.

<a id="bug-003"></a>

### [BUG-003] Los usuarios recién registrados no reciben slug y su perfil devuelve 404
- **Severidad:** S2
- **Prioridad:** P1
- **Frecuencia:** Siempre (cada registro nuevo)
- **Tipo:** Funcional / Integridad de datos
- **Módulo / Capa:** Backend / Autenticación / Perfil
- **Estado de verificación:** CONFIRMADO → **CORREGIDO y verificado** (ronda de corrección 1)
- **Corrección aplicada:** `App\Models\User::booted()` genera el slug en `creating` cuando viene vacío, con sufijo único.
- **Ubicación original:** `app/Actions/Fortify/CreateNewUser.php:27-31` y `routes/web.php:29` (`/perfil/{user:slug}`)
- **Entorno:** PHP 8.3.36, Laravel 13.44.1, SQLite `:memory:`, usuario recién registrado, Fortify con registro habilitado
- **Precondiciones:** registro nuevo con nombre con acentos, por ejemplo `María José Ñandú`
- **Pasos para reproducir:**
  1. Enviar `POST /register` con `name=María José Ñandú`, `email=maria@example.com`, `password=password` y confirmación.
  2. Verificar el registro en la base: `slug` queda en `NULL`.
  3. Abrir el enlace que construye `NavHeader.vue` (`/perfil/${currentUser.slug}`) o cualquier `PostCard` del feed.
  4. Solicitar `GET /perfil/` resultante.
- **Resultado esperado:** el usuario recibe un slug único derivado del nombre (p. ej. `maria-jose-nandu`) y su perfil responde 200.
- **Resultado actual:** `slug` es `NULL` y la ruta de perfil responde **404**; los enlaces del header y de las publicaciones apuntan a `/perfil/`.
- **Evidencia:**
  - `tests/QA/AuthSecurityTest.php:29` — `Expecting null not to be null .`
  - `tests/QA/AuthSecurityTest.php:48` — `Expected response status code [200] but received 404.`
  - Búsqueda de `slug` en `app/`: solo se lee en controladores; `CreateNewUser` no lo asigna. La migración `2026_11_19_085830_add_slug_to_users_table.php` genera slugs solo para usuarios **existentes** al momento de migrar.
  - Frontend: `resources/js/components/Bible/NavHeader.vue:34`, `resources/js/components/Social/PostCard.vue:98,113,139`, `resources/js/pages/Posts/Show.vue:38` interpolan `slug` directamente.
- **Causa raíz probable:** la columna `slug` se agregó en una migración que calcula valores para el stock inicial de usuarios, pero no se agregó ningún *observer*, *hook* de modelo ni asignación en `CreateNewUser`. El campo es nullable y único, así que múltiples usuarios pueden quedar con `NULL` sin violar la restricción.
- **Impacto:** cada usuario nuevo tiene un perfil inalcanzable; el enlace “Mi perfil” del header y los enlaces al autor en el feed y en los posts llevan a 404. Afecta al flujo social principal desde el primer registro y a la navegación básica. No hay workaround desde la UI.
- **Workaround:** ninguno desde la interfaz. Solo asignar el slug manualmente en base de datos o por tinker.
- **Recomendación de corrección:** generar el slug en `CreateNewUser` antes de crear el registro (usando `Str::slug($input['name'])` con sufijo iterativo mientras exista), o centralizarlo en un observer `creating` del modelo `User` para cubrir también registros creados por factories/seeders. Hacer la columna `NOT NULL` después de backfill.
- **Test que lo reproduce:** `tests/QA/AuthSecurityTest.php` → `registered users receive a slug that matches their name` y `a newly registered user can open the profile link built by the frontend`.
- **Esfuerzo estimado:** S
