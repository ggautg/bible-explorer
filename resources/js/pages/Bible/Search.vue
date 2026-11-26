<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, BookOpen, Search, SearchX, Sparkles } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';
import EmptyState from '@/components/Bible/EmptyState.vue';

/**
 * Página de búsqueda de texto dentro de la Biblia.
 * Permite buscar palabras o frases y navegar al capítulo o grafo.
 */
const props = defineProps({
    /** Consulta actual. */
    query: {
        type: String,
        default: '',
    },
    /** Resultados encontrados. */
    results: {
        type: Array,
        default: () => [],
    },
});

const searchInput = ref(props.query || '');
const isSubmitting = ref(false);

watch(
    () => props.query,
    (value) => {
        searchInput.value = value || '';
        isSubmitting.value = false;
    },
);

const canSearch = computed(() => searchInput.value.trim().length >= 3);

/** Ejecuta la búsqueda con la consulta ingresada. */
function submit() {
    if (!canSearch.value || isSubmitting.value) {
        return;
    }

    isSubmitting.value = true;
    router.get(
        '/buscar',
        { q: searchInput.value.trim() },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
}
</script>

<template>
    <Head title="Buscar en la Biblia" />

    <BiblePageShell max-width-class="max-w-7xl">
        <template #header>
            <p
                class="mb-1 inline-flex items-center gap-2 text-xs font-medium tracking-[0.2em] text-bible-accent uppercase"
            >
                <Search class="h-4 w-4" aria-hidden="true" />
                Búsqueda
            </p>
            <h1
                class="font-bible-display text-3xl font-semibold text-bible-accent-bright md:text-4xl"
            >
                Buscar en la Biblia
            </h1>
            <p class="mt-1 text-sm text-bible-muted">
                Encontrá versículos por palabras, temas o frases.
            </p>
        </template>

        <form
            class="mb-8 rounded-2xl border border-bible-border bg-bible-panel/45 p-4 md:p-5"
            @submit.prevent="submit"
        >
            <label
                for="busqueda-biblia"
                class="mb-2 block text-sm font-medium text-bible-text"
                >¿Qué querés buscar?</label
            >
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-bible-muted"
                        aria-hidden="true"
                    />
                    <input
                        id="busqueda-biblia"
                        v-model="searchInput"
                        type="text"
                        autocomplete="off"
                        placeholder="Ejemplo: amor, esperanza, fe..."
                        class="w-full rounded-xl border border-bible-border bg-bible-bg/40 py-3 pr-4 pl-10 text-bible-text placeholder:text-bible-muted-dim focus:ring-2 focus:ring-bible-accent focus:outline-none"
                    />
                </div>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-bible-accent px-5 py-3 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="!canSearch || isSubmitting"
                >
                    <Sparkles class="h-4 w-4" aria-hidden="true" />
                    {{ isSubmitting ? 'Buscando...' : 'Buscar' }}
                </button>
            </div>
            <p class="mt-2 text-xs text-bible-muted">
                Escribí al menos 3 caracteres para obtener resultados
                relevantes.
            </p>
        </form>

        <div v-if="query && query.trim().length >= 3">
            <div
                v-if="results.length"
                class="mb-4 flex flex-wrap items-center justify-between gap-2"
            >
                <p class="text-sm text-bible-muted">
                    <span class="font-medium text-bible-accent-bright">{{
                        results.length
                    }}</span>
                    resultado{{ results.length === 1 ? '' : 's' }} para “{{
                        query
                    }}”
                </p>
                <p
                    class="inline-flex items-center gap-1.5 text-xs tracking-wide text-bible-muted-dim uppercase"
                >
                    <BookOpen class="h-3.5 w-3.5" aria-hidden="true" />
                    Referencias bíblicas
                </p>
            </div>

            <div v-if="results.length" class="space-y-3">
                <article
                    v-for="r in results"
                    :key="`${r.book}-${r.chapter}-${r.verse}`"
                    class="group rounded-2xl border border-bible-border bg-bible-panel/50 p-4 transition-colors hover:border-bible-accent/30 hover:bg-bible-panel/70 md:p-5"
                >
                    <div
                        class="mb-2 flex flex-wrap items-center justify-between gap-2"
                    >
                        <Link
                            :href="`/leer/${r.book}/${r.chapter}`"
                            class="inline-flex items-center gap-2 text-sm font-medium tracking-wide text-bible-accent-bright uppercase transition-colors hover:text-bible-accent"
                        >
                            <span
                                class="rounded-md border border-bible-accent/20 bg-bible-accent/10 px-2 py-0.5 text-[11px]"
                                >{{ r.bookName }} {{ r.chapter }}:{{
                                    r.verse
                                }}</span
                            >
                        </Link>
                        <Link
                            :href="`/bible/${r.book}/${r.chapter}/${r.verse}`"
                            class="inline-flex items-center gap-1 text-sm text-bible-muted transition-colors hover:text-bible-accent-bright"
                        >
                            Ver conexiones
                            <ArrowRight
                                class="h-3.5 w-3.5"
                                aria-hidden="true"
                            />
                        </Link>
                    </div>
                    <p
                        class="font-bible-display text-lg leading-relaxed text-bible-text"
                    >
                        {{ r.text }}
                    </p>
                </article>
            </div>

            <EmptyState
                v-else
                :icon="SearchX"
                title="No se encontraron resultados"
                description="Probá con otra palabra, una frase más corta o un término diferente."
            />
        </div>

        <EmptyState
            v-else
            :icon="Search"
            title="Empezá una búsqueda"
            description="Ingresá un término para explorar versículos relacionados en toda la Biblia."
        />
    </BiblePageShell>
</template>
