<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';
import ToastContainer from '@/components/Bible/ToastContainer.vue';
import { useConstellationGraph } from '@/composables/useConstellationGraph';
import { useExplorationTrail } from '@/composables/useExplorationTrail';
import { useFavorites } from '@/composables/useFavorites';
import { useToast } from '@/composables/useToast';
import { useVerseSearch } from '@/composables/useVerseSearch';

/**
 * Página del grafo de conexiones entre versículos.
 */
const props = defineProps({
  nodes: { type: Array, default: () => [] },
  edges: { type: Array, default: () => [] },
  center: { type: String, default: '' },
});

const svgRef = ref(null);
const selected = ref(null);
const copied = ref(false);

const { render, reference, navigateTo } = useConstellationGraph(svgRef, selected);
const { searchQuery, suggestions, searchError, isResolving, pickSuggestion, submitSearch } = useVerseSearch();
const { trail, visit, clear } = useExplorationTrail();
const { isFavorite, toggleFavorite } = useFavorites();
const { showToast } = useToast();

const pageTitle = computed(() => {
  if (!props.center) return 'Grafo de conexiones';
  return `Grafo: ${props.center.replace(/-/g, ' ')}`;
});

/** Copia el versículo seleccionado y su enlace público. */
async function copyLink() {
  const v = selected.value;
  if (!v) return;

  const shareUrl = `${window.location.origin}/compartido/${v.book}/${v.chapter}/${v.verse}`;
  const shareText = `"${v.text}"\n${reference(v)}\n${shareUrl}`;

  try {
    await navigator.clipboard.writeText(shareText);
    copied.value = true;
    showToast('Versículo copiado al portapapeles.');
    setTimeout(() => {
      copied.value = false;
    }, 1800);
  } catch {
    showToast('No se pudo copiar el versículo.');
  }
}

/** Alterna el favorito del versículo seleccionado. */
async function toggleSelectedFavorite() {
  const v = selected.value;
  if (!v) return;

  const payload = { ...v, book_code: v.book, bookName: v.bookName };
  const wasFavorite = isFavorite(payload);

  try {
    await toggleFavorite(payload);
    showToast(wasFavorite ? 'Versículo quitado de favoritos.' : 'Versículo guardado en favoritos.');
  } catch {
    showToast('No se pudo actualizar favoritos.');
  }
}

/** Renderiza el grafo cuando hay datos disponibles. */
async function renderGraph() {
  selected.value = null;
  await nextTick();
  render(props.nodes, props.edges);
}

onMounted(() => {
  void renderGraph();
});

watch(() => props.center, () => {
  void renderGraph();
});

watch(
  () => props.center,
  (value) => {
    if (!value) return;
    const [book, chapter, verse] = value.split('-');
    visit({ book, chapter: Number(chapter), verse: Number(verse) });
  },
  { immediate: true }
);
</script>

