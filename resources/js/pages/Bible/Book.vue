<script setup>
import { Head, Link } from '@inertiajs/vue3'
import NavHeader from '@/components/Bible/NavHeader.vue'

defineProps({ book: Object, chapters: Array })
</script>

<template>

  <Head :title="book.name" />
  <div class="bg-bible-bg min-h-screen text-bible-text p-4 md:p-8 font-bible-body">
    <NavHeader back-href="/leer" back-label="← Libros">
      <h1 class="font-bible-display font-semibold text-2xl md:text-3xl text-bible-accent-bright mt-2">
        {{ book.name }}
      </h1>
    </NavHeader>

    <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-2 max-w-2xl">
      <Link v-for="c in chapters" :key="c" :href="`/leer/${book.code}/${c}`"
        class="bg-bible-panel border border-bible-border rounded-md py-2.5 text-center text-bible-text font-bible-display hover:border-bible-accent hover:text-bible-accent-bright transition-colors">
        {{ c }}
      </Link>
    </div>
  </div>
</template>