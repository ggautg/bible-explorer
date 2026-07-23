<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { logout } from '@/routes'
import { useConstellationGraph } from '@/composables/useConstellationGraph'
import { useVerseSearch } from '@/composables/useVerseSearch'
import { useExplorationTrail } from '@/composables/useExplorationTrail'
import { useFavorites } from '@/composables/useFavorites'

const props = defineProps({
  nodes: Array,
  edges: Array,
  center: String,
})

const svgRef = ref(null)
const selected = ref(null)

const { render, reference, navigateTo } = useConstellationGraph(svgRef, selected)
const { searchQuery, suggestions, searchError, pickSuggestion, submitSearch } = useVerseSearch()
const { trail, visit, clear } = useExplorationTrail()
const { isFavorite, toggleFavorite } = useFavorites()

onMounted(() => nextTick(() => render(props.nodes, props.edges)))

watch(() => props.center, () => nextTick(() => render(props.nodes, props.edges)))

watch(() => props.center, () => {
  const [book, chapter, verse] = props.center.split('-')
  visit({ book, chapter: Number(chapter), verse: Number(verse) })
}, { immediate: true })
</script>

<template>

  <Head :title="`Explorador — ${center.replace(/-/g, ' ')}`" />

  <div class="bg-bible-bg min-h-screen text-bible-text p-4 md:p-8 font-bible-body">
    <header class="mb-4">
      <div class="flex justify-between items-start mb-4">
        <div>
          <h1 class="font-bible-display font-semibold text-2xl md:text-3xl text-bible-accent-bright mb-1">
            Explorador de Constelaciones Bíblicas
          </h1>
          <p class="text-bible-muted text-sm">
            Doble clic en una estrella para navegar hacia ese versículo
          </p>
        </div>
        <div class="flex items-center gap-4">
          <Link href="/leer" class="text-bible-accent text-sm hover:underline whitespace-nowrap">
            Leer
          </Link>
          <Link href="/favoritos" class="text-bible-accent text-sm hover:underline whitespace-nowrap">
            ★ Favoritos
          </Link>
          <Link :href="logout()" method="post" as="button"
            class="text-bible-muted text-sm hover:text-bible-error whitespace-nowrap">
            Cerrar sesión
          </Link>
        </div>
      </div>

      <div class="relative max-w-xs mb-6">
        <input v-model="searchQuery" @keyup.enter="submitSearch" type="text" placeholder="Ej: Juan 3:16"
          autocomplete="off"
          class="w-full bg-bible-panel border border-bible-border text-bible-text px-3.5 py-2.5 rounded-md text-sm focus:outline-none focus:border-bible-accent" />
        <ul v-if="suggestions.length"
          class="absolute top-full left-0 right-0 bg-bible-panel border border-bible-border rounded-md p-1 m-0 list-none z-10">
          <li v-for="book in suggestions" :key="book.code" @click="pickSuggestion(book)"
            class="px-2.5 py-2 cursor-pointer rounded text-sm hover:bg-bible-border hover:text-bible-accent-bright">
            {{ book.name }}
          </li>
        </ul>
        <p v-if="searchError" class="text-bible-error text-xs mt-1.5">{{ searchError }}</p>
      </div>
      <div v-if="trail.length > 1" class="flex flex-wrap items-center gap-1.5 mb-6 text-sm">
        <template v-for="(step, i) in trail" :key="i">
          <Link :href="`/bible/${step.book}/${step.chapter}/${step.verse}`"
            class="text-bible-muted hover:text-bible-accent-bright transition-colors"
            :class="{ 'text-bible-accent-bright font-medium': i === trail.length - 1 }">
            {{ step.book }} {{ step.chapter }}:{{ step.verse }}
          </Link>
          <span v-if="i < trail.length - 1" class="text-bible-muted-dim">→</span>
        </template>
        <button @click="clear" class="text-bible-muted-dim text-xs ml-2 hover:text-bible-error"
          title="Reiniciar recorrido">
          ✕ limpiar
        </button>
      </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-[1fr_320px] gap-6">
      <svg ref="svgRef" viewBox="0 0 900 640" preserveAspectRatio="xMidYMid meet"
        class="w-full h-[420px] md:h-[640px] bg-bible-bg border border-bible-border rounded-lg"></svg>

      <aside v-if="selected" class="bg-bible-panel border border-bible-border rounded-lg p-5 h-fit">
        <span class="font-bible-body text-xs uppercase tracking-wide text-bible-accent">
          {{ reference(selected) }}
        </span>
        <p class="font-bible-display text-lg leading-relaxed my-3 text-bible-text">
          {{ selected.text }}
        </p>
        <div class="flex gap-2">
          <button @click="navigateTo(selected)"
            class="bg-transparent border border-bible-accent text-bible-accent px-3.5 py-2 rounded-md text-sm hover:bg-bible-accent hover:text-bible-bg transition-colors">
            Ir a este versículo →
          </button>
          <button @click="toggleFavorite(selected)" class="border px-3.5 py-2 rounded-md text-sm transition-colors"
            :class="isFavorite(selected) ? 'border-bible-accent-bright text-bible-accent-bright bg-bible-accent/10' : 'border-bible-border text-bible-muted hover:border-bible-accent'">
            ★
          </button>
        </div>
      </aside>
    </div>
  </div>
