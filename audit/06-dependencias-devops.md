# 06 — Dependencias y DevOps

Auditoría de manifiestos, lockfiles, auditorías de dependencias, CI y definición de entorno. Alcance: repositorio local y árbol `node_modules`/`vendor` instalado localmente.

## Resultados de la auditoría de dependencias

| Comando | Resultado observado |
|---|---|
| `composer audit` | 0 avisos de seguridad; 2 paquetes abandonados informativos (`dragon-code/contracts`, `dragon-code/support`). El código de salida fue 1 por esos paquetes abandonados, no por vulnerabilidades. |
| `npm audit` | 22 avisos: 2 críticos, 9 altos, 11 moderados. |
| `npm audit --omit=dev` | 8 avisos: 7 altos, 1 moderado. |
| `npm ls vue axios vite concurrently --depth=0` | `axios@1.18.1`, `concurrently@9.2.4`, `vite@8.1.5`, `vue@3.5.40`. |
| `composer show laravel/framework` | `v13.44.1` |
| `composer show inertiajs/inertia-laravel` | `v3.8.0` |
| `composer show laravel/fortify` | `v1.44.0` |
| `php artisan test` | 52 tests, 196 aserciones, en verde (verificación previa). |
| `pint --dirty --test`, `phpstan analyse`, `eslint`, `prettier --check`, `vue-tsc --noEmit`, `npm run build` | en verde (verificación previa). |

## Resumen de la capa

| Severidad | Cantidad | IDs |
|---|---:|---|
| Alta | 1 | OPS-001 |
| Media | 2 | OPS-002, OPS-003 |
| Baja | 1 | OPS-004 |
| Informativa | 1 | OPS-005 |

---

## OPS-001 Versiones npm con avisos de seguridad conocidos (incluye runtime)

- **Severidad:** Alta
- **Capa:** Dependencias / DevOps
- **Referencia:** OWASP A06:2021 / CWE-1104
- **Ubicación:** `package.json:23,33,42,51`, `package-lock.json:2471,2699,7217,7323`, `resources/js/composables/useNotes.js:1`, `resources/js/composables/useFavorites.js:1`
- **Descripción:** `npm audit` sobre el árbol instalado reporta **22 avisos** (2 críticos, 9 altos, 11 moderados). El subconjunto de producción (`npm audit --omit=dev`) reporta **8 avisos** (7 altos, 1 moderado): `axios@1.18.1`, `vue@3.5.40`/`@vue/server-renderer`, `vite@8.1.5`, `follow-redirects`, `nanoid`, `postcss` y `source-map-js`. El aviso crítico está en `concurrently@9.2.4` vía `shell-quote` (GHSA-pqg4-j6r4-53mv), dependencia de desarrollo.
- **Evidencia:**
  - `package.json:42`: `"axios": "^1.18.1"`; `package-lock.json:2471`: `"version": "1.18.1"`.
  - `package.json:51`: `"vue": "^3.5.13"`; `package-lock.json:7323`: `"version": "3.5.40"`.
  - `package.json:33`: `"vite": "^8.0.0"`; `package-lock.json:7217`: `"version": "8.1.5"`.
  - `package.json:23`: `"concurrently": "^9.0.1"`; `package-lock.json:2699`: `"version": "9.2.4"`.
  - `npm audit --omit=dev`: `Axios: Prototype pollution gadget in fetch adapter can alter outbound requests` (GHSA-vh66-26gq-q6x8); `@vue/server-renderer: XSS via missing CR in attribute-name blacklist` (GHSA-g2v6-rqmx-r4w6).
  - `npm audit`: `shell-quote: quote() command injection via a line terminator in a token after a { comment } token` (GHSA-pqg4-j6r4-53mv), severidad `critical`, `fixAvailable: true`.
  - `useNotes.js:1` y `useFavorites.js:1` confirman que `axios` está en código de producción del cliente.
