<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import NavHeader from '@/components/Bible/NavHeader.vue'

const props = defineProps({ books: Array })

const oldTestament = computed(() => props.books.filter(b => b.testament === 'OT'))
const newTestament = computed(() => props.books.filter(b => b.testament === 'NT'))
</script>

<template>

  <Head title="Leer la Biblia" />
  <div class="bg-bible-bg min-h-screen text-bible-text p-4 md:p-8 font-bible-body">
    <NavHeader>
      <h1 class="font-bible-display font-semibold text-2xl md:text-3xl text-bible-accent-bright">Leer</h1>
    </NavHeader>

    <section>
      <h2
        class="font-bible-display text-lg text-bible-muted uppercase tracking-wide mb-4 border-b border-bible-border pb-2">
        Antiguo Testamento
      </h2>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5">
        <Link v-for="b in oldTestament" :key="b.code" :href="`/leer/${b.code}`"
          class="bg-bible-panel border border-bible-border rounded-md px-3.5 py-3 text-bible-text text-sm hover:border-bible-accent hover:text-bible-accent-bright transition-colors">
          {{ b.name }}
        </Link>
      </div>
    </section>

    <section class="mt-8">
      <h2
        class="font-bible-display text-lg text-bible-muted uppercase tracking-wide mb-4 border-b border-bible-border pb-2">
        Nuevo Testamento
      </h2>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5">
        <Link v-for="b in newTestament" :key="b.code" :href="`/leer/${b.code}`"
          class="bg-bible-panel border border-bible-border rounded-md px-3.5 py-3 text-bible-text text-sm hover:border-bible-accent hover:text-bible-accent-bright transition-colors">
          {{ b.name }}
        </Link>
      </div>
    </section>
  </div>
</template>