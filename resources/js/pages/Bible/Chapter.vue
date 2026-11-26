<script setup>
import { Head, Link } from '@inertiajs/vue3';
import {
    BookOpen,
    ChevronLeft,
    ChevronRight,
    Copy,
    GitBranch,
    Heart,
    Languages,
    NotebookPen,
    Share2,
} from '@lucide/vue';
import { onUnmounted, ref } from 'vue';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';

import { useFavorites } from '@/composables/useFavorites';
import { useNotes } from '@/composables/useNotes';
import { useToast } from '@/composables/useToast';

/**
 * Página de lectura de un capítulo bíblico.
 * Permite leer versículos, comparar traducción, guardar favoritos,
 * escribir notas y compartir referencias.
 */
const props = defineProps({
    book: { type: Object, required: true },
    chapter: { type: Number, required: true },
    verses: { type: Array, default: () => [] },
    alternateVerses: { type: Object, default: () => ({}) },
    hasNext: { type: Boolean, default: false },
    hasPrev: { type: Boolean, default: false },
});

const {
    isFavorite,
    toggleFavorite,
    loading: favoritesLoading,
} = useFavorites();
const { noteFor, saveNote, deleteNote, loading: notesLoading } = useNotes();
const { showToast } = useToast();

const showComparison = ref(false);
const openNoteVerse = ref(null);
const draftText = ref('');
const copiedVerse = ref(null);
const savingNoteVerse = ref(null);
let copiedTimer = null;

onUnmounted(() => clearTimeout(copiedTimer));
const favoriteBusyVerse = ref(null);

/** Construye los datos base de un versículo para acciones de usuario. */
function versePayload(v) {
    return {
        book_code: props.book.code,
        bookName: props.book.name,
        chapter: props.chapter,
        verse: v.verse,
        text: v.text,
    };
}

/** Copia un versículo y su enlace para compartirlo. */
async function shareVerse(v) {
    const shareUrl = `${window.location.origin}/compartido/${props.book.code}/${props.chapter}/${v.verse}`;
    const shareText = `"${v.text}"\n${props.book.name} ${props.chapter}:${v.verse}\n${shareUrl}`;

    try {
        await navigator.clipboard.writeText(shareText);
        copiedVerse.value = v.verse;
        showToast('Versículo copiado al portapapeles.');
        clearTimeout(copiedTimer);
        copiedTimer = setTimeout(() => {
            if (copiedVerse.value === v.verse) {
                copiedVerse.value = null;
            }
        }, 1800);
    } catch {
        showToast('No se pudo copiar el versículo.');
    }
}

/** Abre o cierra el editor de nota para un versículo. */
function toggleNoteBox(v) {
    if (openNoteVerse.value === v.verse) {
        openNoteVerse.value = null;
        draftText.value = '';

        return;
    }

    const payload = versePayload(v);
    const existing = noteFor(payload);
    openNoteVerse.value = v.verse;
    draftText.value = existing?.body || '';
}

/** Guarda la nota del versículo actual. */
async function submitNote(v) {
    const body = draftText.value.trim();

    if (!body) {
        return;
    }

    savingNoteVerse.value = v.verse;

    try {
        await saveNote(versePayload(v), body);
        openNoteVerse.value = null;
        draftText.value = '';
        showToast('Nota guardada.');
    } catch {
        showToast('No se pudo guardar la nota.');
    } finally {
        savingNoteVerse.value = null;
    }
}

/** Elimina la nota del versículo actual. */
async function removeNote(v) {
    savingNoteVerse.value = v.verse;

    try {
        await deleteNote(versePayload(v));
        openNoteVerse.value = null;
        draftText.value = '';
        showToast('Nota eliminada.');
    } catch {
        showToast('No se pudo eliminar la nota.');
    } finally {
        savingNoteVerse.value = null;
    }
}

/** Alterna el estado de favorito de un versículo. */
async function onToggleFavorite(v) {
    favoriteBusyVerse.value = v.verse;
    const payload = versePayload(v);
    const wasFavorite = isFavorite(payload);

    try {
        await toggleFavorite(payload);
        showToast(
            wasFavorite
                ? 'Versículo quitado de favoritos.'
                : 'Versículo guardado en favoritos.',
        );
    } catch {
        showToast('No se pudo actualizar favoritos.');
    } finally {
        favoriteBusyVerse.value = null;
    }
}
</script>