- **Impacto:** Las dependencias con avisos altos pueden afectar el manejo de peticiones desde el navegador (axios), la representación de atributos (Vue) y el servidor de desarrollo/build (Vite). El crítico de `shell-quote` requiere que una persona desarrolle con tokens controlados en comandos, por eso **no se califica Crítica**: no hay evidencia de exposición en producción. El riesgo real en producción `NO VERIFICADO` depende del árbol desplegado y de si el build incluye SSR (no se encontró entrada SSR activa).
- **Escenario práctico:** Una respuesta o redirección controlada por un tercero aprovecha los gadgets de axios; o un nombre de script/host con terminadores de línea dispara inyección de comandos vía `concurrently` al usar `composer dev`.
- **Corrección:** Ejecutar `npm audit fix` (o actualizar a las versiones corregidas) y confirmar con `npm audit --omit=dev` y `npm audit` en verde. Fijar versiones que excluyan las afectadas y agregar `npm audit --audit-level=high` al workflow. Para `vue-tsc` el arreglo es un cambio major (3.3.14), planificarlo por separado.
- **Ejemplo:**
  - Antes: `"axios": "^1.18.1"`, `"vite": "^8.0.0"`, `"vue": "^3.5.13"`.
  - Después: versiones sin avisos según `npm audit fix` (p. ej. `axios` > 1.19.0, `@vue/server-renderer` ≥ 3.5.42, `vite` > 8.1.5).
- **Esfuerzo:** M
- **Estado:** Verificado en el árbol local. Árbol de producción `NO VERIFICADO`.

Avisos de producción detallados por `npm audit --omit=dev`:

| Paquete | Severidad | Aviso representativo |
|---|---|---|
| `@vue/server-renderer` | high | XSS via missing CR in attribute-name blacklist (GHSA-g2v6-rqmx-r4w6) |
| `axios` | high | Prototype pollution gadget in fetch adapter (GHSA-vh66-26gq-q6x8) |
| `follow-redirects` | high | Array-form headers bypass cross-origin credential stripping |
| `nanoid` | high | Custom generators can loop indefinitely when size is zero |
| `postcss` | moderate | Incomplete fix of sourceMappingURL reads |
| `source-map-js` | high | Event-loop denial of service through indexed source-map section offsets |
| `vite` | high | launch-editor / Vite custom middleware advisories (GHSA-698v-4r3x-3vcv, GHSA-9jrq-w75r-8gcw) |
| `vue` | high | Depende de `@vue/server-renderer` |

## OPS-002 El CI no ejecuta auditoría de dependencias

- **Severidad:** Media
- **Capa:** DevOps / CI
- **Referencia:** OWASP A06:2021 / CWE-1104
- **Ubicación:** `.github/workflows/tests.yml:34-38`, `composer.json:65-71`
- **Descripción:** El workflow hace `composer setup` y `composer ci:check`. Este último corre ESLint, Prettier, `vue-tsc` y tests, pero ni `composer audit` ni `npm audit`. Por eso OPS-001 puede llegar a `main` sin fallar el pipeline.
- **Evidencia:**
  - `.github/workflows/tests.yml:34-35`: `- name: Setup Application` / `run: composer setup`.
  - `.github/workflows/tests.yml:37-38`: `- name: Run CI Checks` / `run: composer ci:check`.
  - `composer.json:65-71`: `"ci:check"` contiene `npm run lint:check`, `npm run format:check`, `npm run types:check`, `@test`; no aparece `audit`.
- **Impacto:** Las actualizaciones de seguridad no se vuelven visibles automáticamente. Hoy `composer audit` está limpio, pero `npm audit` falla con avisos altos/críticos y el CI igual pasaría.
- **Escenario práctico:** Se fusiona un PR que sube `axios` a una versión vulnerable; los 52 tests siguen en verde y nadie se entera hasta un incidente.
- **Corrección:** Agregar dos pasos al workflow (o a `ci:check`): `composer audit --locked` y `npm audit --audit-level=high --omit=dev`, más un job semanal separado para avisos de desarrollo con política de excepciones documentada.
- **Ejemplo:**
  - Antes: `run: composer ci:check`
  - Después: `run: composer ci:check && composer audit --locked && npm audit --audit-level=high --omit=dev`
- **Esfuerzo:** S
- **Estado:** Verificado en configuración. Ejecución real del workflow `NO VERIFICADO` (solo se leyó el archivo).

