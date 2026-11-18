<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { logout } from '@/routes';
import testimonials from '@/helpers/testimonials.js';
import TestimonialCard from '@/components/Bible/TestimonialCard.vue';
import StarfieldBackground from '@/components/Bible/StarfieldBackground.vue';

/**
 * Página de bienvenida del explorador bíblico.
 * Presenta la propuesta de valor, el versículo del día y accesos principales.
 */
const props = defineProps({
  /** Versículo destacado del día, o null si aún no hay datos cargados. */
  verseOfTheDay: {
    type: Object,
    default: null,
  },
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
</script>

<template>
  <Head title="Inicio" />

  <div class="relative min-h-screen overflow-hidden bg-bible-bg text-bible-text font-bible-body">
    <StarfieldBackground />

    <main class="relative mx-auto grid max-w-6xl grid-cols-1 items-start gap-12 px-6 py-10 lg:grid-cols-[1.15fr_0.85fr] lg:gap-16 lg:py-16">
      <!-- Columna principal: presentación y acciones -->
      <section class="flex flex-col items-center text-center lg:sticky lg:top-16">
        <svg width="220" height="140" viewBox="0 0 220 140" class="mb-8" aria-hidden="true">
          <g stroke="#C9A227" stroke-opacity="0.45" stroke-width="1">
            <line x1="110" y1="70" x2="40" y2="30" />
            <line x1="110" y1="70" x2="180" y2="25" />
            <line x1="110" y1="70" x2="60" y2="115" />
            <line x1="110" y1="70" x2="165" y2="105" />
            <line x1="40" y1="30" x2="180" y2="25" />
            <line x1="60" y1="115" x2="165" y2="105" />
          </g>
          <circle cx="110" cy="70" r="6" fill="#E8C87A" />
          <circle cx="40" cy="30" r="3" fill="#8A7B54" />
          <circle cx="180" cy="25" r="3" fill="#8A7B54" />
          <circle cx="60" cy="115" r="3" fill="#8A7B54" />
          <circle cx="165" cy="105" r="3" fill="#8A7B54" />
        </svg>

        <p class="mb-3 text-xs font-medium uppercase tracking-[0.2em] text-bible-accent">Explorador bíblico</p>
        <h1 class="mb-4 max-w-2xl font-bible-display text-4xl font-semibold leading-tight text-bible-accent-bright md:text-5xl">
          Cada versículo tiene su constelación
        </h1>
        <p class="mb-8 max-w-xl text-base leading-relaxed text-bible-muted md:text-lg">
          Explora la Biblia como una red de conexiones. Descubre citas, ecos y referencias entre pasajes,
          y sigue el hilo desde cualquier versículo.
        </p>

        <div class="mb-8 w-full max-w-xl rounded-xl border border-bible-border bg-bible-panel/90 p-5 text-left shadow-sm">
          <p class="text-xs font-medium uppercase tracking-wide text-bible-accent">Versículo del día</p>

          <template v-if="verseOfTheDay">
            <p class="mt-3 font-bible-display text-xl leading-relaxed text-bible-text">
              “{{ verseOfTheDay.text }}”
            </p>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
              <p class="text-sm text-bible-muted">
                {{ verseOfTheDay.bookName }} {{ verseOfTheDay.chapter }}:{{ verseOfTheDay.verse }}
              </p>
              <Link
                :href="`/bible/${verseOfTheDay.book}/${verseOfTheDay.chapter}/${verseOfTheDay.verse}`"
                class="text-sm font-medium text-bible-accent-bright transition-colors hover:text-bible-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent rounded"
              >
                Ver conexiones →
              </Link>
            </div>
          </template>

          <template v-else>
            <p class="mt-3 text-sm leading-relaxed text-bible-muted">
              El versículo del día estará disponible cuando se cargue el contenido bíblico en la base de datos.
            </p>
          </template>
        </div>

        <div class="flex flex-wrap justify-center gap-3">
          <Link
            href="/leer"
            class="inline-flex items-center justify-center rounded-md bg-bible-accent px-5 py-3 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
          >
            Empezar a leer
          </Link>
          <Link
            href="/bible/GEN/1/1"
            class="inline-flex items-center justify-center rounded-md border border-bible-border px-5 py-3 text-sm font-medium text-bible-text transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
          >
            Explorar un ejemplo
          </Link>
        </div>

        <div v-if="user" class="mt-8 rounded-lg border border-bible-border/70 bg-bible-panel/50 px-4 py-3 text-sm text-bible-muted">
          Sesión iniciada como <span class="font-medium text-bible-accent-bright">{{ user.name }}</span>.
          <Link
            :href="logout()"
            method="post"
            as="button"
            class="ml-2 text-bible-muted underline-offset-2 transition-colors hover:text-bible-error hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent rounded"
          >
            Cerrar sesión
          </Link>
        </div>

        <div v-else class="mt-8 flex flex-wrap justify-center gap-4 text-sm">
          <Link
            href="/login"
            class="text-bible-muted transition-colors hover:text-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent rounded"
          >
            Iniciar sesión
          </Link>
          <Link
            href="/register"
            class="text-bible-muted transition-colors hover:text-bible-accent-bright focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent rounded"
          >
            Crear cuenta
          </Link>
        </div>
      </section>

      <!-- Columna secundaria: testimonios -->
      <section class="w-full">
        <h2 class="mb-4 text-center text-sm font-medium uppercase tracking-[0.18em] text-bible-muted lg:text-left">
          Lo que dicen quienes ya lo usan
        </h2>
        <div class="grid grid-cols-1 gap-4">
          <TestimonialCard v-for="(testimonial, index) in testimonials" :key="index" :testimonial="testimonial" />
        </div>
      </section>
    </main>
  </div>
</template>
