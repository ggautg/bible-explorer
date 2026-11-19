<script setup>
import { Link } from '@inertiajs/vue3';
import NavHeader from '@/components/Bible/NavHeader.vue';

/**
 * Contenedor de página con navegación lateral vertical.
 *
 * Organiza la navegación en un menú lateral (opciones apiladas) y el contenido
 * principal en una columna flexible, manteniendo coherencia visual.
 */
defineProps({
  /** Enlace de retroceso opcional para la cabecera del contenido. */
  backHref: {
    type: String,
    default: null,
  },
  /** Texto visible del enlace de retroceso. */
  backLabel: {
    type: String,
    default: '← Volver',
  },
  /** Ancho máximo del contenido (columna principal). */
  maxWidthClass: {
    type: String,
    default: 'max-w-5xl',
  },
  /** Oculta la navegación lateral cuando no es necesaria. */
  hideNav: {
    type: Boolean,
    default: false,
  },
});
</script>

<template>
  <div class="min-h-screen bg-bible-bg text-bible-text font-bible-body">
    <div class="mx-auto w-full max-w-[1400px] px-4 py-6 md:px-6 md:py-8">
      <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:gap-8">
        <aside
          v-if="!hideNav"
          class="w-full shrink-0 lg:sticky lg:top-8 lg:w-64 lg:self-start"
          aria-label="Menú lateral"
        >
          <div class="rounded-2xl border border-bible-border bg-bible-panel/50 p-3 md:p-4">
            <div class="mb-4 px-3">
              <p class="font-bible-display text-lg font-semibold leading-tight text-bible-accent-bright">Explorador Bíblico</p>
              <p class="text-xs text-bible-muted">Comunidad y herramientas</p>
            </div>
            <NavHeader />
          </div>
        </aside>

        <main class="min-w-0 flex-1">
          <div :class="['mx-auto w-full', maxWidthClass]">
            <div v-if="backHref || $slots.header" class="mb-6 space-y-2 border-b border-bible-border/70 pb-5">
              <Link
                v-if="backHref"
                :href="backHref"
                class="inline-flex items-center text-sm text-bible-muted transition-colors hover:text-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent rounded"
              >
                {{ backLabel }}
              </Link>
              <slot name="header" />
            </div>
            <slot />
          </div>
        </main>
      </div>
    </div>
  </div>
</template>