</template>

<style scoped>
.sky {
  background: var(--bible-bg);
  min-height: 100vh;
  color: var(--bible-text);
  padding: 2rem;
  font-family: var(--font-body);
}

.topbar h1 {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 1.9rem;
  color: var(--bible-accent-bright);
  margin-bottom: 0.25rem;
}

.hint {
  color: var(--bible-muted);
  font-size: 0.85rem;
  margin-bottom: 1rem;
}

.search {
  position: relative;
  max-width: 320px;
  margin-bottom: 1.5rem;
}

.search input {
  width: 100%;
  background: var(--bible-panel);
  border: 1px solid var(--bible-border);
  color: var(--bible-text);
  padding: 0.6rem 0.9rem;
  border-radius: 6px;
  font-family: var(--font-body);
  font-size: 0.9rem;
}

.search input:focus {
  outline: none;
  border-color: var(--bible-accent);
}

.suggestions {
  position: absolute;
  top: 105%;
  left: 0;
  right: 0;
  background: var(--bible-panel);
  border: 1px solid var(--bible-border);
  border-radius: 6px;
  list-style: none;
  padding: 0.25rem;
  margin: 0;
  z-index: 10;
}

.suggestions li {
  padding: 0.45rem 0.7rem;
  cursor: pointer;
  border-radius: 4px;
  font-size: 0.9rem;
}

.suggestions li:hover {
  background: var(--bible-border);
  color: var(--bible-accent-bright);
}

.search-error {
  color: var(--bible-error);
  font-size: 0.8rem;
  margin-top: 0.4rem;
}

.layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 1.5rem;
}

.graph {
  width: 100%;
  height: 640px;
  background: var(--bible-bg);
  border: 1px solid var(--bible-border);
  border-radius: 8px;
}

.panel {
  background: var(--bible-panel);
  border: 1px solid var(--bible-border);
  border-radius: 8px;
  padding: 1.25rem;
  height: fit-content;
}

.panel.empty {
  color: var(--bible-muted-dim);
  font-style: italic;
}

.ref {
  font-family: var(--font-body);
  font-size: 0.75rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--bible-accent);
}

.verse-text {
  font-family: var(--font-display);
  font-size: 1.15rem;
  line-height: 1.6;
  margin: 0.75rem 0 1.25rem;
  color: var(--bible-text);
}

.jump {
  background: transparent;
  border: 1px solid var(--bible-accent);
  color: var(--bible-accent);
  padding: 0.5rem 0.9rem;
  border-radius: 6px;
  cursor: pointer;
  font-family: var(--font-body);
  font-size: 0.85rem;
}

.jump:hover {
  background: var(--bible-accent);
  color: var(--bible-bg);
}
</style>