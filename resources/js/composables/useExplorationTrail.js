import { ref } from 'vue';

/**
 * Historial de navegación por el grafo durante la sesión actual.
 */
const STORAGE_KEY = 'bible-trail';
const trail = ref(loadTrail());

/** Recupera el recorrido guardado en la sesión. */
function loadTrail() {
    try {
        const raw = sessionStorage.getItem(STORAGE_KEY);

        return raw ? JSON.parse(raw) : [];
    } catch {
        return [];
    }
}

/** Persiste el recorrido en la sesión del navegador. */
function persist() {
    try {
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(trail.value));
    } catch {
        // Si el almacenamiento no está disponible, se continúa sin persistencia.
    }
}

/**
 * Composable para registrar y limpiar el recorrido de exploración.
 */
export function useExplorationTrail() {
    /** Registra una visita a un versículo evitando duplicados consecutivos. */
    function visit(node) {
        const entry = {
            book: node.book,
            chapter: Number(node.chapter),
            verse: Number(node.verse),
        };
        const key = `${entry.book}-${entry.chapter}-${entry.verse}`;

        const last = trail.value[trail.value.length - 1];

        if (last && `${last.book}-${last.chapter}-${last.verse}` === key) {
            return;
        }

        trail.value = [...trail.value, entry];
        persist();
    }

    /** Limpia el recorrido de la sesión. */
    function clear() {
        trail.value = [];
        persist();
    }

    return { trail, visit, clear };
}
