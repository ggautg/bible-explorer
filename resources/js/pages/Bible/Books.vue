<script setup>
import { Head, Link } from '@inertiajs/vue3';
import {
    BookOpen,
    ChevronRight,
    Library,
    ScrollText,
    Sparkles,
} from '@lucide/vue';
import { computed } from 'vue';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';

/**
 * Página de listado de libros bíblicos.
 * Presenta los testamentos como secciones claras y escaneables.
 */
const props = defineProps({
    /** Colección de libros disponibles. */
    books: {
        type: Array,
        default: () => [],
    },
});

const oldTestament = computed(() =>
    props.books.filter((b) => b.testament === 'OT'),
);
const newTestament = computed(() =>
    props.books.filter((b) => b.testament === 'NT'),
);

/** Tarjetas rápidas para orientar al usuario al entrar a la lectura. */
const quickStats = computed(() => [
    { label: 'Libros', value: props.books.length, icon: Library },
    {
        label: 'Antiguo Testamento',
        value: oldTestament.value.length,
        icon: ScrollText,
    },
    {
        label: 'Nuevo Testamento',
        value: newTestament.value.length,
        icon: Sparkles,
    },
]);
</script>

<template>
    <Head title="Leer la Biblia" />

    <BiblePageShell max-width-class="max-w-7xl">
        <template #header>
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <p
                        class="mb-1 inline-flex items-center gap-2 text-xs font-medium tracking-[0.2em] text-bible-accent uppercase"
                    >
                        <BookOpen class="h-4 w-4" aria-hidden="true" />
                        Lectura
                    </p>
                    <h1
                        class="font-bible-display text-3xl font-semibold text-bible-accent-bright md:text-4xl"
                    >
                        Leer la Biblia
                    </h1>
                    <p class="mt-1 text-sm text-bible-muted">
                        Elegí un libro, abrí un capítulo y empezá a leer con
                        calma.
                    </p>
                </div>
                <Link
                    href="/buscar"
                    class="inline-flex items-center justify-center gap-2 rounded-full border border-bible-border bg-bible-panel/40 px-4 py-2 text-sm font-medium text-bible-text transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                >
                    Buscar un pasaje
                    <ChevronRight class="h-4 w-4" aria-hidden="true" />
                </Link>
            </div>
        </template>

        <!-- Resumen rápido para dar contexto antes de la lista. -->
        <div class="mb-8 grid gap-3 sm:grid-cols-3">
            <div
                v-for="stat in quickStats"
                :key="stat.label"
                class="rounded-2xl border border-bible-border bg-bible-panel/45 p-4"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-bible-accent/20 bg-bible-bg/40 text-bible-accent"
                    >
                        <component
                            :is="stat.icon"
                            class="h-5 w-5"
                            aria-hidden="true"
                        />
                    </div>
                    <div>
                        <p
                            class="text-2xl leading-none font-semibold text-bible-accent-bright"
                        >
                            {{ stat.value }}
                        </p>
                        <p class="mt-1 text-xs text-bible-muted">
                            {{ stat.label }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-10">
            <section>
                <div
                    class="mb-4 flex items-end justify-between gap-4 border-b border-bible-border pb-3"
                >
                    <div class="flex items-center gap-2">
                        <ScrollText
                            class="h-5 w-5 text-bible-accent"
                            aria-hidden="true"
                        />
                        <h2 class="font-bible-display text-xl text-bible-text">
                            Antiguo Testamento
                        </h2>
                    </div>
                    <span
                        class="rounded-full border border-bible-border bg-bible-bg/30 px-2.5 py-1 text-xs text-bible-muted"
                        >{{ oldTestament.length }} libros</span
                    >
                </div>
                <div
                    class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5"
                >
                    <Link
                        v-for="b in oldTestament"
                        :key="b.code"
                        :href="`/leer/${b.code}`"
                        class="group flex items-center justify-between gap-2 rounded-xl border border-bible-border bg-bible-panel/60 px-3.5 py-3 text-sm text-bible-text transition-all hover:-translate-y-0.5 hover:border-bible-accent/60 hover:bg-bible-panel/80 hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                    >
                        <span class="truncate">{{ b.name }}</span>
                        <ChevronRight
                            class="h-4 w-4 shrink-0 text-bible-muted transition-colors group-hover:text-bible-accent"
                            aria-hidden="true"
                        />
                    </Link>
                </div>
            </section>

            <section>
                <div
                    class="mb-4 flex items-end justify-between gap-4 border-b border-bible-border pb-3"
                >
                    <div class="flex items-center gap-2">
                        <Sparkles
                            class="h-5 w-5 text-bible-accent"
                            aria-hidden="true"
                        />
                        <h2 class="font-bible-display text-xl text-bible-text">
                            Nuevo Testamento
                        </h2>
                    </div>
                    <span
                        class="rounded-full border border-bible-border bg-bible-bg/30 px-2.5 py-1 text-xs text-bible-muted"
                        >{{ newTestament.length }} libros</span
                    >
                </div>
                <div
                    class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5"
                >
                    <Link
                        v-for="b in newTestament"
                        :key="b.code"
                        :href="`/leer/${b.code}`"
                        class="group flex items-center justify-between gap-2 rounded-xl border border-bible-border bg-bible-panel/60 px-3.5 py-3 text-sm text-bible-text transition-all hover:-translate-y-0.5 hover:border-bible-accent/60 hover:bg-bible-panel/80 hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                    >
                        <span class="truncate">{{ b.name }}</span>
                        <ChevronRight
                            class="h-4 w-4 shrink-0 text-bible-muted transition-colors group-hover:text-bible-accent"
                            aria-hidden="true"
                        />
                    </Link>
                </div>
            </section>
        </div>
    </BiblePageShell>
</template>
