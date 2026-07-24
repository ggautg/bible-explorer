<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useNotes } from '@/composables/useNotes'
import NavHeader from '@/components/Bible/NavHeader.vue'

const { notes, deleteNote } = useNotes()
</script>

<template>

  <Head title="Mis notas" />
  <div class="bg-bible-bg min-h-screen text-bible-text p-4 md:p-8 font-bible-body">
    <NavHeader back-href="/leer" back-label="← Leer">
      <h1 class="font-bible-display font-semibold text-2xl md:text-3xl text-bible-accent-bright mt-2">
        Mis notas
      </h1>
    </NavHeader>

    <p v-if="!notes.length" class="text-bible-muted-dim italic">
      Todavía no escribiste ninguna nota. Buscá el ✎ mientras leés un capítulo.
    </p>

    <div v-else class="max-w-2xl space-y-4">
      <div v-for="n in notes" :key="`${n.book_code}-${n.chapter}-${n.verse}`"
        class="bg-bible-panel border border-bible-border rounded-lg p-4">
        <div class="flex justify-between items-start gap-3">
          <Link :href="`/leer/${n.book_code}/${n.chapter}`"
            class="text-bible-accent text-xs uppercase tracking-wide hover:underline">
            {{ n.book_name }} {{ n.chapter }}:{{ n.verse }}
          </Link>
          <button @click="deleteNote(n)" class="text-bible-error text-xs hover:underline">
            Borrar
          </button>
        </div>
        <p class="text-bible-text text-sm mt-2 leading-relaxed">{{ n.body }}</p>
      </div>
    </div>
  </div>
</template>