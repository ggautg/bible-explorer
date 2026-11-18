<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { logout } from '@/routes';

/**
 * Cabecera de navegación del explorador bíblico.
 *
 * Ofrece accesos claros a las secciones principales y mantiene una jerarquía
 * visual simple para facilitar el recorrido con la vista.
 */
const props = defineProps({
  /** Enlace de retroceso opcional. */
  backHref: { type: String, default: null },
  /** Texto visible del enlace de retroceso. */
  backLabel: { type: String, default: '← Volver' },
});

const page = usePage();

/** Enlaces principales de la aplicación. */
const navItems = [
  { href: '/leer', label: 'Leer' },
  { href: '/buscar', label: 'Buscar' },
  { href: '/bible/GEN/1/1', label: 'Grafo' },
  { href: '/favoritos', label: 'Favoritos' },
  { href: '/notas', label: 'Notas' },
];

/** URL actual para resaltar el apartado activo. */
const currentUrl = computed(() => page.url || '/');

/** Determina si un enlace corresponde a la sección actual. */
function isActive(href) {
  if (href === '/bible/GEN/1/1') {
    return currentUrl.value.startsWith('/bible');
  }
  return currentUrl.value === href || currentUrl.value.startsWith(`${href}/`);
}
</script>

<template>
  <header class="mb-8 flex flex-col gap-4 border-b border-bible-border/70 pb-5 lg:flex-row lg:items-start lg:justify-between">
    <div class="min-w-0 space-y-1">
      <Link
        v-if="backHref"
        :href="backHref"
        class="inline-flex items-center text-sm text-bible-muted transition-colors hover:text-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent rounded"
      >
        {{ backLabel }}
      </Link>
      <slot />
    </div>

    <nav class="flex flex-wrap items-center gap-2 text-sm" aria-label="Navegación principal">
      <Link
        v-for="item in navItems"
        :key="item.href"
        :href="item.href"
        class="rounded-md px-3 py-1.5 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
        :class="isActive(item.href)
          ? 'bg-bible-panel text-bible-accent-bright border border-bible-border'
          : 'text-bible-muted hover:text-bible-accent-bright hover:bg-bible-panel/60'"
      >
        {{ item.label }}
      </Link>

      <Link
        :href="logout()"
        method="post"
        as="button"
        class="rounded-md px-3 py-1.5 text-bible-muted transition-colors hover:bg-bible-panel/60 hover:text-bible-error focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
      >
        Cerrar sesión
      </Link>
    </nav>
  </header>
</template>
