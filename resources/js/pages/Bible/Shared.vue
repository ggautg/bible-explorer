<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'

const props = defineProps({ verse: Object })
const page = usePage()
</script>

<template>
  <Head :title="`${verse.bookName} ${verse.chapter}:${verse.verse}`" />

  <div class="relative min-h-screen bg-bible-bg text-bible-text font-bible-body overflow-hidden flex items-center justify-center p-6">
    <div class="absolute inset-0 pointer-events-none opacity-60">
      <div class="stars"></div>
    </div>

    <div class="relative max-w-lg text-center">
      <svg width="120" height="80" viewBox="0 0 120 80" class="mx-auto mb-8" aria-hidden="true">
        <g stroke="#C9A227" stroke-opacity="0.5" stroke-width="1">
          <line x1="60" y1="40" x2="20" y2="15" />
          <line x1="60" y1="40" x2="100" y2="12" />
          <line x1="60" y1="40" x2="30" y2="65" />
          <line x1="60" y1="40" x2="95" y2="62" />
        </g>
        <circle cx="60" cy="40" r="5" fill="#E8C87A" />
        <circle cx="20" cy="15" r="2.5" fill="#8A7B54" />
        <circle cx="100" cy="12" r="2.5" fill="#8A7B54" />
        <circle cx="30" cy="65" r="2.5" fill="#8A7B54" />
        <circle cx="95" cy="62" r="2.5" fill="#8A7B54" />
      </svg>

      <p class="font-bible-display text-2xl md:text-3xl text-bible-text leading-relaxed mb-4">
        "{{ verse.text }}"
      </p>
      <p class="text-bible-accent text-sm uppercase tracking-wide mb-10">
        {{ verse.bookName }} {{ verse.chapter }}:{{ verse.verse }}
      </p>

      <div v-if="page.props.auth.user" class="flex gap-4 justify-center flex-wrap">
        <Link
          :href="`/bible/${verse.book}/${verse.chapter}/${verse.verse}`"
          class="bg-bible-accent text-bible-bg font-medium px-6 py-3 rounded-md hover:bg-bible-accent-bright transition-colors"
        >
          Ver su constelación →
        </Link>
      </div>

      <div v-else class="flex flex-col items-center gap-4">
        <Link
          :href="`/register`"
          class="bg-bible-accent text-bible-bg font-medium px-6 py-3 rounded-md hover:bg-bible-accent-bright transition-colors"
        >
          Empezar a explorar
        </Link>
        <p class="text-bible-muted-dim text-xs">
          Creá una cuenta gratis para leer, guardar favoritos y explorar conexiones como esta.
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.stars {
  width: 100%;
  height: 100%;
  background-image:
    radial-gradient(1.5px 1.5px at 20px 30px, #EDE6D6, transparent),
    radial-gradient(1.5px 1.5px at 120px 80px, #EDE6D6, transparent),
    radial-gradient(1px 1px at 200px 40px, #8F8874, transparent),
    radial-gradient(1.5px 1.5px at 300px 120px, #EDE6D6, transparent),
    radial-gradient(1px 1px at 400px 60px, #8F8874, transparent),
    radial-gradient(1.5px 1.5px at 500px 20px, #EDE6D6, transparent);
  background-repeat: repeat;
  background-size: 800px 300px;
}
</style>