<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useFavorites } from '@/composables/useFavorites'

const { favorites, toggleFavorite } = useFavorites()
</script>

<template>
  <Head title="Favoritos" />
  <div class="bg-bible-bg min-h-screen text-bible-text p-4 md:p-8 font-bible-body">
    <header class="mb-8">
      <Link href="/leer" class="text-bible-muted text-sm hover:text-bible-accent">← Leer</Link>
      <h1 class="font-bible-display font-semibold text-2xl md:text-3xl text-bible-accent-bright mt-2">
        Favoritos
      </h1>
    </header>

    <p v-if="!favorites.length" class="text-bible-muted-dim italic">
      Todavía no guardaste ningún versículo. Buscá la ★ mientras leés o explorás el grafo.
    </p>

    <div v-else class="max-w-2xl space-y-4">
      <div
        v-for="f in [...favorites].sort((a, b) => b.savedAt - a.savedAt)"
        :key="`${f.book}-${f.chapter}-${f.verse}`"
        class="bg-bible-panel border border-bible-border rounded-lg p-4"
      >
        <div class="flex justify-between items-start gap-3">
          <Link :href="`/bible/${f.book}/${f.chapter}/${f.verse}`" class="text-bible-accent text-xs uppercase tracking-wide hover:underline">
            {{ f.bookName }} {{ f.chapter }}:{{ f.verse }}
          </Link>
          <button @click="toggleFavorite(f)" class="text-bible-accent-bright text-sm hover:opacity-70">
            ★ Quitar
          </button>
        </div>
        <p class="font-bible-display text-lg mt-2 leading-relaxed">{{ f.text }}</p>
      </div>
    </div>
  </div>
</template>