<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';
import EmptyState from '@/components/Bible/EmptyState.vue';
import LoadingState from '@/components/Bible/LoadingState.vue';
import { useFavorites } from '@/composables/useFavorites';
import { useToast } from '@/composables/useToast';
import ToastContainer from '@/components/Bible/ToastContainer.vue';

/**
 * Página con los versículos favoritos del usuario.
 */
const { favorites, toggleFavorite, loading, loaded } = useFavorites();
const { showToast } = useToast();

/** Quita un versículo de favoritos y muestra confirmación. */
async function removeFavorite(favorite) {
  try {
    await toggleFavorite(favorite);
    showToast('Versículo quitado de favoritos.');
  } catch {
    showToast('No se pudo quitar el favorito.');
  }
}
</script>

<template>
  <Head title="Favoritos" />

  <BiblePageShell max-width-class="max-w-7xl">
    <template #header>
      <h1 class="font-bible-display text-3xl font-semibold text-bible-accent-bright">Favoritos</h1>
      <p class="text-sm text-bible-muted">Tus versículos guardados para volver a ellos rápidamente.</p>
    </template>

    <LoadingState v-if="loading && !loaded" message="Cargando tus versículos favoritos..." />

    <EmptyState
      v-else-if="loaded && !favorites.length"
      title="Todavía no tenés favoritos"
      description="Cuando encuentres un versículo especial, marcalo como favorito desde la lectura o el grafo."
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
        v-for="f in favorites"
        :key="`${f.book_code}-${f.chapter}-${f.verse}`"
        class="rounded-xl border border-bible-border bg-bible-panel/50 p-4 md:p-5"
      >
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <Link
              :href="`/bible/${f.book_code}/${f.chapter}/${f.verse}`"
              class="text-sm font-medium uppercase tracking-wide text-bible-accent-bright transition-colors hover:text-bible-accent"
            >
              {{ f.book_name }} {{ f.chapter }}:{{ f.verse }}
            </Link>
            <p class="mt-2 font-bible-display text-lg leading-relaxed text-bible-text">{{ f.text }}</p>
          </div>

          <button
            type="button"
            @click="removeFavorite(f)"
            class="rounded-md border border-bible-border px-3 py-1.5 text-sm text-bible-muted transition-colors hover:border-bible-error hover:text-bible-error focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
          >
            Quitar
          </button>
        </div>
      </article>
    </div>

    <ToastContainer />
  </BiblePageShell>
</template>
