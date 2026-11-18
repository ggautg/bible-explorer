<script setup>
/**
 * Estado vacío reutilizable.
 *
 * Comunica de forma simple cuándo no hay contenido disponible y, opcionalmente,
 * sugiere una acción al usuario.
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
});
</script>

<template>
  <div class="rounded-lg border border-dashed border-bible-border bg-bible-panel/40 px-5 py-6 text-center">
    <p class="font-bible-display text-lg text-bible-accent-bright">{{ title }}</p>
    <p v-if="description" class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-bible-muted">
      {{ description }}
    </p>
    <div v-if="$slots.actions" class="mt-4 flex flex-wrap justify-center gap-3">
      <slot name="actions" />
    </div>
  </div>
</template>
