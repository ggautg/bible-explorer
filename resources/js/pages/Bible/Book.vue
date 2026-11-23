<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { BookMarked, BookOpen, Hash } from '@lucide/vue';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';

/**
 * Página de selección de capítulos de un libro bíblico.
 * Muestra una grilla clara para saltar rápido al capítulo deseado.
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
      <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="mb-1 inline-flex items-center gap-2 text-xs font-medium uppercase tracking-[0.2em] text-bible-accent">
            <BookOpen class="h-4 w-4" aria-hidden="true" />
            Libro
          </p>
          <h1 class="font-bible-display text-3xl font-semibold text-bible-accent-bright md:text-4xl">{{ book.name }}</h1>
          <p class="mt-1 text-sm text-bible-muted">Seleccioná un capítulo para comenzar la lectura.</p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-full border border-bible-border bg-bible-panel/40 px-3 py-1.5 text-xs text-bible-muted">
          <Hash class="h-3.5 w-3.5 text-bible-accent" aria-hidden="true" />
          {{ chapters.length }} capítulos
        </div>
      </div>
    </template>

    <div class="rounded-2xl border border-bible-border bg-bible-panel/45 p-4 md:p-6">
      <div class="mb-4 flex items-center gap-2 text-sm text-bible-muted">
        <BookMarked class="h-4 w-4 text-bible-accent" aria-hidden="true" />
        <span>Tip: podés usar la navegación del capítulo para avanzar versículo a versículo.</span>
      </div>

      <div class="grid grid-cols-5 gap-2 sm:grid-cols-8 md:grid-cols-10 lg:grid-cols-12">
        <Link
          v-for="c in chapters"
          :key="c"
          :href="`/leer/${book.code}/${c}`"
          class="group rounded-xl border border-bible-border bg-bible-bg/40 py-2.5 text-center font-bible-display text-base text-bible-text transition-all hover:-translate-y-0.5 hover:border-bible-accent/60 hover:bg-bible-accent/10 hover:text-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
        >
          {{ c }}
        </Link>
      </div>
    </div>
  </BiblePageShell>
</template>
