import axios from 'axios';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

/**
 * Estado compartido de notas personales en el cliente.
 */
const notes = ref([]);
const loaded = ref(false);
const loading = ref(false);
let loadId = 0;

/** Genera una clave única para identificar la nota de un versículo. */
function keyOf(v) {
    return `${v.book_code}-${v.chapter}-${v.verse}`;
}

/** Descarta el estado en memoria al cambiar de usuario. */
export function resetNotes() {
    loadId++;
    notes.value = [];
    loaded.value = false;
    loading.value = false;
}

/** Carga las notas del usuario autenticado desde la API. */
async function loadNotes(notify = false) {
    if (loading.value || loaded.value) {
        return;
    }

    const current = ++loadId;
    loading.value = true;

    try {
        const { data } = await axios.get('/api/notes');

        if (current !== loadId) {
            return;
        }

        notes.value = Array.isArray(data) ? data : [];
        loaded.value = true;
    } catch (error) {
        if (current === loadId && notify) {
            toast.error('No se pudieron cargar las notas. Intentá de nuevo.');
        }

        throw error;
    } finally {
        if (current === loadId) {
            loading.value = false;
        }
    }
}

/**
 * Composable para consultar y gestionar notas de versículos.
 */
export function useNotes() {
    if (typeof window !== 'undefined' && !loaded.value && !loading.value) {
        void loadNotes(true).catch(() => {});
    }

    /** Obtiene la nota asociada a un versículo, si existe. */
    function noteFor(v) {
        return notes.value.find((n) => keyOf(n) === keyOf(v));
    }

    /** Crea o actualiza la nota de un versículo. */
    async function saveNote(v, body) {
        if (!loaded.value) {
            await loadNotes();
        }

        const payload = {
            book_code: v.book_code ?? v.book,
            book_name: v.bookName ?? v.book_name,
            chapter: v.chapter,
            verse: v.verse,
            body,
        };

        const { data } = await axios.put('/api/notes', payload);
        const index = notes.value.findIndex((n) => keyOf(n) === keyOf(payload));

        if (index >= 0) {
            notes.value[index] = data;
        } else {
            notes.value = [data, ...notes.value];
        }

        return data;
    }

    /** Elimina la nota asociada a un versículo. */
    async function deleteNote(v) {
        if (!loaded.value) {
            await loadNotes();
        }

        const payload = {
            book_code: v.book_code ?? v.book,
            chapter: v.chapter,
            verse: v.verse,
        };

        await axios.delete('/api/notes', { data: payload });
        notes.value = notes.value.filter((n) => keyOf(n) !== keyOf(payload));
    }

    return { notes, noteFor, saveNote, deleteNote, loaded, loading, loadNotes };
}
