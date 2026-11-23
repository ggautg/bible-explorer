<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, NotebookPen, Trash2 } from '@lucide/vue';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';
import EmptyState from '@/components/Bible/EmptyState.vue';
import LoadingState from '@/components/Bible/LoadingState.vue';
import { useNotes } from '@/composables/useNotes';
import { useToast } from '@/composables/useToast';

/**
 * Página con las notas personales del usuario.
 * Permite revisar y eliminar reflexiones guardadas por versículo.
 */
const { notes, deleteNote, loading, loaded } = useNotes();
const { showToast } = useToast();

/** Elimina una nota y muestra confirmación. */
async function removeNote(note) {
  try {
    await deleteNote(note);
    showToast('Nota eliminada.');
  } catch {
    showToast('No se pudo eliminar la nota.');
  }
}
</script>

<template>
  <Head title="Mis notas" />

  <BiblePageShell max-width-class="max-w-7xl">
    <template #header>
      <p class="mb-1 inline-flex items-center gap-2 text-xs font-medium uppercase tracking-[0.2em] text-bible-accent">
        <NotebookPen class="h-4 w-4" aria-hidden="true" />
        Reflexiones
      </p>
      <h1 class="font-bible-display text-3xl font-semibold text-bible-accent-bright md:text-4xl">Mis notas</h1>
      <p class="mt-1 text-sm text-bible-muted">Tus reflexiones personales guardadas junto a la Palabra.</p>
    </template>

    <LoadingState v-if="loading && !loaded" message="Cargando tus notas..." />

    <EmptyState
      v-else-if="loaded && !notes.length"
      :icon="NotebookPen"
      title="Todavía no escribiste notas"
      description="Mientras leés un capítulo, podés agregar una nota breve en cualquier versículo."
    >
      <template #actions>
        <Link
          href="/leer"
          class="inline-flex items-center gap-2 rounded-full bg-bible-accent px-5 py-2.5 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright"
        >
          <BookOpen class="h-4 w-4" aria-hidden="true" />
          Ir a leer
        </Link>
      </template>
    </EmptyState>

    <div v-else class="space-y-3">
      <article
        v-for="n in notes"
        :key="`${n.book_code}-${n.chapter}-${n.verse}`"
        class="group rounded-2xl border border-bible-border bg-bible-panel/50 p-4 transition-colors hover:border-bible-accent/30 md:p-5"
      >
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="min-w-0 flex-1">
            <Link
              :href="`/leer/${n.book_code}/${n.chapter}`"
              class="inline-flex items-center gap-2 text-sm font-medium uppercase tracking-wide text-bible-accent-bright transition-colors hover:text-bible-accent"
            >
              <span class="rounded-md border border-bible-accent/20 bg-bible-accent/10 px-2 py-0.5 text-[11px]">{{ n.book_name }} {{ n.chapter }}:{{ n.verse }}</span>
            </Link>
            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-bible-text md:text-[15px]">{{ n.body }}</p>
          </div>

          <button
            type="button"
            @click="removeNote(n)"
            class="inline-flex items-center gap-2 rounded-xl border border-bible-border px-3 py-2 text-sm text-bible-muted transition-colors hover:border-bible-error/50 hover:bg-bible-error/5 hover:text-bible-error focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
          >
            <Trash2 class="h-4 w-4" aria-hidden="true" />
            Eliminar
          </button>
        </div>
      </article>
    </div>
  </BiblePageShell>
</template>
