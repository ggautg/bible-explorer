<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, Heart, Trash2 } from '@lucide/vue';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';
import EmptyState from '@/components/Bible/EmptyState.vue';
import LoadingState from '@/components/Bible/LoadingState.vue';
import { useFavorites } from '@/composables/useFavorites';
import { useToast } from '@/composables/useToast';

/**
 * Página con los versículos favoritos del usuario.
 * Permite revisar y quitar versículos guardados.
 */
const { favorites, toggleFavorite, loading, loaded } = useFavorites();
const { showToast } = useToast();

/** Quita un versículo de favoritos y muestra confirmación. */
async function removeFavorite(favorite) {
    try {
        await toggleFavorite(favorite);
        showToast('Versículo quitado de favoritos.');
    } catch {
        showToast('No se pudo quitar el favorito.');
    }
}
</script>

<template>
    <Head title="Favoritos" />

    <BiblePageShell max-width-class="max-w-7xl">
        <template #header>
            <p
                class="mb-1 inline-flex items-center gap-2 text-xs font-medium tracking-[0.2em] text-bible-accent uppercase"
            >
                <Heart class="h-4 w-4" aria-hidden="true" />
                Guardados
            </p>
            <h1
                class="font-bible-display text-3xl font-semibold text-bible-accent-bright md:text-4xl"
            >
                Favoritos
            </h1>
            <p class="mt-1 text-sm text-bible-muted">
                Tus versículos guardados para volver a ellos cuando quieras.
            </p>
        </template>

        <LoadingState
            v-if="loading && !loaded"
            message="Cargando tus versículos favoritos..."
        />

        <EmptyState
            v-else-if="loaded && !favorites.length"
            :icon="Heart"
            title="Todavía no tenés favoritos"
            description="Cuando encuentres un versículo especial, guardalo desde la lectura o el grafo."
        >
            <template #actions>
                <Link
                    href="/leer"
                    class="inline-flex items-center gap-2 rounded-full bg-bible-accent px-5 py-2.5 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright"
                >
                    <BookOpen class="h-4 w-4" aria-hidden="true" />
                    Ir a leer
                </Link>
            </template>
        </EmptyState>

        <div v-else class="space-y-3">
            <article
                v-for="f in favorites"
                :key="`${f.book_code}-${f.chapter}-${f.verse}`"
                class="group rounded-2xl border border-bible-border bg-bible-panel/50 p-4 transition-colors hover:border-bible-accent/30 md:p-5"
            >
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <Link
                            :href="`/bible/${f.book_code}/${f.chapter}/${f.verse}`"
                            class="inline-flex items-center gap-2 text-sm font-medium tracking-wide text-bible-accent-bright uppercase transition-colors hover:text-bible-accent"
                        >
                            <span
                                class="rounded-md border border-bible-accent/20 bg-bible-accent/10 px-2 py-0.5 text-[11px]"
                                >{{ f.book_name }} {{ f.chapter }}:{{
                                    f.verse
                                }}</span
                            >
                        </Link>
                        <p
                            class="mt-3 font-bible-display text-lg leading-relaxed text-bible-text"
                        >
                            {{ f.text }}
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="removeFavorite(f)"
                        class="inline-flex items-center gap-2 rounded-xl border border-bible-border px-3 py-2 text-sm text-bible-muted transition-colors hover:border-bible-error/50 hover:bg-bible-error/5 hover:text-bible-error focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                    >
                        <Trash2 class="h-4 w-4" aria-hidden="true" />
                        Quitar
                    </button>
                </div>
            </article>
        </div>
    </BiblePageShell>
</template>
