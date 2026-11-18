<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';
import EmptyState from '@/components/Bible/EmptyState.vue';
import LoadingState from '@/components/Bible/LoadingState.vue';
import { useNotes } from '@/composables/useNotes';
import { useToast } from '@/composables/useToast';
import ToastContainer from '@/components/Bible/ToastContainer.vue';

/**
 * Página con las notas personales del usuario.
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

  <BiblePageShell max-width-class="max-w-4xl">
    <template #header>
      <h1 class="font-bible-display text-3xl font-semibold text-bible-accent-bright">Mis notas</h1>
      <p class="text-sm text-bible-muted">Reflexiones personales guardadas junto a tus versículos.</p>
    </template>

    <LoadingState v-if="loading && !loaded" message="Cargando tus notas..." />

    <EmptyState
      v-else-if="loaded && !notes.length"
      title="Aún no has escrito notas"
      description="Mientras lees un capítulo, puedes agregar una nota breve en cualquier versículo."
    >
      <template #actions>
        <Link
          href="/leer"
          class="rounded-md bg-bible-accent px-4 py-2 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright"
        >
          Ir a leer
        </Link>
      </template>
    </EmptyState>

    <div v-else class="space-y-3">
      <article
        v-for="n in notes"
        :key="`${n.book_code}-${n.chapter}-${n.verse}`"
        class="rounded-xl border border-bible-border bg-bible-panel/50 p-4 md:p-5"
      >
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <Link
              :href="`/leer/${n.book_code}/${n.chapter}`"
              class="text-sm font-medium uppercase tracking-wide text-bible-accent-bright transition-colors hover:text-bible-accent"
            >
              {{ n.book_name }} {{ n.chapter }}:{{ n.verse }}
            </Link>
            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-bible-text">{{ n.body }}</p>
          </div>

          <button
            type="button"
            @click="removeNote(n)"
            class="rounded-md border border-bible-border px-3 py-1.5 text-sm text-bible-muted transition-colors hover:border-bible-error hover:text-bible-error focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
          >
            Eliminar
          </button>
        </div>
      </article>
    </div>

    <ToastContainer />
  </BiblePageShell>
</template>
