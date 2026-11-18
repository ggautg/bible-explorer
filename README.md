# Explorador Bíblico

Aplicación web para leer la Biblia, buscar versículos, guardar favoritos, escribir notas personales y explorar conexiones entre pasajes mediante un grafo interactivo.

## Stack técnico

- **Backend:** Laravel 13, PHP 8.3+
- **Frontend:** Inertia.js 3 + Vue 3 (JavaScript)
- **Estilos:** Tailwind CSS 4 con tema visual oscuro coherente
- **Base de datos:** MySQL 8 (configurable)
- **Autenticación:** Laravel Fortify (incluye opciones de seguridad según configuración)

## Requisitos previos

- PHP 8.3 o superior
- Composer 2
- Node.js y npm
- MySQL 8 (o compatible)

## Puesta en marcha

### 1. Instalar dependencias

```bash
composer install
npm install
```

### 2. Configurar entorno

Crear el archivo `.env` (puedes partir de `.env.example`) y definir, como mínimo:

```env
APP_NAME="Explorador Bíblico"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_ES

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bible_explorer
DB_USERNAME=root
DB_PASSWORD=ionysmaster666
```

> Nota: las credenciales anteriores corresponden a la configuración solicitada para este entorno de desarrollo.

Crear la base de datos en MySQL si aún no existe:

```sql
CREATE DATABASE IF NOT EXISTS bible_explorer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Generar la clave de la aplicación:

```bash
php artisan key:generate
```

### 3. Migrar la base de datos

```bash
php artisan migrate
```

### 4. Cargar contenido bíblico (recomendado)

Para que la lectura, búsqueda y versículo del día funcionen correctamente, importa al menos una traducción:

```bash
php artisan bible:seed-text spa_rvg
```

Opcionalmente, importa referencias cruzadas para enriquecer el grafo:

```bash
php artisan bible:seed-crossrefs
```

> Los comandos descargan datos desde `bible.helloao.org`. En entornos locales con problemas de certificados SSL, el cliente HTTP se ajusta automáticamente para permitir la importación.

### 5. Compilar assets y ejecutar

Modo desarrollo:

```bash
npm run dev
php artisan serve
```

O usar el script combinado definido en Composer:

```bash
composer run dev
```

Compilación de producción:

```bash
npm run build
```

## Funcionalidades principales

- **Inicio:** presentación del explorador y versículo del día.
- **Lectura:** navegación por libros y capítulos, con vista de versículos.
- **Comparación:** opción de mostrar texto alterno (cuando está disponible).
- **Búsqueda:** búsqueda de texto en versículos.
- **Favoritos:** guardar y quitar versículos favoritos por usuario.
- **Notas personales:** crear, editar y eliminar notas por versículo.
- **Grafo de conexiones:** visualización interactiva de referencias cruzadas.
- **Compartir:** vista pública de un versículo compartido.

## Estructura relevante

- `app/Http/Controllers/Bible/*`: controladores de lectura, búsqueda, grafo, favoritos, notas y compartidos.
- `app/Http/Controllers/Settings/*`: configuración de perfil y seguridad.
- `app/Models/*`: modelos Eloquent (`BibleBook`, `BibleVerse`, `Favorite`, `Note`, `User`).
- `app/Services/*`: lógica de dominio (grafo y resolución de referencias).
- `app/Console/Commands/*`: comandos de importación de texto y referencias.
- `resources/js/pages/*`: páginas Inertia (Vue 3).
- `resources/js/components/Bible/*`: componentes reutilizables del explorador bíblico.
- `resources/js/composables/*`: estado y lógica reutilizable en frontend.

## Revisión y cambios realizados

Esta revisión integral se enfocó en poner la aplicación en marcha, mejorar coherencia visual/UX, completar español de interfaz y documentar/comentar el código.

### 1. Puesta en marcha y configuración

- Se creó configuración de entorno para ejecución local con **MySQL** (`DB_DATABASE=bible_explorer`, usuario `root`).
- Se generó clave de aplicación y se ejecutaron migraciones correctamente.
- Se ajustaron los comandos de importación bíblica para ser más tolerantes a fallos de certificados SSL en entorno local/desarrollo.

### 2. Backend: correcciones, claridad y documentación

- **Modelos:** se agregaron comentarios PHPDoc y descripciones de responsabilidades en todos los modelos (`User`, `BibleBook`, `BibleVerse`, `Favorite`, `Note`), se tiparon relaciones y se normalizaron atributos asignables (incluyendo `translation` en `BibleVerse`).
- **Controladores:** se comentaron todos los controladores (base, inicio, Biblia y configuración) con descripciones de clase/métodos y comportamiento.
- **Servicios y utilidades:** se documentaron `BibleGraphService` y `BibleReferenceParser`, mejorando legibilidad y manejo de casos.
- **Robustez:** `HomeController` ahora maneja el caso sin versículos cargados (evita fallos cuando la tabla está vacía).
- **Mensajes:** se estandarizaron mensajes de éxito de configuración en español ("Perfil actualizado correctamente.", "Contraseña actualizada correctamente.").

### 3. Interfaz y experiencia de usuario (UI/UX)

- **Tema coherente:** se unificó la paleta visual en un tema oscuro consistente (colores, bordes, acentos y tipografía) para toda la aplicación.
- **Componentización Vue:** se extrajeron y reutilizaron componentes compartidos del explorador (`BiblePageShell`, `StarfieldBackground`, `LoadingState`, `EmptyState`, `TestimonialCard`, etc.) para reducir duplicación y mejorar mantenibilidad.
- **Navegación y legibilidad:** se reorganizaron cabeceras, espaciados y jerarquía tipográfica para facilitar el recorrido visual.
- **Estados de carga y vacíos:** se añadieron indicadores y mensajes claros en favoritos, notas y flujos de consulta.
- **Formularios y acciones simplificadas:** se mejoraron interacciones de lectura (favoritos, notas, copiado/compartir, comparación), búsqueda (validación y feedback) y grafo (búsqueda de referencia, panel de detalles, acciones).

### 4. Idioma español

- Se revisó y ajustó el contenido visible para mantener **español consistente** en la experiencia principal (navegación, etiquetas, mensajes, estados y textos de apoyo).
- Se tradujeron elementos clave de componentes compartidos de interfaz (menús, apariencia, acciones de cuenta, etc.) para reducir mezclas de idioma.

### 5. Frontend: mejoras técnicas

- Se mejoraron composables (`useFavorites`, `useNotes`, `useToast`, `useVerseSearch`, `useExplorationTrail`) con manejo de estados de carga, comentarios y mensajes más claros.
- Se ajustó la inicialización de tema/progreso para alinearse con la identidad visual del sistema.
- Se corrigieron casos de interfaz cuando no hay datos disponibles (por ejemplo, ausencia de versículo del día).

## Comandos útiles

```bash
# Ejecutar pruebas
php artisan test

# Formatear PHP (Pint)
./vendor/bin/pint

# Verificar tipos/build frontend
npm run build
```

## Notas

- La calidad de la experiencia de búsqueda/grafo depende de que el contenido bíblico y las referencias cruzadas estén importados.
- Si alguna ruta de configuración/autenticación del kit base no tiene página frontend correspondiente en esta personalización, debe revisarse antes de exponerla en producción.
