<script setup>
/**
 * Indicador de carga reutilizable.
 *
 * Muestra un mensaje claro mientras se obtienen datos asíncronos para que la
 * interfaz sea predecible y fluida.
 */
defineProps({
  /** Texto descriptivo del estado de carga. */
  message: {
    type: String,
    default: 'Cargando información...',
  },
});
</script>

<template>
  <div class="flex items-center gap-3 rounded-2xl border border-bible-border bg-bible-panel/50 px-5 py-4 text-bible-muted" role="status" aria-live="polite">
    <span class="inline-block h-5 w-5 animate-spin rounded-full border-2 border-bible-border border-t-bible-accent" aria-hidden="true"></span>
    <span class="text-sm">{{ message }}</span>
  </div>
</template>
