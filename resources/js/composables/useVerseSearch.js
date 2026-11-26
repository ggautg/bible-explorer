import { router } from '@inertiajs/vue3';
import { onUnmounted, ref, watch } from 'vue';

/**
 * Composable para buscar y resolver referencias bíblicas en el grafo.
 */
export function useVerseSearch() {
    const searchQuery = ref('');
    const suggestions = ref([]);
    const searchError = ref('');
    const isResolving = ref(false);
    let debounceTimer = null;
    let suggestController = null;
    let suggestRequestId = 0;

    watch(searchQuery, (value) => {
        clearTimeout(debounceTimer);
        suggestController?.abort();
        suggestRequestId++;
        searchError.value = '';

        const trimmed = value.trim();

        // Si el usuario ya escribió una referencia completa, no mostramos sugerencias.
        if (/\d+:\d+$/.test(trimmed)) {
            suggestions.value = [];

            return;
        }

        if (trimmed.length < 1) {
            suggestions.value = [];

            return;
        }

        const requestId = suggestRequestId;
        debounceTimer = setTimeout(async () => {
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
            } catch (error) {
                if (
                    requestId === suggestRequestId &&
                    error.name !== 'AbortError'
                ) {
                    suggestions.value = [];
                }
            }
        }, 220);
    });

    onUnmounted(() => {
        clearTimeout(debounceTimer);
        suggestController?.abort();
    });

    /** Completa el campo con el libro sugerido. */
    function pickSuggestion(book) {
        searchQuery.value = `${book.name} `;
        suggestions.value = [];
        searchError.value = '';
    }

    /** Resuelve la referencia ingresada y navega al versículo correspondiente. */
    async function submitSearch() {
        const query = searchQuery.value.trim();

        if (!query || isResolving.value) {
            return;
        }

        isResolving.value = true;
        searchError.value = '';

        try {
            const res = await fetch(
                `/api/bible/resolve?ref=${encodeURIComponent(query)}`,
            );
            const data = await res.json();

            if (!res.ok) {
                searchError.value =
                    data.error || 'No se pudo resolver la referencia.';

                return;
            }

            suggestions.value = [];
            router.visit(`/bible/${data.book}/${data.chapter}/${data.verse}`);
        } catch {
            searchError.value =
                'Ocurrió un error al buscar la referencia. Inténtalo de nuevo.';
        } finally {
            isResolving.value = false;
        }
    }

    return {
        searchQuery,
        suggestions,
        searchError,
        isResolving,
        pickSuggestion,
        submitSearch,
    };
}
