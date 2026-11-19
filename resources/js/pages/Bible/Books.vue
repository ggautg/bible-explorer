<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';

/**
 * Página de listado de libros bíblicos.
 */
const props = defineProps({
  /** Colección de libros disponibles. */
  books: {
    type: Array,
    default: () => [],
  },
});

const oldTestament = computed(() => props.books.filter((b) => b.testament === 'OT'));
const newTestament = computed(() => props.books.filter((b) => b.testament === 'NT'));
</script>

<template>
  <Head title="Leer la Biblia" />

  <BiblePageShell max-width-class="max-w-7xl">
    <template #header>
      <h1 class="font-bible-display text-3xl font-semibold text-bible-accent-bright">Lectura de la Biblia</h1>
      <p class="text-sm text-bible-muted">Elegí un libro para empezar a leer por capítulos.</p>
    </template>

    <div class="space-y-10">
      <section>
        <div class="mb-4 flex items-end justify-between gap-4 border-b border-bible-border pb-2">
          <h2 class="font-bible-display text-xl text-bible-text">Antiguo Testamento</h2>
          <span class="text-xs uppercase tracking-wide text-bible-muted">{{ oldTestament.length }} libros</span>
        </div>
        <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
          <Link
            v-for="b in oldTestament"
            :key="b.code"
            :href="`/leer/${b.code}`"
            class="rounded-lg border border-bible-border bg-bible-panel/70 px-3.5 py-3 text-sm text-bible-text transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
          >
            {{ b.name }}
          </Link>
        </div>
      </section>

      <section>
        <div class="mb-4 flex items-end justify-between gap-4 border-b border-bible-border pb-2">
          <h2 class="font-bible-display text-xl text-bible-text">Nuevo Testamento</h2>
          <span class="text-xs uppercase tracking-wide text-bible-muted">{{ newTestament.length }} libros</span>
        </div>
        <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
          <Link
            v-for="b in newTestament"
            :key="b.code"
            :href="`/leer/${b.code}`"
            class="rounded-lg border border-bible-border bg-bible-panel/70 px-3.5 py-3 text-sm text-bible-text transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
          >
            {{ b.name }}
          </Link>
        </div>
      </section>
    </div>
  </BiblePageShell>
</template>
