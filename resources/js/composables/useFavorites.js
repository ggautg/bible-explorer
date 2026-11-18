import { ref } from 'vue';
import axios from 'axios';

/**
 * Estado compartido de favoritos en el cliente.
 * Se carga una sola vez y se reutiliza entre vistas/componentes.
 */
const favorites = ref([]);
const loaded = ref(false);
const loading = ref(false);

/** Genera una clave única para identificar un versículo favorito. */
function keyOf(v) {
  return `${v.book_code}-${v.chapter}-${v.verse}`;
}

/** Carga los favoritos del usuario autenticado desde la API. */
async function loadFavorites() {
  if (loading.value || loaded.value) {
    return;
  }

  loading.value = true;

  try {
    const { data } = await axios.get('/api/favorites');
    favorites.value = Array.isArray(data) ? data : [];
    loaded.value = true;
  } finally {
    loading.value = false;
  }
}

/**
 * Composable para consultar y modificar favoritos.
 */
export function useFavorites() {
  if (typeof window !== 'undefined' && !loaded.value && !loading.value) {
    void loadFavorites();
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
      favorites.value = favorites.value.filter((f) => keyOf(f) !== keyOf(payload));
      return;
    }

    const { data } = await axios.post('/api/favorites', payload);
    favorites.value = [data, ...favorites.value];
  }

  return { favorites, isFavorite, toggleFavorite, loaded, loading, loadFavorites };
}