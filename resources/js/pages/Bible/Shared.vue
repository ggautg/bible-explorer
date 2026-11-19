<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import StarfieldBackground from '@/components/Bible/StarfieldBackground.vue';

/**
 * Página pública para visualizar un versículo compartido.
 */
const props = defineProps({
  /** Información del versículo compartido. */
  verse: {
    type: Object,
    required: true,
  },
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
</script>

<template>
  <Head :title="`${verse.bookName} ${verse.chapter}:${verse.verse}`" />

  <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-bible-bg p-6 text-bible-text font-bible-body">
    <StarfieldBackground />

    <main class="relative w-full max-w-xl rounded-2xl border border-bible-border bg-bible-panel/85 p-8 text-center shadow-lg">
      <p class="mb-4 text-xs font-medium uppercase tracking-[0.2em] text-bible-accent">Versículo compartido</p>

      <p class="font-bible-display text-2xl leading-relaxed text-bible-text md:text-3xl">
        “{{ verse.text }}”
      </p>

      <p class="mt-4 text-sm font-medium uppercase tracking-wide text-bible-muted">
        {{ verse.bookName }} {{ verse.chapter }}:{{ verse.verse }}
      </p>

      <div class="mt-8 flex flex-col items-center gap-3">
        <Link
          v-if="user"
          :href="`/bible/${verse.book}/${verse.chapter}/${verse.verse}`"
          class="inline-flex items-center justify-center rounded-md bg-bible-accent px-5 py-3 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
        >
          Ver sus conexiones
        </Link>

        <template v-else>
          <Link
            href="/register"
            class="inline-flex items-center justify-center rounded-md bg-bible-accent px-5 py-3 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
          >
            Creá tu cuenta para explorar
          </Link>
          <p class="max-w-sm text-sm leading-relaxed text-bible-muted">
            Registrate gratis para leer capítulos completos, guardar favoritos, escribir notas y explorar conexiones.
          </p>
          <Link
            href="/login"
            class="text-sm text-bible-muted transition-colors hover:text-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent rounded"
          >
            Ya tengo una cuenta
          </Link>
        </template>
      </div>
    </main>
  </div>
</template>
