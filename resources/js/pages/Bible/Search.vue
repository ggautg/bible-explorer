<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';
import EmptyState from '@/components/Bible/EmptyState.vue';

/**
 * Página de búsqueda de texto dentro de la Biblia.
 */
const props = defineProps({
  /** Consulta actual. */
  query: {
    type: String,
    default: '',
  },
  /** Resultados encontrados. */
  results: {
    type: Array,
    default: () => [],
  },
});

const searchInput = ref(props.query || '');
const isSubmitting = ref(false);

watch(
  () => props.query,
  (value) => {
    searchInput.value = value || '';
    isSubmitting.value = false;
  }
);

const canSearch = computed(() => searchInput.value.trim().length >= 3);

/** Ejecuta la búsqueda con la consulta ingresada. */
function submit() {
  if (!canSearch.value || isSubmitting.value) {
    return;
  }

  isSubmitting.value = true;
  router.get(
    '/buscar',
    { q: searchInput.value.trim() },
    {
      preserveState: true,
      preserveScroll: true,
      onFinish: () => {
        isSubmitting.value = false;
      },
    }
  );
}
</script>

<template>
  <Head title="Buscar en la Biblia" />

  <BiblePageShell max-width-class="max-w-4xl">
    <template #header>
      <h1 class="font-bible-display text-3xl font-semibold text-bible-accent-bright">Buscar en la Biblia</h1>
      <p class="text-sm text-bible-muted">Encuentra versículos por palabras o frases.</p>
    </template>

    <form class="mb-8 rounded-xl border border-bible-border bg-bible-panel/40 p-4" @submit.prevent="submit">
      <label for="busqueda-biblia" class="mb-2 block text-sm font-medium text-bible-text">Término de búsqueda</label>
      <div class="flex flex-col gap-3 sm:flex-row">
        <input
          id="busqueda-biblia"
          v-model="searchInput"
          type="text"
          autocomplete="off"
          placeholder="Ejemplo: amor, esperanza o fe"
          class="w-full rounded-md border border-bible-border bg-bible-bg/40 px-4 py-3 text-bible-text placeholder:text-bible-muted-dim focus:outline-none focus:ring-2 focus:ring-bible-accent"
        />
        <button
          type="submit"
          class="inline-flex items-center justify-center rounded-md bg-bible-accent px-5 py-3 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="!canSearch || isSubmitting"
        >
          {{ isSubmitting ? 'Buscando...' : 'Buscar' }}
        </button>
      </div>
      <p class="mt-2 text-xs text-bible-muted">Escribe al menos 3 caracteres para obtener resultados relevantes.</p>
    </form>

    <div v-if="query && query.trim().length >= 3">
      <div v-if="results.length" class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <p class="text-sm text-bible-muted">
          {{ results.length }} resultado{{ results.length === 1 ? '' : 's' }} para “{{ query }}”
        </p>
        <p class="text-xs uppercase tracking-wide text-bible-muted-dim">Ordenados por relevancia</p>
      </div>

      <div v-if="results.length" class="space-y-3">
        <article
          v-for="r in results"
          :key="`${r.book}-${r.chapter}-${r.verse}`"
          class="rounded-xl border border-bible-border bg-bible-panel/50 p-4 md:p-5"
        >
          <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <Link
              :href="`/leer/${r.book}/${r.chapter}`"
              class="text-sm font-medium uppercase tracking-wide text-bible-accent-bright transition-colors hover:text-bible-accent"
            >
              {{ r.bookName }} {{ r.chapter }}:{{ r.verse }}
            </Link>
            <Link
              :href="`/bible/${r.book}/${r.chapter}/${r.verse}`"
              class="text-sm text-bible-muted transition-colors hover:text-bible-accent-bright"
            >
              Ver conexiones
            </Link>
          </div>
          <p class="font-bible-display text-lg leading-relaxed text-bible-text">{{ r.text }}</p>
        </article>
      </div>

      <EmptyState
        v-else
        title="No se encontraron resultados"
        description="Prueba con otra palabra, una frase más corta o un término diferente."
      />
    </div>

    <EmptyState
      v-else
      title="Comienza una búsqueda"
      description="Ingresa un término para explorar versículos relacionados en toda la Biblia."
    />
  </BiblePageShell>
</template>
