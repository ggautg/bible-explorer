<script setup>
/**
 * Tarjeta de testimonio mostrada en la página de inicio.
 */
defineProps({
  /** Datos del testimonio (nombre, rol, texto e imagen). */
  testimonial: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <article class="rounded-xl border border-bible-border bg-bible-panel/80 p-5 shadow-sm">
    <p class="font-bible-display text-lg leading-relaxed text-bible-text">
      “{{ testimonial.text }}”
    </p>

    <div class="mt-5 flex items-center gap-3 border-t border-bible-border/70 pt-4">
      <img
        :src="testimonial.image"
        :alt="`Foto de ${testimonial.name}`"
        class="h-10 w-10 rounded-full border border-bible-border object-cover"
        loading="lazy"
      />
      <div class="min-w-0">
        <p class="text-sm font-medium text-bible-text">{{ testimonial.name }}</p>
        <p class="text-sm text-bible-muted">{{ testimonial.role }}</p>
      </div>
    </div>
  </article>
</template>
