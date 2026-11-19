<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { BookHeart, CalendarDays, NotebookPen, Sparkles } from '@lucide/vue';
import { computed } from 'vue';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';

/**
 * Página personal de devocionario.
 * Ofrece un espacio simple para la oración, la lectura y la reflexión diaria.
 */

const todayLabel = computed(() =>
  new Intl.DateTimeFormat('es-AR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
  }).format(new Date()),
);

const prompts = [
  '¿Qué te está hablando Dios hoy a través de su Palabra?',
  '¿Por qué podés dar gracias en este momento?',
  '¿Hay algo que necesites entregar en oración?',
];
</script>

<template>
  <Head title="Mi devocionario" />

  <BiblePageShell max-width-class="max-w-7xl">
    <template #header>
      <p class="mb-1 text-xs font-medium uppercase tracking-[0.2em] text-bible-accent">Espacio personal</p>
      <h1 class="font-bible-display text-3xl font-semibold text-bible-accent-bright md:text-4xl">Mi devocionario</h1>
      <p class="mt-2 max-w-2xl text-sm text-bible-muted md:text-base">
        Un lugar tranquilo para leer, orar y anotar lo que Dios va haciendo en tu vida.
      </p>
    </template>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1.3fr_1fr]">
      <section class="space-y-6">
        <article class="rounded-3xl border border-bible-border bg-gradient-to-br from-bible-panel/85 to-bible-bg/50 p-6 shadow-sm md:p-7">
          <div class="mb-4 flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
              <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-bible-accent/15 text-bible-accent-bright">
                <CalendarDays class="h-5 w-5" />
              </div>
              <div>
                <h2 class="text-lg font-semibold text-bible-text">Hoy</h2>
                <p class="text-sm capitalize text-bible-muted">{{ todayLabel }}</p>
              </div>
            </div>
          </div>
          <blockquote class="rounded-2xl border-l-4 border-bible-accent/60 bg-bible-bg/40 px-5 py-4">
            <p class="font-bible-display text-xl italic leading-relaxed text-bible-text md:text-2xl">
              “Lámpara es a mis pies tu palabra, y lumbrera a mi camino.”
            </p>
            <footer class="mt-2 text-sm font-medium text-bible-accent-bright">Salmos 119:105</footer>
          </blockquote>
          <p class="mt-4 text-sm leading-relaxed text-bible-muted md:text-base">
            Tomate un momento para leer con calma, agradecer y escuchar. No hace falta apurarse: lo importante es encontrarte con Dios.
          </p>
        </article>

        <article class="rounded-3xl border border-bible-border bg-bible-panel/80 p-6 shadow-sm md:p-7">
          <div class="mb-4 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-bible-accent/15 text-bible-accent-bright">
              <NotebookPen class="h-5 w-5" />
            </div>
            <h2 class="text-lg font-semibold text-bible-text">Para reflexionar</h2>
          </div>
          <ul class="space-y-3">
            <li
              v-for="(prompt, i) in prompts"
              :key="i"
              class="flex gap-3 rounded-2xl border border-bible-border/70 bg-bible-bg/35 px-4 py-3 text-sm leading-relaxed text-bible-text/95 md:text-[15px]"
            >
              <Sparkles class="mt-0.5 h-4 w-4 shrink-0 text-bible-accent" aria-hidden="true" />
              <span>{{ prompt }}</span>
            </li>
          </ul>
        </article>
      </section>

      <aside class="space-y-5">
        <section class="rounded-3xl border border-bible-border bg-bible-panel/80 p-6 shadow-sm">
          <div class="mb-3 flex items-center gap-2.5">
            <BookHeart class="h-5 w-5 text-bible-accent-bright" />
            <h2 class="text-base font-semibold text-bible-text">Accesos rápidos</h2>
          </div>
          <p class="mb-4 text-sm leading-relaxed text-bible-muted">
            Seguí tu tiempo devocional con lectura, búsqueda y tus notas.
          </p>
          <div class="flex flex-col gap-2.5">
            <Link
              href="/leer"
              class="inline-flex items-center justify-center rounded-xl bg-bible-accent px-4 py-2.5 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright"
            >
              Leer la Biblia
            </Link>
            <Link
              href="/buscar"
              class="inline-flex items-center justify-center rounded-xl border border-bible-border bg-bible-bg/40 px-4 py-2.5 text-sm font-medium text-bible-text transition-colors hover:border-bible-accent/50 hover:text-bible-accent-bright"
            >
              Buscar un pasaje
            </Link>
            <Link
              href="/notas"
              class="inline-flex items-center justify-center rounded-xl border border-bible-border bg-bible-bg/40 px-4 py-2.5 text-sm font-medium text-bible-text transition-colors hover:border-bible-accent/50 hover:text-bible-accent-bright"
            >
              Ver mis notas
            </Link>
            <Link
              href="/favoritos"
              class="inline-flex items-center justify-center rounded-xl border border-bible-border bg-bible-bg/40 px-4 py-2.5 text-sm font-medium text-bible-text transition-colors hover:border-bible-accent/50 hover:text-bible-accent-bright"
            >
              Ver mis favoritos
            </Link>
          </div>
        </section>

        <section class="rounded-3xl border border-bible-border bg-bible-panel/60 p-6">
          <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-bible-accent">Sugerencia</h2>
          <p class="text-sm leading-relaxed text-bible-muted md:text-[15px]">
            Podés elegir un pasaje, leerlo despacio y escribir una nota con lo que te llamó la atención. Con el tiempo, este espacio va a reflejar tu caminar.
          </p>
        </section>
      </aside>
    </div>
  </BiblePageShell>
</template>