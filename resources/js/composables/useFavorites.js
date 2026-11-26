import axios from 'axios';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

/**
 * Estado compartido de favoritos en el cliente.
 * Se carga una sola vez y se reutiliza entre vistas/componentes.
 */
const favorites = ref([]);
const loaded = ref(false);
const loading = ref(false);
let loadId = 0;

/** Genera una clave única para identificar un versículo favorito. */
function keyOf(v) {
    return `${v.book_code}-${v.chapter}-${v.verse}`;
}

/** Descarta el estado en memoria al cambiar de usuario. */
export function resetFavorites() {
    loadId++;
    favorites.value = [];
    loaded.value = false;
    loading.value = false;
}

/** Carga los favoritos del usuario autenticado desde la API. */
async function loadFavorites(notify = false) {
    if (loading.value || loaded.value) {
        return;
    }

    const current = ++loadId;
    loading.value = true;

    try {
        const { data } = await axios.get('/api/favorites');

        if (current !== loadId) {
            return;
        }

        favorites.value = Array.isArray(data) ? data : [];
        loaded.value = true;
    } catch (error) {
        if (current === loadId && notify) {
            toast.error(
                'No se pudieron cargar los favoritos. Intentá de nuevo.',
            );
        }

        throw error;
    } finally {
        if (current === loadId) {
            loading.value = false;
        }
    }
}

/**
 * Composable para consultar y modificar favoritos.
 */
export function useFavorites() {
    if (typeof window !== 'undefined' && !loaded.value && !loading.value) {
        void loadFavorites(true).catch(() => {});
    }

    /** Indica si un versículo ya está marcado como favorito. */
    function isFavorite(v) {
        return favorites.value.some((f) => keyOf(f) === keyOf(v));
    }

    /** Agrega o quita un versículo de favoritos. */
    async function toggleFavorite(v) {
        if (!loaded.value) {
            await loadFavorites();
        }

        const payload = {
            book_code: v.book_code ?? v.book,
            book_name: v.bookName ?? v.book_name,
            chapter: v.chapter,
            verse: v.verse,
            text: v.text,
        };

        if (isFavorite(payload)) {
            await axios.delete('/api/favorites', {
                data: {
                    book_code: payload.book_code,
                    chapter: payload.chapter,
                    verse: payload.verse,
                },
            });
            favorites.value = favorites.value.filter(
                (f) => keyOf(f) !== keyOf(payload),
            );

            return;
        }

        const { data } = await axios.post('/api/favorites', payload);
        favorites.value = [data, ...favorites.value];
    }

    return {
        favorites,
        isFavorite,
        toggleFavorite,
        loaded,
        loading,
        loadFavorites,
    };
}
