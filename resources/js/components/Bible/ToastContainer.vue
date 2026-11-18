<script setup>
import { useToast } from '@/composables/useToast';

/** Contenedor visual de notificaciones breves de la interfaz. */
const { toasts } = useToast();
</script>

<template>
  <div
    class="pointer-events-none fixed bottom-6 left-1/2 z-50 flex -translate-x-1/2 flex-col items-center gap-2 px-4"
    aria-live="polite"
    aria-atomic="true"
  >
    <TransitionGroup name="toast">
      <div
        v-for="t in toasts"
        :key="t.id"
        class="rounded-md border border-bible-accent/40 bg-bible-accent px-4 py-2.5 text-sm font-medium text-bible-bg shadow-lg"
      >
        {{ t.message }}
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>