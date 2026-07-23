<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { logout } from '@/routes'

defineProps({ book: Object, chapters: Array })
</script>

<template>

  <Head :title="book.name" />
  <div class="bg-bible-bg min-h-screen text-bible-text p-4 md:p-8 font-bible-body">
    <header class="flex justify-between items-start mb-8">
  <div>
    <Link href="/leer" class="text-bible-muted text-sm hover:text-bible-accent">← Libros</Link>
    <h1 class="font-bible-display font-semibold text-2xl md:text-3xl text-bible-accent-bright mt-2">
      {{ book.name }}
    </h1>
  </div>
  <div class="flex items-center gap-4">
    <Link href="/bible/GEN/1/1" class="text-bible-accent text-sm hover:underline whitespace-nowrap">
      Grafo
    </Link>
    <Link href="/favoritos" class="text-bible-accent text-sm hover:underline whitespace-nowrap">
      ★ Favoritos
    </Link>
    <Link :href="logout()" method="post" as="button" class="text-bible-muted text-sm hover:text-bible-error whitespace-nowrap">
      Cerrar sesión
    </Link>
  </div>
</header>

    <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-2 max-w-2xl">
      <Link v-for="c in chapters" :key="c" :href="`/leer/${book.code}/${c}`"
        class="bg-bible-panel border border-bible-border rounded-md py-2.5 text-center text-bible-text font-bible-display hover:border-bible-accent hover:text-bible-accent-bright transition-colors">
        {{ c }}
      </Link>
    </div>
  </div>
</template>