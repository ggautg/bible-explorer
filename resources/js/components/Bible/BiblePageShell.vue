<script setup>
import NavHeader from '@/components/Bible/NavHeader.vue';

/**
 * Contenedor de página para las vistas del explorador bíblico.
 *
 * Estandariza el fondo, el espaciado, el ancho máximo y la cabecera de
 * navegación para mantener coherencia visual y facilitar el escaneo.
 */
defineProps({
  /** Enlace de retroceso opcional para la cabecera. */
  backHref: {
    type: String,
    default: null,
  },
  /** Texto visible del enlace de retroceso. */
  backLabel: {
    type: String,
    default: '← Volver',
  },
  /** Ancho máximo del contenido principal. */
  maxWidthClass: {
    type: String,
    default: 'max-w-5xl',
  },
  /** Oculta la navegación principal cuando no es necesaria. */
  hideNav: {
    type: Boolean,
    default: false,
  },
});
</script>

<template>
  <div class="min-h-screen bg-bible-bg text-bible-text font-bible-body">
    <div :class="['mx-auto w-full px-4 py-6 md:px-8 md:py-8', maxWidthClass]">
      <NavHeader v-if="!hideNav" :back-href="backHref" :back-label="backLabel">
        <slot name="header" />
      </NavHeader>
      <slot />
    </div>
  </div>
</template>