<template>
    <Head :title="`${book.name} ${chapter}`" />

    <BiblePageShell
        :back-href="`/leer/${book.code}`"
        :back-label="`← Volver a ${book.name}`"
        max-width-class="max-w-7xl"
    >
        <template #header>
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
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
                        {{ book.name }} {{ chapter }}
                    </h1>
                    <p class="mt-1 text-sm text-bible-muted">
                        {{ verses.length }} versículos en este capítulo.
                    </p>
                </div>
                <div
                    class="inline-flex items-center gap-2 rounded-full border border-bible-border bg-bible-panel/40 px-3 py-1.5 text-xs text-bible-muted"
                >
                    <Languages
                        class="h-3.5 w-3.5 text-bible-accent"
                        aria-hidden="true"
                    />
                    RVG
                </div>
            </div>
        </template>

        <div
            class="mb-6 flex flex-wrap items-center gap-3 rounded-2xl border border-bible-border bg-bible-panel/35 p-3 md:p-4"
        >
            <button
                type="button"
                @click="showComparison = !showComparison"
                class="inline-flex items-center gap-2 rounded-xl border border-bible-border bg-bible-bg/40 px-3.5 py-2 text-sm text-bible-text transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="!Object.keys(alternateVerses || {}).length"
            >
                <Languages class="h-4 w-4" aria-hidden="true" />
                {{
                    showComparison
                        ? 'Ocultar comparación'
                        : 'Comparar traducción'
                }}
            </button>
            <p
                v-if="!Object.keys(alternateVerses || {}).length"
                class="text-xs text-bible-muted"
            >
                No hay traducción alternativa disponible para este capítulo.
            </p>
            <p v-else class="text-xs text-bible-muted">
                Podés comparar con “Palabra de Dios para ti”.
            </p>
        </div>

        <div
            v-if="favoritesLoading || notesLoading"
            class="mb-4 text-sm text-bible-muted"
        >
            Cargando tus favoritos y notas...
        </div>

        <section class="space-y-3">
            <article
                v-for="v in verses"
                :key="v.verse"
                class="group rounded-2xl border border-bible-border bg-bible-panel/45 p-4 transition-colors hover:border-bible-accent/25 md:p-5"
            >
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                >
                    <div class="min-w-0 flex-1">
                        <p
                            class="font-bible-display text-lg leading-relaxed text-bible-text md:text-xl"
                        >
                            <span
                                class="mr-2 inline-flex h-7 min-w-7 items-center justify-center rounded-lg border border-bible-accent/20 bg-bible-bg/60 px-1.5 align-middle font-bible-body text-xs font-semibold text-bible-accent"
                            >
                                {{ v.verse }}
                            </span>
                            {{ v.text }}
                        </p>

                        <p
                            v-if="showComparison && alternateVerses[v.verse]"
                            class="mt-3 rounded-xl border-l-2 border-bible-accent/50 bg-bible-bg/30 px-4 py-3 font-bible-body text-sm leading-relaxed text-bible-muted italic"
                        >
                            <span
                                class="mb-1 block text-[11px] font-medium tracking-wide text-bible-accent/80 uppercase not-italic"
                                >Palabra de Dios para ti</span
                            >
                            {{ alternateVerses[v.verse] }}
                        </p>
                    </div>

                    <!-- Acciones por versículo: notas, favoritos, grafo y compartir. -->
                    <div
                        class="flex flex-wrap items-center gap-2 md:max-w-[260px] md:justify-end"
                    >
                        <button
                            type="button"
                            @click="toggleNoteBox(v)"
                            class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-medium transition-colors focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                            :class="
                                noteFor(versePayload(v))
                                    ? 'border-bible-accent/50 bg-bible-accent/10 text-bible-accent-bright'
                                    : 'border-bible-border text-bible-muted hover:border-bible-accent hover:text-bible-accent-bright'
                            "
                            :aria-label="
                                noteFor(versePayload(v))
                                    ? 'Editar nota'
                                    : 'Agregar nota'
                            "
                        >
                            <NotebookPen
                                class="h-3.5 w-3.5"
                                aria-hidden="true"
                            />
                            {{ noteFor(versePayload(v)) ? 'Nota' : 'Nota' }}
                        </button>

                        <button
                            type="button"
                            @click="onToggleFavorite(v)"
                            class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-medium transition-colors focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                            :class="
                                isFavorite(versePayload(v))
                                    ? 'border-bible-accent/50 bg-bible-accent/10 text-bible-accent-bright'
                                    : 'border-bible-border text-bible-muted hover:border-bible-accent hover:text-bible-accent-bright'
                            "
                            :disabled="favoriteBusyVerse === v.verse"
                            :aria-label="
                                isFavorite(versePayload(v))
                                    ? 'Quitar de favoritos'
                                    : 'Guardar en favoritos'
                            "
                        >
                            <Heart
                                class="h-3.5 w-3.5"
                                :class="
                                    isFavorite(versePayload(v))
                                        ? 'fill-current'
                                        : ''
                                "
                                aria-hidden="true"
                            />
                            {{
                                isFavorite(versePayload(v))
                                    ? 'Guardado'
                                    : 'Guardar'
                            }}
                        </button>

                        <Link
                            :href="`/bible/${book.code}/${chapter}/${v.verse}`"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-bible-border px-2.5 py-1.5 text-xs font-medium text-bible-muted transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                            aria-label="Ver conexiones del versículo"
                        >
                            <GitBranch class="h-3.5 w-3.5" aria-hidden="true" />
                            Grafo
                        </Link>

                        <button
                            type="button"
                            @click="shareVerse(v)"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-bible-border px-2.5 py-1.5 text-xs font-medium text-bible-muted transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                            :aria-label="
                                copiedVerse === v.verse
                                    ? 'Versículo copiado'
                                    : 'Copiar versículo'
                            "
                        >
                            <component
                                :is="copiedVerse === v.verse ? Copy : Share2"
                                class="h-3.5 w-3.5"
                                aria-hidden="true"
                            />
                            {{
                                copiedVerse === v.verse
                                    ? 'Copiado'
                                    : 'Compartir'
                            }}
                        </button>
                    </div>
                </div>

                <div
                    v-if="openNoteVerse === v.verse"
                    class="mt-4 rounded-lg border border-bible-border bg-bible-bg/40 p-3"
                >
                    <label
                        :for="`nota-${v.verse}`"
                        class="mb-2 block text-xs font-medium tracking-wide text-bible-muted uppercase"
                    >
                        Tu nota personal
                    </label>
                    <textarea
                        :id="`nota-${v.verse}`"
                        v-model="draftText"
                        rows="3"
                        maxlength="5000"
                        placeholder="Escribí una reflexión breve sobre este versículo..."
                        class="w-full resize-y rounded-md border border-bible-border bg-bible-panel/60 px-3 py-2 text-sm text-bible-text placeholder:text-bible-muted-dim focus:ring-2 focus:ring-bible-accent focus:outline-none"
                    ></textarea>
                    <div
                        class="mt-3 flex flex-wrap items-center justify-between gap-2"
                    >
                        <button
                            v-if="noteFor(versePayload(v))"
                            type="button"
                            @click="removeNote(v)"
                            class="rounded text-sm text-bible-error transition-colors hover:underline focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                            :disabled="savingNoteVerse === v.verse"
                        >
                            Eliminar nota
                        </button>
                        <span v-else class="text-xs text-bible-muted"
                            >La nota se guarda solo para tu cuenta.</span
                        >

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="toggleNoteBox(v)"
                                class="rounded-md border border-bible-border px-3 py-1.5 text-sm text-bible-muted transition-colors hover:text-bible-text focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                @click="submitNote(v)"
                                class="rounded-md bg-bible-accent px-3 py-1.5 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="
                                    !draftText.trim() ||
                                    savingNoteVerse === v.verse
                                "
                            >
                                {{
                                    savingNoteVerse === v.verse
                                        ? 'Guardando...'
                                        : 'Guardar nota'
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </article>
        </section>

        <nav
            class="mt-10 flex items-center justify-between gap-4 border-t border-bible-border/70 pt-6 text-sm"
            aria-label="Navegación de capítulos"
        >
            <Link
                v-if="hasPrev"
                :href="`/leer/${book.code}/${chapter - 1}`"
                class="inline-flex items-center gap-2 rounded-xl border border-bible-border bg-bible-panel/30 px-4 py-2.5 text-bible-text transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
            >
                <ChevronLeft class="h-4 w-4" aria-hidden="true" />
                Capítulo anterior
            </Link>
            <span v-else></span>

            <Link
                v-if="hasNext"
                :href="`/leer/${book.code}/${chapter + 1}`"
                class="inline-flex items-center gap-2 rounded-xl border border-bible-border bg-bible-panel/30 px-4 py-2.5 text-bible-text transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
            >
                Capítulo siguiente
                <ChevronRight class="h-4 w-4" aria-hidden="true" />
            </Link>
        </nav>
    </BiblePageShell>
</template>
