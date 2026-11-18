<script setup>
/**
 * Fondo decorativo de estrellas.
 *
 * Se utiliza en páginas principales para reforzar la identidad visual
 * "constelación" sin interferir con la interacción del contenido.
 */
defineProps({
  /** Opacidad del fondo para ajustar la intensidad visual. */
  opacity: {
    type: [Number, String],
    default: 0.6,
  },
});
</script>

<template>
  <div class="pointer-events-none absolute inset-0 overflow-hidden" :style="{ opacity }" aria-hidden="true">
    <div class="stars h-full w-full"></div>
  </div>
</template>

<style scoped>
.stars {
  background-image:
    radial-gradient(1.5px 1.5px at 20px 30px, #ede6d6, transparent),
    radial-gradient(1.5px 1.5px at 120px 80px, #ede6d6, transparent),
    radial-gradient(1px 1px at 200px 40px, #8f8874, transparent),
    radial-gradient(1.5px 1.5px at 300px 120px, #ede6d6, transparent),
    radial-gradient(1px 1px at 400px 60px, #8f8874, transparent),
    radial-gradient(1.5px 1.5px at 500px 20px, #ede6d6, transparent),
    radial-gradient(1px 1px at 600px 150px, #8f8874, transparent),
    radial-gradient(1.5px 1.5px at 700px 90px, #ede6d6, transparent),
    radial-gradient(1px 1px at 80px 180px, #8f8874, transparent),
    radial-gradient(1.5px 1.5px at 350px 200px, #ede6d6, transparent);
  background-repeat: repeat;
  background-size: 800px 300px;
}
</style>
