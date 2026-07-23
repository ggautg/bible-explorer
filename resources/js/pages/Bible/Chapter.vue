<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { logout } from '@/routes'

const props = defineProps({ book: Object, chapter: Number, verses: Array, hasNext: Boolean, hasPrev: Boolean })

import { useFavorites } from '@/composables/useFavorites'

const { isFavorite, toggleFavorite } = useFavorites()

function favoriteData(v) {
    return { book: props.book.code, bookName: props.book.name, chapter: props.chapter, verse: v.verse, text: v.text }
}
</script>

<template>
    <Head :title="`${book.name} ${chapter}`" />
    <div class="bg-bible-bg min-h-screen text-bible-text p-4 md:p-8 font-bible-body">
        <header class="flex justify-between items-start mb-8">
  <div>
    <Link :href="`/leer/${book.code}`" class="text-bible-muted text-sm hover:text-bible-accent">
      ← {{ book.name }}
    </Link>
    <h1 class="font-bible-display font-semibold text-2xl md:text-3xl text-bible-accent-bright mt-2">
      {{ book.name }} {{ chapter }}
    </h1>
  </div>
  <div class="flex items-center gap-4">
    <Link href="/leer" class="text-bible-accent text-sm hover:underline whitespace-nowrap">
      Leer
    </Link>
    <Link href="/favoritos" class="text-bible-accent text-sm hover:underline whitespace-nowrap">
      ★ Favoritos
    </Link>
    <Link :href="logout()" method="post" as="button" class="text-bible-muted text-sm hover:text-bible-error whitespace-nowrap">
      Cerrar sesión
    </Link>
  </div>
</header>

        <div class="max-w-2xl font-bible-display text-lg md:text-xl leading-relaxed">
            <p v-for="v in verses" :key="v.verse" class="mb-3.5 group relative">
                <span class="text-bible-accent font-bible-body text-xs align-super mr-1">{{ v.verse }}</span>
                {{ v.text }}
                <button @click="toggleFavorite(favoriteData(v))"
                    :title="isFavorite(favoriteData(v)) ? 'Quitar de favoritos' : 'Guardar en favoritos'"
                    class="text-sm ml-1.5 transition-opacity"
                    :class="isFavorite(favoriteData(v)) ? 'text-bible-accent-bright opacity-100' : 'text-bible-muted-dim opacity-0 group-hover:opacity-100 hover:text-bible-accent-bright'">
                    ★
                </button>
                <Link :href="`/bible/${book.code}/${chapter}/${v.verse}`" title="Ver conexiones"
                    class="text-bible-muted-dim no-underline text-sm ml-1 opacity-0 group-hover:opacity-100 hover:text-bible-accent-bright transition-opacity">
                    ✦
                </Link>
            </p>
        </div>

        <nav class="flex justify-between max-w-2xl mt-10">
            <Link v-if="hasPrev" :href="`/leer/${book.code}/${chapter - 1}`"
                class="text-bible-accent text-sm hover:underline">
                ← Capítulo anterior
            </Link>
            <span v-else></span>
            <Link v-if="hasNext" :href="`/leer/${book.code}/${chapter + 1}`"
                class="text-bible-accent text-sm hover:underline">
                Capítulo siguiente →
            </Link>
        </nav>
    </div>
</template>