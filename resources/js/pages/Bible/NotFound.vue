<script setup>
import { Head, Link } from '@inertiajs/vue3';
import StarfieldBackground from '@/components/Bible/StarfieldBackground.vue';

/**
 * Página mostrada cuando una referencia bíblica no existe o es inválida.
 */
</script>

<template>
  <Head title="Referencia no encontrada" />

  <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-bible-bg p-6 text-bible-text font-bible-body">
    <StarfieldBackground :opacity="0.35" />

    <main class="relative w-full max-w-lg rounded-2xl border border-bible-border bg-bible-panel/85 p-8 text-center">
      <p class="text-xs font-medium uppercase tracking-[0.2em] text-bible-accent">No se encontró la referencia</p>
      <h1 class="mt-3 font-bible-display text-3xl font-semibold text-bible-accent-bright">Esa estrella no está en el mapa</h1>
      <p class="mt-4 text-sm leading-relaxed text-bible-muted">
        La referencia que buscaste no existe o la dirección tiene un error. Puedes volver a la lectura o explorar una conexión conocida.
      </p>

      <div class="mt-8 flex flex-wrap justify-center gap-3">
        <Link
          href="/leer"
          class="inline-flex items-center justify-center rounded-md bg-bible-accent px-5 py-2.5 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
        >
          Ir a leer
        </Link>
        <Link
          href="/bible/GEN/1/1"
          class="inline-flex items-center justify-center rounded-md border border-bible-border px-5 py-2.5 text-sm font-medium text-bible-text transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
        >
          Ver un ejemplo
        </Link>
      </div>
    </main>
  </div>
</template>