<template>
  <Head :title="pageTitle" />

  <BiblePageShell max-width-class="max-w-7xl">
    <template #header>
      <h1 class="font-bible-display text-3xl font-semibold text-bible-accent-bright">Grafo de conexiones</h1>
      <p class="text-sm text-bible-muted">Explora relaciones entre versículos y navega por sus referencias cruzadas.</p>
    </template>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
      <section class="space-y-4">
        <div class="rounded-xl border border-bible-border bg-bible-panel/40 p-4">
          <form class="flex flex-col gap-3 sm:flex-row" @submit.prevent="submitSearch">
            <div class="relative flex-1">
              <label for="buscar-referencia-grafo" class="sr-only">Buscar referencia bíblica</label>
              <input
                id="buscar-referencia-grafo"
                v-model="searchQuery"
                type="text"
                autocomplete="off"
                placeholder="Escribí una referencia, por ejemplo: Juan 3:16"
                class="w-full rounded-md border border-bible-border bg-bible-bg/40 px-4 py-2.5 text-sm text-bible-text placeholder:text-bible-muted-dim focus:outline-none focus:ring-2 focus:ring-bible-accent"
              />

              <ul
                v-if="suggestions.length"
                class="absolute left-0 right-0 top-full z-10 mt-1 max-h-60 overflow-auto rounded-md border border-bible-border bg-bible-panel p-1 shadow-lg"
                role="listbox"
              >
                <li v-for="book in suggestions" :key="book.code">
                  <button
                    type="button"
                    class="w-full rounded px-3 py-2 text-left text-sm text-bible-text transition-colors hover:bg-bible-bg/60 hover:text-bible-accent-bright"
                    @click="pickSuggestion(book)"
                  >
                    {{ book.name }}
                  </button>
                </li>
              </ul>
            </div>

            <button
              type="submit"
              class="inline-flex items-center justify-center rounded-md bg-bible-accent px-4 py-2.5 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="!searchQuery.trim() || isResolving"
            >
              {{ isResolving ? 'Buscando...' : 'Ir al versículo' }}
            </button>
          </form>

          <p v-if="searchError" class="mt-2 text-sm text-bible-error">{{ searchError }}</p>
          <p class="mt-2 text-xs text-bible-muted">
            Consejo: hacé clic en un nodo para ver detalles y doble clic para navegar a ese versículo.
          </p>
        </div>

        <div v-if="trail.length > 1" class="flex flex-wrap items-center gap-2 rounded-xl border border-bible-border bg-bible-panel/30 px-4 py-3 text-sm">
          <span class="text-bible-muted">Recorrido:</span>
          <template v-for="(step, i) in trail" :key="`${step.book}-${step.chapter}-${step.verse}-${i}`">
            <Link
              :href="`/bible/${step.book}/${step.chapter}/${step.verse}`"
              class="rounded px-1.5 py-0.5 transition-colors hover:text-bible-accent-bright"
              :class="i === trail.length - 1 ? 'bg-bible-panel text-bible-accent-bright' : 'text-bible-muted'"
            >
              {{ step.book }} {{ step.chapter }}:{{ step.verse }}
            </Link>
            <span v-if="i < trail.length - 1" class="text-bible-muted-dim">→</span>
          </template>
          <button
            type="button"
            @click="clear"
            class="ml-auto text-xs text-bible-muted transition-colors hover:text-bible-error"
          >
            Limpiar recorrido
          </button>
        </div>

        <div class="rounded-xl border border-bible-border bg-bible-panel/20 p-2">
          <svg
            ref="svgRef"
            viewBox="0 0 900 640"
            preserveAspectRatio="xMidYMid meet"
            class="h-[420px] w-full rounded-lg bg-bible-bg md:h-[640px]"
            role="img"
            aria-label="Grafo interactivo de conexiones bíblicas"
          ></svg>
        </div>
      </section>

      <aside class="h-fit rounded-xl border border-bible-border bg-bible-panel/50 p-5">
        <template v-if="selected">
          <p class="text-xs font-medium uppercase tracking-[0.18em] text-bible-accent">Versículo seleccionado</p>
          <h2 class="mt-2 font-bible-display text-2xl font-semibold text-bible-accent-bright">{{ reference(selected) }}</h2>
          <p class="mt-3 font-bible-display text-lg leading-relaxed text-bible-text">{{ selected.text }}</p>

          <div class="mt-5 flex flex-wrap gap-2">
            <button
              type="button"
              @click="navigateTo(selected)"
              class="rounded-md bg-bible-accent px-4 py-2 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
            >
              Abrir versículo
            </button>
            <button
              type="button"
              @click="toggleSelectedFavorite"
              class="rounded-md border px-4 py-2 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
              :class="isFavorite({ ...selected, book_code: selected.book })
                ? 'border-bible-accent/60 bg-bible-accent/10 text-bible-accent-bright'
                : 'border-bible-border text-bible-muted hover:border-bible-accent hover:text-bible-accent-bright'"
            >
              {{ isFavorite({ ...selected, book_code: selected.book }) ? 'Quitar de favoritos' : 'Guardar en favoritos' }}
            </button>
            <button
              type="button"
              @click="copyLink"
              class="rounded-md border border-bible-border px-4 py-2 text-sm font-medium text-bible-muted transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
            >
              {{ copied ? 'Copiado' : 'Copiar enlace' }}
            </button>
          </div>
        </template>

        <template v-else>
          <h2 class="font-bible-display text-xl font-semibold text-bible-accent-bright">Detalles del versículo</h2>
          <p class="mt-3 text-sm leading-relaxed text-bible-muted">
            Seleccioná un nodo del grafo para ver su texto y acceder a sus acciones. Si el grafo está vacío, es posible que todavía no se hayan importado las referencias cruzadas.
          </p>
          <p class="mt-3 text-xs text-bible-muted-dim">
            Nodos: {{ nodes.length }} · Conexiones: {{ edges.length }}
          </p>
        </template>
      </aside>
    </div>

    <ToastContainer />
  </BiblePageShell>
</template>
