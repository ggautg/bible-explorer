<script setup>
import { computed, onUnmounted, ref, watch } from 'vue';

/**
 * Selector de libro, capítulo y versículo para publicaciones.
 *
 * Usa los endpoints JSON de búsqueda de libros y de estructura bíblica; el
 * nombre del libro se deriva en el servidor al guardar, acá sólo se muestra.
 */
const props = defineProps({
    /** Código del libro seleccionado (por ejemplo, JHN). */
    bookCode: { type: String, default: null },
    /** Capítulo seleccionado. */
    chapter: { type: [Number, String], default: null },
    /** Versículo seleccionado. */
    verse: { type: [Number, String], default: null },
});

const emit = defineEmits(['change']);

const bookQuery = ref('');
const bookLabel = ref('');
const suggestions = ref([]);
const showSuggestions = ref(false);
const chapters = ref([]);
const verses = ref([]);
const loadingBooks = ref(false);
const loadingStructure = ref(false);
const error = ref('');

const localBook = ref(props.bookCode);
const localChapter = ref(props.chapter);
const localVerse = ref(props.verse);

const selectedReference = computed(() => {
    if (!bookLabel.value || !localChapter.value || !localVerse.value) {
        return null;
    }

    return `${bookLabel.value} ${localChapter.value}:${localVerse.value}`;
});

let suggestTimer = null;
let suggestController = null;
let suggestRequestId = 0;
let structureController = null;

onUnmounted(() => {
    clearTimeout(suggestTimer);
    suggestController?.abort();
    structureController?.abort();
});

/** Propaga la selección completa al compositor. */
function emitChange() {
    emit('change', {
        book_code: localBook.value || null,
        chapter: localChapter.value ? Number(localChapter.value) : null,
        verse: localVerse.value ? Number(localVerse.value) : null,
    });
}

watch(bookQuery, (value) => {
    clearTimeout(suggestTimer);
    suggestController?.abort();
    suggestRequestId++;
    showSuggestions.value = true;
    error.value = '';

    const trimmed = value.trim();

    // Si el campo muestra el libro ya elegido, no vuelve a buscarlo.
    if (!trimmed || (localBook.value && trimmed === bookLabel.value)) {
        suggestions.value = [];
        showSuggestions.value = false;

        return;
    }

    const requestId = suggestRequestId;
    suggestTimer = setTimeout(async () => {
        loadingBooks.value = true;
        suggestController = new AbortController();

        try {
            const res = await fetch(
                `/api/bible/books-search?q=${encodeURIComponent(trimmed)}`,
                { signal: suggestController.signal },
            );
            const data = res.ok ? await res.json() : [];

            if (requestId === suggestRequestId) {
                suggestions.value = data;
            }
        } catch (fetchError) {
            if (
                requestId === suggestRequestId &&
                fetchError.name !== 'AbortError'
            ) {
                error.value = 'No se pudo buscar el libro. Intentá de nuevo.';
            }
        } finally {
            if (requestId === suggestRequestId) {
                loadingBooks.value = false;
            }
        }
    }, 220);
});

watch(
    () => props.bookCode,
    (value) => {
        localBook.value = value;

        if (!value) {
            bookQuery.value = '';
            bookLabel.value = '';
            suggestions.value = [];
            chapters.value = [];
            verses.value = [];
        }
    },
);

watch(
    () => props.chapter,
    (value) => {
        localChapter.value = value;

        if (!value) {
            localVerse.value = null;
            verses.value = [];
        }
    },
);

watch(
    () => props.verse,
    (value) => {
        localVerse.value = value;
    },
);

/** Carga capítulos (o versículos de un capítulo) para el libro elegido. */
async function loadStructure(chapter = null) {
    structureController?.abort();
    structureController = new AbortController();
    loadingStructure.value = true;
    error.value = '';

    const params = new URLSearchParams({ book: localBook.value });

    if (chapter) {
        params.set('chapter', String(chapter));
    }

    try {
        const res = await fetch(`/api/bible/structure?${params}`, {
            signal: structureController.signal,
        });
        const data = await res.json();

        if (!res.ok) {
            error.value = data.error || 'No se pudo cargar la referencia.';

            return;
        }

        if (chapter) {
            verses.value = data.verses;
        } else {
            chapters.value = data.chapters;
        }
    } catch (fetchError) {
        if (fetchError.name !== 'AbortError') {
            error.value = 'No se pudo cargar la referencia. Intentá de nuevo.';
        }
    } finally {
        loadingStructure.value = false;
    }
}

