<script setup>
/**
 * Estado vacío reutilizable.
 *
 * Comunica de forma simple cuándo no hay contenido disponible y, opcionalmente,
 * sugiere una acción al usuario. Soporta ícono para mejorar el escaneo visual.
 */
defineProps({
  /** Título breve del estado vacío. */
  title: {
    type: String,
    default: 'No hay elementos para mostrar',
  },
  /** Descripción o ayuda contextual. */
  description: {
    type: String,
    default: '',
  },
  /** Componente de ícono (lucide) opcional. */
  icon: {
    type: [Object, Function],
    default: null,
  },
});
</script>

<template>
  <div class="rounded-2xl border border-dashed border-bible-border bg-bible-panel/35 px-6 py-10 text-center">
    <div v-if="icon" class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl border border-bible-accent/20 bg-bible-bg/40 text-bible-accent">
      <component :is="icon" class="h-7 w-7" aria-hidden="true" />
    </div>
    <p class="font-bible-display text-xl text-bible-accent-bright">{{ title }}</p>
    <p v-if="description" class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-bible-muted">
      {{ description }}
    </p>
    <div v-if="$slots.actions" class="mt-5 flex flex-wrap justify-center gap-3">
      <slot name="actions" />
    </div>
  </div>
</template>
