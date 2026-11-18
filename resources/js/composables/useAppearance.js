import { computed, onMounted, ref } from 'vue';

/**
 * Aplica el tema visual de la aplicación.
 *
 * Para mantener una identidad y paleta coherentes en todas las pantallas,
 * el explorador utiliza siempre el modo oscuro.
 */
export function updateTheme(_value) {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.classList.add('dark');
}

/** Persiste una cookie simple en el navegador. */
const setCookie = (name, value, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }
    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

/** Obtiene la apariencia guardada en almacenamiento local. */
const getStoredAppearance = () => {
    if (typeof window === 'undefined') {
        return null;
    }
    return localStorage.getItem('appearance');
};

/** Inicializa el tema al cargar la aplicación en el cliente. */
export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    const savedAppearance = getStoredAppearance() || 'dark';
    localStorage.setItem('appearance', savedAppearance);
    setCookie('appearance', savedAppearance);
    updateTheme(savedAppearance);
}

const appearance = ref('dark');

/**
 * Composable de apariencia para componentes que gestionan preferencias.
 */
export function useAppearance() {
    onMounted(() => {
        const savedAppearance = getStoredAppearance();
        if (savedAppearance) {
            appearance.value = savedAppearance;
        }
    });

    const resolvedAppearance = computed(() => 'dark');

    /**
     * Actualiza la preferencia de apariencia (se conserva por compatibilidad).
     */
    function updateAppearance(value) {
        appearance.value = value || 'dark';
        localStorage.setItem('appearance', appearance.value);
        setCookie('appearance', appearance.value);
        updateTheme(appearance.value);
    }

    return { appearance, resolvedAppearance, updateAppearance };
}
