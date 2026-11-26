import { computed, onMounted, ref } from 'vue';

/** Temas bíblicos disponibles (3 claros y 3 oscuros). */
export const BIBLICAL_THEMES = [
    {
        id: 'pergamino',
        label: 'Pergamino',
        description: 'Tonos de pergamino antiguo, cálidos y claros.',
        mode: 'light',
    },
    {
        id: 'olivo',
        label: 'Olivo',
        description: 'Verdes suaves inspirados en el monte de los Olivos.',
        mode: 'light',
    },
    {
        id: 'jordan',
        label: 'Jordán',
        description: 'Azules claros como las aguas del Jordán.',
        mode: 'light',
    },
    {
        id: 'getsemani',
        label: 'Getsemaní',
        description: 'Noche serena con dorados de constelación.',
        mode: 'dark',
    },
    {
        id: 'sinai',
        label: 'Sinaí',
        description: 'Oscuro desértico con acentos de bronce.',
        mode: 'dark',
    },
    {
        id: 'patmos',
        label: 'Patmos',
        description: 'Azul profundo como el mar en la noche.',
        mode: 'dark',
    },
];

export const DEFAULT_THEME = 'getsemani';

const THEME_IDS = new Set(BIBLICAL_THEMES.map((t) => t.id));
const DARK_THEMES = new Set(
    BIBLICAL_THEMES.filter((t) => t.mode === 'dark').map((t) => t.id),
);

/** Resuelve un id de tema válido (con compatibilidad hacia atrás). */
export function resolveThemeId(value) {
    if (value && THEME_IDS.has(value)) {
        return value;
    }

    // Compatibilidad con valores anteriores
    if (value === 'light') {
        return 'pergamino';
    }

    if (value === 'dark' || value === 'system' || !value) {
        return DEFAULT_THEME;
    }

    return DEFAULT_THEME;
}

/** Indica si un tema es oscuro. */
export function isDarkTheme(themeId) {
    return DARK_THEMES.has(resolveThemeId(themeId));
}

/** Persiste una cookie simple en el navegador. */
const setCookie = (name, value, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

/** Lee una cookie por nombre. */
const getCookie = (name) => {
    if (typeof document === 'undefined') {
        return null;
    }

    const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));

    return match ? decodeURIComponent(match[1]) : null;
};

/** Obtiene el tema guardado. */
const getStoredTheme = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    try {
        return (
            localStorage.getItem('theme') || localStorage.getItem('appearance')
        );
    } catch {
        return null;
    }
};

/**
 * Aplica el tema visual en el documento.
 */
export function applyTheme(value) {
    if (typeof document === 'undefined') {
        return DEFAULT_THEME;
    }

    const theme = resolveThemeId(value);
    const dark = isDarkTheme(theme);
    const root = document.documentElement;

    root.setAttribute('data-theme', theme);
    root.classList.toggle('dark', dark);
    root.style.colorScheme = dark ? 'dark' : 'light';

    // Compatibilidad con código que lee appearance
    try {
        localStorage.setItem('theme', theme);
        localStorage.setItem('appearance', theme);
    } catch {
        // Sin almacenamiento disponible: no es crítico.
    }

    setCookie('theme', theme);
    setCookie('appearance', theme);

    return theme;
}

/** Inicializa el tema al cargar la aplicación en el cliente. */
export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    const saved =
        getStoredTheme() || getCookie('theme') || getCookie('appearance');
    applyTheme(saved || DEFAULT_THEME);
}

const theme = ref(DEFAULT_THEME);
const appearance = ref(DEFAULT_THEME);

/**
 * Composable de apariencia/tema para componentes que gestionan preferencias.
 */
export function useAppearance() {
    onMounted(() => {
        const saved =
            getStoredTheme() || getCookie('theme') || getCookie('appearance');
        const resolved = resolveThemeId(saved || DEFAULT_THEME);
        theme.value = resolved;
        appearance.value = resolved;
    });

    const resolvedAppearance = computed(() =>
        isDarkTheme(theme.value) ? 'dark' : 'light',
    );

    /**
     * Actualiza el tema seleccionado.
     */
    function updateAppearance(value) {
        const resolved = applyTheme(value);
        theme.value = resolved;
        appearance.value = resolved;
    }

    function setTheme(value) {
        updateAppearance(value);
    }

    return {
        theme,
        appearance,
        resolvedAppearance,
        themes: BIBLICAL_THEMES,
        setTheme,
        updateAppearance,
    };
}
