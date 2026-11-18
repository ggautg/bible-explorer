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
  <div class="flex items-center gap-3 rounded-lg border border-bible-border bg-bible-panel/60 px-4 py-3 text-bible-muted" role="status" aria-live="polite">
    <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-bible-border border-t-bible-accent" aria-hidden="true"></span>
    <span class="text-sm">{{ message }}</span>
  </div>
</template>
