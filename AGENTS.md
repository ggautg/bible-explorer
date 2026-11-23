# AGENTS.md — Explorador Bíblico

Guía rápida y actualizada para agentes que trabajen en este repositorio.

## Contexto del producto
- **Nombre:** Explorador Bíblico (`bible-explorer`).
- **Dominio:** red social cristiana con herramientas bíblicas (lectura, búsqueda, grafo, notas, favoritos, devocionario).
- **Idioma de la interfaz y documentación:** español rioplatense, claro y respetuoso.
- **Stack:** Laravel 13 + Inertia 3 + Vue 3 (JavaScript, sin TS en páginas/componentes nuevos salvo los ya existentes) + Tailwind CSS 4 + MySQL.

## Comandos útiles
```bash
# Backend
composer install
php artisan key:generate
php artisan migrate
php artisan test
./vendor/bin/pint --dirty

# Frontend
npm ci
npm run dev
npm run build
```

## Base de datos local
- Conexión: MySQL local.
- DB: `bible_explorer`
- Host: `127.0.0.1:3306`
- Usuario: `root`
- Password: `ionysmaster666`
- Configurar en `.env` (no commitear credenciales reales en archivos versionados).

## Arquitectura y convenciones
- **Rutas:** `routes/web.php` para web/social/biblia; `routes/settings.php` para configuración.
- **Controladores:** delgados; validan entrada, coordinan modelos y devuelven Inertia/redirect/JSON.
- **Modelos:** documentar propósito, atributos clave, relaciones y casts.
- **Vistas Vue:** usar `BiblePageShell` para páginas autenticadas del producto. Mantener `max-w-*` coherente y layout fluido.
- **Componentes:** extraer UI repetida a `resources/js/components/...`; nombres descriptivos, props tipadas/documentadas en JSDoc.
- **Estilos:** usar tokens del tema (`bible-*`, `background`, `foreground`, etc.). No hardcodear colores que rompan temas.
- **Temas:** 6 temas bíblicos (pergamino, olivo, jordán, getsemaní, sinaí, patmos). Default: getsemaní. Persistencia en `useAppearance`.
- **Idioma:** textos visibles en español rioplatense. Evitar anglicismos innecesarios; mantener términos de producto cuando aportan claridad (ej. “post”, “feed”) solo si ya están establecidos, preferir “publicación”, “inicio/comunidad”.

## UX/UI
- Priorizar legibilidad, jerarquía visual y movimiento natural del ojo (de arriba a abajo, izquierda a derecha).
- Navegación lateral vertical en desktop; adaptada en mobile.
- Formularios simples, con estados claros (procesando, error, vacío).
- Feedback inmediato en acciones sociales (like, rebibleo, seguir) con `preserveScroll`.

## Social (modelo actual)
- `posts`: publicaciones, respuestas (`parent_id`) y rebibleos (`rebible_of_id`).
- `post_likes`: me gusta único por usuario/post.
- `follows`: relación seguidor/seguido.
- `users.church`: iglesia del usuario.

## Herramientas bíblicas
- Mantener lectura, búsqueda, grafo, notas, favoritos, devocionario y compartir versículos funcionando, aunque el foco visual sea social.

## Calidad
- Documentar cambios relevantes, funciones y decisiones.
- No eliminar código/archivos ajenos sin verificar.
- Antes de cerrar: ejecutar build/lints/tests razonables para el cambio.
- Mantener compatibilidad con datos existentes y migraciones aplicadas.
