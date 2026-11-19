<script setup>
/**
 * Tarjeta de métrica para el dashboard del perfil.
 * Muestra una etiqueta, un valor destacado y un ícono.
 */
defineProps({
  /** Etiqueta descriptiva de la métrica. */
  label: { type: String, required: true },
  /** Valor numérico o textual a destacar. */
  value: { type: [Number, String], required: true },
  /** Componente de ícono (lucide-vue) a renderizar. */
  icon: { type: [Object, Function], default: null },
});
</script>

<template>
  <div class="rounded-2xl border border-bible-border/70 bg-bible-bg/35 p-4 md:p-5">
    <div class="mb-2 flex items-center justify-between gap-2">
      <p class="text-xs font-medium uppercase tracking-wide text-bible-muted">{{ label }}</p>
      <component :is="icon" v-if="icon" class="h-4 w-4 text-bible-accent/80" aria-hidden="true" />
    </div>
    <p class="text-2xl font-semibold tabular-nums text-bible-text md:text-3xl">{{ value }}</p>
  </div>
</template>