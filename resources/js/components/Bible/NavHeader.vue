<script setup>
import { Link } from '@inertiajs/vue3'
import { logout } from '@/routes'

defineProps({
  backHref: { type: String, default: null },
  backLabel: { type: String, default: '← Volver' },
})
</script>

<template>
  <header class="flex justify-between items-start mb-8 gap-4 flex-wrap">
    <div>
      <Link v-if="backHref" :href="backHref" class="text-bible-muted text-sm hover:text-bible-accent">
        {{ backLabel }}
      </Link>
      <slot />
    </div>

    <div class="flex items-center gap-4 flex-wrap">
      <Link href="/leer" class="text-bible-accent text-sm hover:underline whitespace-nowrap">
        Leer
      </Link>
      <Link href="/buscar" class="text-bible-accent text-sm hover:underline whitespace-nowrap">
  Buscar
</Link>
      <Link href="/bible/GEN/1/1" class="text-bible-accent text-sm hover:underline whitespace-nowrap">
        Grafo
      </Link>
      <Link href="/favoritos" class="text-bible-accent text-sm hover:underline whitespace-nowrap">
        ★ Favoritos
      </Link>
      <Link href="/notas" class="text-bible-accent text-sm hover:underline whitespace-nowrap">
        ✎ Notas
      </Link>
      <Link :href="logout()" method="post" as="button" class="text-bible-muted text-sm hover:text-bible-error whitespace-nowrap">
        Cerrar sesión
      </Link>
    </div>
  </header>
</template>