## OPS-003 `composer setup` prepara un entorno inseguro si se usa fuera de CI

- **Severidad:** Media
- **Capa:** DevOps / Configuración
- **Referencia:** OWASP A05:2021 / CWE-16
- **Ubicación:** `composer.json:47-53`, `.env.example:2-5,23,30-32`, `phpunit.xml:21-31`
- **Descripción:** El script `setup` copia `.env.example` si no existe `.env`, genera clave y ejecuta `migrate --force`. La plantilla trae `APP_DEBUG=true`, `APP_ENV=local`, SQLite y `SESSION_ENCRYPT=false`. En CI esto es correcto porque `phpunit.xml` fuerza SQLite en memoria; el riesgo aparece si alguien usa `composer setup` como paso de despliegue.
- **Evidencia:**
  - `composer.json:49-52`: `"@php -r \"file_exists('.env') || copy('.env.example', '.env');\""`, `"@php artisan key:generate"`, `"@php artisan migrate --force"`, `"npm install"`.
  - `.env.example:2-5`: `APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=http://localhost`.
  - `.env.example:23,30-32`: `DB_CONNECTION=sqlite`, `SESSION_DRIVER=database`, `SESSION_ENCRYPT=false`.
  - `phpunit.xml:26-27`: `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` (confirma que el uso en tests es intencional).
- **Impacto:** Un despliegue automatizado que invoque `setup` podría quedar con depuración activa, sesiones sin cifrar y base de datos SQLite local, repitiendo CFG-002 y CFG-004 en producción.
- **Escenario práctico:** Un script de entrega continua llama a `composer setup` en el servidor; `.env` no existía → se copia la plantilla con `APP_DEBUG=true`.
- **Corrección:** Renombrar el script a `ci:setup` o documentar explícitamente "solo desarrollo/CI", y crear un `deploy:setup` separado que exija `.env` completo con `APP_DEBUG=false`, `APP_ENV=production`, `SESSION_ENCRYPT=true` y MySQL. Verificar la existencia del `.env` antes de migrar con `--force`.
- **Ejemplo:**
  - Antes: `file_exists('.env') || copy('.env.example', '.env');`
  - Después: `if (!file_exists('.env')) { throw new RuntimeException('Falta .env de despliegue'); }`
- **Esfuerzo:** S
- **Estado:** Verificado en archivos. Uso real en despliegue `NO VERIFICADO`.

## OPS-004 Instalación npm sin `npm ci` y con `ignore-scripts=true`

- **Severidad:** Baja
- **Capa:** DevOps / Reproducibilidad
- **Referencia:** CWE-1104
- **Ubicación:** `composer.json:52`, `.npmrc:1`, `package-lock.json:1`
- **Descripción:** `composer setup` usa `npm install`, que puede reescribir `package-lock.json`, en vez de `npm ci`, que falla si el lock y el manifiesto difieren. Además `.npmrc` fija `ignore-scripts=true`, por lo que no se ejecutan scripts de instalación de dependencias nativas u opcionales. El build local funciona, pero la reproducibilidad depende de que el lock ya esté correcto.
- **Evidencia:**
  - `composer.json:52`: `"npm install"`.
  - `.npmrc:1`: `ignore-scripts=true`.
  - `git ls-files`: `package-lock.json` y `composer.lock` están versionados; no se encontraron `Dockerfile`, `docker-compose*.yml`, `nginx.conf`, `Procfile`, `.nvmrc` ni `.node-version`.
  - `package.json:55-62`: `optionalDependencies` incluye binarios nativos de Rollup, Tailwind y Lightningcss para Linux y Windows.
- **Impacto:** Dos personas desarrollando pueden terminar con árboles distintos; un cambio no intencional en el lock puede pasar silenciosamente. `ignore-scripts=true` puede dejar paquetes opcionales sin inicializar en otra plataforma (en Windows local el build pasó; en Linux de CI `NO VERIFICADO`).
- **Escenario práctico:** Un PR cambia `package.json` sin actualizar el lock; `npm install` lo reescribe y el CI compra la nueva resolución sin revisión explícita.
- **Corrección:** Usar `npm ci` en CI/setup, agregar un chequeo de `git diff --exit-code package-lock.json` después de instalar y documentar por qué `ignore-scripts=true` es necesario (o permitir scripts solo para paquetes auditados).
- **Ejemplo:**
  - Antes: `"npm install"`
  - Después: `"npm ci"`