/** Selecciona un libro de las sugerencias y carga sus capítulos. */
function selectBook(book) {
    clearTimeout(suggestTimer);
    suggestController?.abort();
    suggestRequestId++;
    localBook.value = book.code;
    bookLabel.value = book.name;
    bookQuery.value = book.name;
    suggestions.value = [];
    showSuggestions.value = false;
    localChapter.value = null;
    localVerse.value = null;
    chapters.value = [];
    verses.value = [];
    emitChange();
    loadStructure();
}

/** Al cambiar de capítulo se recargan los versículos disponibles. */
function selectChapter(value) {
    localChapter.value = value ? Number(value) : null;
    localVerse.value = null;
    verses.value = [];

    if (localChapter.value) {
        loadStructure(localChapter.value);
    }

    emitChange();
}

/** Al elegir versículo se cierra la lista de sugerencias de libro. */
function selectVerse(value) {
    localVerse.value = value ? Number(value) : null;
    showSuggestions.value = false;
    emitChange();
}
</script>

<template>
    <div class="space-y-2">
        <div class="grid gap-2 sm:grid-cols-3">
            <div class="relative sm:col-span-1">
                <label
                    class="mb-1 block text-xs font-medium text-bible-muted"
                    :for="`verse-book-${bookCode || 'new'}`"
                    >Libro</label
                >
                <input
                    :id="`verse-book-${bookCode || 'new'}`"
                    v-model="bookQuery"
                    type="text"
                    role="combobox"
                    aria-autocomplete="list"
                    :aria-expanded="showSuggestions && suggestions.length > 0"
                    aria-controls="verse-book-suggestions"
                    autocomplete="off"
                    placeholder="Ej. Juan"
                    class="w-full rounded-xl border border-bible-border bg-bible-bg/60 px-3 py-2 text-sm text-bible-text placeholder:text-bible-muted-dim focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                />
                <div
                    v-if="showSuggestions && suggestions.length"
                    id="verse-book-suggestions"
                    role="listbox"
                    class="absolute top-full z-10 mt-1 max-h-60 w-full overflow-auto rounded-xl border border-bible-border bg-bible-panel p-1 shadow-lg"
                >
                    <button
                        v-for="book in suggestions"
                        :key="book.code"
                        type="button"
                        role="option"
                        :aria-selected="book.code === localBook"
                        class="w-full rounded-lg px-3 py-2 text-left text-sm text-bible-text hover:bg-bible-bg/60 hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                        @click="selectBook(book)"
                    >
                        {{ book.name }}
                    </button>
                </div>
            </div>

            <div>
                <label
                    class="mb-1 block text-xs font-medium text-bible-muted"
                    :for="`verse-chapter-${bookCode || 'new'}`"
                    >Capítulo</label
                >
                <select
                    :id="`verse-chapter-${bookCode || 'new'}`"
                    :value="localChapter ?? ''"
                    :disabled="!localBook || loadingStructure"
                    class="w-full rounded-xl border border-bible-border bg-bible-bg/60 px-3 py-2 text-sm text-bible-text focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                    @change="selectChapter($event.target.value)"
                >
                    <option value="">
                        {{ loadingStructure ? 'Cargando…' : 'Elegí capítulo' }}
                    </option>
                    <option v-for="n in chapters" :key="n" :value="n">
                        {{ n }}
                    </option>
                </select>
            </div>

            <div>
                <label
                    class="mb-1 block text-xs font-medium text-bible-muted"
                    :for="`verse-number-${bookCode || 'new'}`"
                    >Versículo</label
                >
                <select
                    :id="`verse-number-${bookCode || 'new'}`"
                    :value="localVerse ?? ''"
                    :disabled="!localChapter || loadingStructure"
                    class="w-full rounded-xl border border-bible-border bg-bible-bg/60 px-3 py-2 text-sm text-bible-text focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                    @change="selectVerse($event.target.value)"
                >
                    <option value="">
                        {{ loadingStructure ? 'Cargando…' : 'Elegí versículo' }}
                    </option>
                    <option v-for="n in verses" :key="n" :value="n">
                        {{ n }}
                    </option>
                </select>
            </div>
        </div>

        <p
            v-if="selectedReference"
            class="text-xs font-medium text-bible-accent-bright"
            aria-live="polite"
        >
            Referencia elegida: {{ selectedReference }}
        </p>
        <p v-if="loadingBooks" class="text-xs text-bible-muted" role="status">
            Buscando libros…
        </p>
        <p v-if="error" class="text-sm text-bible-error" role="alert">
            {{ error }}
        </p>
    </div>
</template>
