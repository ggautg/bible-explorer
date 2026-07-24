<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import NavHeader from '@/components/Bible/NavHeader.vue'

const props = defineProps({ query: String, results: Array })

const searchInput = ref(props.query)

function submit() {
  if (searchInput.value.trim().length < 3) return
  router.get('/buscar', { q: searchInput.value.trim() })
}
</script>

<template>

  <Head title="Buscar en la Biblia" />
  <div class="bg-bible-bg min-h-screen text-bible-text p-4 md:p-8 font-bible-body">
    <NavHeader back-href="/leer" back-label="← Leer">
      <h1 class="font-bible-display font-semibold text-2xl md:text-3xl text-bible-accent-bright mt-2">
        Buscar
      </h1>
    </NavHeader>

    <div class="max-w-xl mb-8">
      <input v-model="searchInput" @keyup.enter="submit" type="text" placeholder="Ej: adulterio, amor, esperanza..."
        class="w-full bg-bible-panel border border-bible-border text-bible-text px-4 py-3 rounded-md focus:outline-none focus:border-bible-accent" />
      <p class="text-bible-muted-dim text-xs mt-1.5">Mínimo 3 caracteres</p>
    </div>

    <p v-if="query && !results.length" class="text-bible-muted-dim italic">
      No se encontraron versículos con "{{ query }}".
    </p>

    <div v-if="results.length" class="max-w-2xl">
      <p class="text-bible-muted text-sm mb-4">
        {{ results.length }} resultados para "{{ query }}", ordenados por relevancia
      </p>
      <div class="space-y-3">
        <div v-for="r in results" :key="`${r.book}-${r.chapter}-${r.verse}`"
          class="bg-bible-panel border border-bible-border rounded-lg p-4">
          <div class="flex justify-between items-start gap-3 mb-1.5">
            <Link :href="`/leer/${r.book}/${r.chapter}`"
              class="text-bible-accent text-xs uppercase tracking-wide hover:underline">
              {{ r.bookName }} {{ r.chapter }}:{{ r.verse }}
            </Link>
            <Link :href="`/bible/${r.book}/${r.chapter}/${r.verse}`"
              class="text-bible-muted-dim text-xs hover:text-bible-accent-bright" title="Ver conexiones">
              ✦
            </Link>
          </div>
          <p class="font-bible-display text-base leading-relaxed">{{ r.text }}</p>
        </div>
      </div>
    </div>
  </div>
</template>