- **Esfuerzo:** S
- **Estado:** Verificado en configuración. Instalación en CI Linux `NO VERIFICADO`.

## OPS-005 Sin definición de artefacto/servidor de producción en el repositorio

- **Severidad:** Informativa
- **Capa:** DevOps
- **Referencia:** CWE-16
- **Ubicación:** `composer.json:55-58` (único comando de servidor encontrado); búsqueda de `Dockerfile`, `docker-compose*.yml`, `nginx.conf`, `Procfile`, `.nvmrc`, `.node-version` → 0 resultados
- **Descripción:** El único servidor documentado en el repo es `php artisan serve` dentro del script `dev`, junto con `queue:listen` y Vite. No hay configuración de servidor web, contenedores ni versión declarada de Node para producción. Esto confirma el alcance "solo local" y conecta con CFG-006.
- **Evidencia:**
  - `composer.json:55-58`: `"dev": [... "php artisan serve" "php artisan queue:listen --tries=1" "npm run dev" ...]`.
  - `git ls-files`/`glob`: 0 coincidencias para los archivos de despliegue listados arriba.
  - `.github/workflows/tests.yml:29-32`: CI fija Node `22`, pero el repo no lo declara para desarrollo/producción.
- **Impacto:** No es un hallazgo explotable por sí solo; limita la auditabilidad. No se puede afirmar cómo se sirven los archivos, qué cabeceras se envían ni si PHP/FPM está endurecido.
- **Escenario práctico:** Una persona administradora asume que `php artisan serve` es el servidor de producción; ese comando no es adecuado para producción y no configura HTTPS ni cabeceras.
- **Corrección:** Si existe despliegue real, versionar su definición (Dockerfile o configuración nginx/Apache + `php.ini` + systemd/supervisor) y un `.nvmrc` con Node 22. Si no existe, documentar explícitamente "entorno local únicamente".
- **Ejemplo:** Agregar `.nvmrc` con `22` y un `Dockerfile` basado en `php:8.3-fpm` + nginx.
- **Esfuerzo:** M
- **Estado:** Ausencia verificada en el repo. Servidor real de producción `NO VERIFICADO`.

## Buenas prácticas de esta capa

- Las actions de GitHub están fijadas por SHA completo y el workflow usa `permissions: contents: read` (`.github/workflows/tests.yml:9-10,18,23,30`), lo que reduce riesgo de supply-chain en el CI.
- `composer audit` reporta **0 avisos de seguridad**; solo 2 paquetes abandonados de forma informativa.
- `composer.lock` y `package-lock.json` están versionados (`git ls-files`), lo que permite instalaciones deterministas si se usa `npm ci`.
- CI fija PHP 8.3 y Node 22 (`.github/workflows/tests.yml:25,32`), coherente con `php >= 8.3` de `composer.json:12`.
- El pipeline ejecuta Pint, PHPStan, ESLint, Prettier, `vue-tsc` y la suite completa (`composer.json:65-71`, `.github/workflows/tests.yml:37-38`); localmente se verificó en verde.
- Los tests usan SQLite en memoria, correo `array`, cola `sync` y sesión `array` (`phpunit.xml:26-31`), evitando depender de MySQL en CI.
- `composer.json:108-115` habilita `optimize-autoloader`, `preferred-install: dist` y un `allow-plugins` explícito y mínimo.
- No se encontraron secretos en archivos versionados fuera del historial/current README ya documentado en CFG-001 (siempre censurado en estos informes).

## Límites de la verificación

- No se consultó el repositorio remoto, así que la visibilidad pública/privada `NO VERIFICADO`.
- No se ejecutó el workflow de GitHub Actions; solo se audita su definición.
- No se verificó el árbol de dependencias instalado en producción ni si el bundle final incluye SSR.
