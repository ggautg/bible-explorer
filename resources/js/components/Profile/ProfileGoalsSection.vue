<script setup>
import { Link } from '@inertiajs/vue3';
import { Flame } from '@lucide/vue';

/**
 * Sección de metas personales del perfil.
 * Renderiza cada meta con barra/timeline de progreso y, si hay, apoyos.
 */
const props = defineProps({
  /** Nombre del dueño del perfil (para textos contextuales). */
  profileName: { type: String, required: true },
  /** Objeto de metas: { title, supporters, items[] }. */
  goals: { type: Object, required: true },
});

/** Genera las unidades visuales de una meta para la barra de progreso. */
function goalUnits(goal) {
  const total = Number(goal?.total || 0);
  const current = Number(goal?.current || 0);
  return Array.from({ length: total }, (_, i) => {
    const n = i + 1;
    return { n, completed: n < current, current: n === current };
  });
}

/** Etiqueta del puntero según el tipo de unidad. */
function goalPointerLabel(goal) {
  const unit = (goal?.unit || '').toLowerCase();
  if (unit.startsWith('cap')) return `Cap. ${goal.current}`;
  if (unit.startsWith('día') || unit.startsWith('dia')) return `Día ${goal.current}`;
  return `${goal.current}`;
}

/** Forma corta de la unidad para leyendas compactas. */
function goalUnitShort(goal) {
  const unit = (goal?.unit || '').toLowerCase();
  if (unit.startsWith('cap')) return 'cap.';
  if (unit.startsWith('día') || unit.startsWith('dia')) return 'días';
  return goal?.unit || '';
}

/** Inicial para avatar de respaldo. */
function initial(name) {
  return (name || '?').trim().charAt(0).toUpperCase();
}
</script>

<template>
  <section
    class="rounded-3xl border border-bible-border bg-gradient-to-br from-bible-panel/85 to-bible-bg/50 p-6 shadow-sm md:p-7"
    aria-label="Metas personales"
  >
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-2.5">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-bible-accent/15 text-bible-accent-bright">
          <Flame class="h-5 w-5" aria-hidden="true" />
        </div>
        <div>
          <p class="text-xs font-medium uppercase tracking-wide text-bible-accent">Compromisos</p>
          <h2 class="text-lg font-semibold text-bible-text md:text-xl">{{ goals.title || 'Metas' }}</h2>
        </div>
      </div>
      <p class="text-sm text-bible-muted">
        {{ profileName }} va avanzando paso a paso. ¡Vamos que se puede!
      </p>
    </div>

    <div class="space-y-6">
      <article
        v-for="goal in (goals.items || [])"
        :key="goal.key || goal.label"
        class="rounded-2xl border border-bible-border/70 bg-bible-bg/35 p-4 md:p-5"
      >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div class="min-w-0">
            <h3 class="text-base font-semibold text-bible-text md:text-lg">{{ goal.label }}</h3>
            <p v-if="goal.description" class="mt-0.5 text-sm text-bible-muted">{{ goal.description }}</p>
            <p class="mt-2 text-sm text-bible-muted">
              Va por
              <span class="font-semibold text-bible-accent-bright">{{ goal.current }}</span>
              de <span class="font-semibold text-bible-text">{{ goal.total }}</span>
              {{ goal.unit }}.
            </p>
          </div>
          <div class="shrink-0 self-start rounded-xl border border-bible-accent/30 bg-bible-accent/10 px-3.5 py-2 text-center">
            <p class="text-[11px] uppercase tracking-wide text-bible-muted">Progreso</p>
            <p class="text-xl font-semibold text-bible-accent-bright">
              {{ goal.current }}<span class="text-sm text-bible-muted">/{{ goal.total }}</span>
            </p>
          </div>
        </div>

        <div class="mt-5">
          <div class="relative pt-7">
            <div
              class="absolute top-0 flex -translate-x-1/2 flex-col items-center"
              :style="{ left: `${((Number(goal.current) - 0.5) / Number(goal.total)) * 100}%` }"
            >
              <span class="whitespace-nowrap rounded-full bg-bible-accent px-2.5 py-0.5 text-[11px] font-semibold text-bible-bg shadow-sm">
                {{ goalPointerLabel(goal) }}
              </span>
              <span class="mt-1 h-0 w-0 border-l-[5px] border-r-[5px] border-t-[6px] border-l-transparent border-r-transparent border-t-bible-accent" aria-hidden="true"></span>
            </div>

            <ol
              class="grid gap-1.5 sm:gap-2"
              :style="{ gridTemplateColumns: `repeat(${goal.total}, minmax(0, 1fr))` }"
              :aria-label="`Progreso de ${goal.label}`"
            >
              <li v-for="u in goalUnits(goal)" :key="u.n" class="relative">
                <div
                  class="h-2 rounded-full transition-colors sm:h-2.5"
                  :class="u.completed || u.current ? 'bg-bible-accent' : 'bg-bible-border/60'"
                ></div>
                <div v-if="Number(goal.total) <= 14" class="mt-2 flex justify-center">
                  <span
                    class="flex h-6 w-6 items-center justify-center rounded-full border text-[10px] font-medium sm:h-7 sm:w-7 sm:text-xs"
                    :class="u.current
                      ? 'border-bible-accent bg-bible-accent/20 text-bible-accent-bright ring-2 ring-bible-accent/30'
                      : u.completed
                        ? 'border-bible-accent/50 bg-bible-accent/10 text-bible-accent-bright'
                        : 'border-bible-border/70 bg-bible-bg/40 text-bible-muted'"
                  >
                    {{ u.n }}
                  </span>
                </div>
              </li>
            </ol>

            <div v-if="Number(goal.total) > 14" class="mt-2 flex items-center justify-between text-[11px] text-bible-muted">
              <span>1</span>
              <span>{{ goal.current }} de {{ goal.total }} {{ goalUnitShort(goal) }}</span>
              <span>{{ goal.total }}</span>
            </div>
          </div>
        </div>
      </article>
    </div>

    <div v-if="goals.supporters && goals.supporters.length" class="mt-6 border-t border-bible-border/60 pt-5">
      <p class="mb-3 text-sm font-medium text-bible-text">Los chicos lo están bancando</p>
      <div class="flex flex-wrap gap-3">
        <Link
          v-for="supporter in goals.supporters"
          :key="supporter.id || supporter.slug || supporter.name"
          :href="supporter.slug ? `/perfil/${supporter.slug}` : '#'"
          class="group inline-flex items-center gap-3 rounded-2xl border border-bible-border/70 bg-bible-bg/35 px-3 py-2.5 transition-colors hover:border-bible-accent/40 hover:bg-bible-bg/50"
        >
          <img
            v-if="supporter.avatar"
            :src="supporter.avatar"
            :alt="`Foto de ${supporter.name}`"
            class="h-10 w-10 rounded-xl border border-bible-border object-cover"
            loading="lazy"
          />
          <div
            v-else
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-bible-border bg-bible-panel text-sm font-semibold text-bible-accent-bright"
            aria-hidden="true"
          >
            {{ initial(supporter.name) }}
          </div>
          <div class="min-w-0">
            <p class="text-sm font-medium text-bible-text group-hover:text-bible-accent-bright">{{ supporter.name }}</p>
            <p class="text-xs text-bible-muted">Acompañando sus metas</p>
          </div>
        </Link>
      </div>
    </div>
  </section>
</template>