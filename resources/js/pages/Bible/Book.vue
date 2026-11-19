<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';

/**
 * Página de selección de capítulos de un libro bíblico.
 */
defineProps({
  /** Libro seleccionado. */
  book: {
    type: Object,
    required: true,
  },
  /** Lista de números de capítulo disponibles. */
  chapters: {
    type: Array,
    default: () => [],
  },
});
</script>

<template>
  <Head :title="book.name" />

  <BiblePageShell back-href="/leer" back-label="← Volver a libros" max-width-class="max-w-7xl">
    <template #header>
      <h1 class="mt-1 font-bible-display text-3xl font-semibold text-bible-accent-bright">{{ book.name }}</h1>
      <p class="text-sm text-bible-muted">Seleccioná un capítulo para leerlo.</p>
    </template>

    <div class="rounded-xl border border-bible-border bg-bible-panel/40 p-4 md:p-5">
      <div class="grid grid-cols-5 gap-2 sm:grid-cols-8 md:grid-cols-10">
        <Link
          v-for="c in chapters"
          :key="c"
          :href="`/leer/${book.code}/${c}`"
          class="rounded-md border border-bible-border bg-bible-bg/40 py-2.5 text-center font-bible-display text-base text-bible-text transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
        >
          {{ c }}
        </Link>
      </div>
    </div>
  </BiblePageShell>
</template>
