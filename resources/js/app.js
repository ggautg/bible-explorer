import { createInertiaApp, router } from '@inertiajs/vue3';
import axios from 'axios';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import ToastContainer from '@/components/Bible/ToastContainer.vue';
import Sonner from '@/components/ui/sonner/Sonner.vue';
import { initializeTheme } from '@/composables/useAppearance';
import { resetFavorites } from '@/composables/useFavorites';
import { resetNotes } from '@/composables/useNotes';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Explorador Bíblico';
let currentUserId = null;
let redirectingToLogin = false;

// Si la sesión caduca a mitad de flujo, las APIs responden 401 y el CSRF
// vencido responde 419. En ambos casos el usuario debe volver al login.
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (
            [401, 419].includes(error?.response?.status) &&
            !redirectingToLogin
        ) {
            redirectingToLogin = true;
            window.location.assign('/login');
        }

        return Promise.reject(error);
    },
);

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            './pages/' + name + '.vue',
            import.meta.glob('./pages/**/*.vue'),
        ).then((page) => {
            page.default.layout = null;

            return page;
        }),
    setup({ el, App, props, plugin }) {
        currentUserId = props.auth?.user?.id ?? null;

        createApp({
            render: () => [
                h(App, props),
                h(ToastContainer),
                h(Sonner, {
                    position: 'top-right',
                    richColors: true,
                    closeButton: true,
                }),
            ],
        })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#c9a227',
    },
});

router.on('success', (event) => {
    const nextUserId = event.detail?.page?.props?.auth?.user?.id ?? null;

    // La primera visita Inertia tras una carga completa todavía no tenía al
    // usuario en memoria: inicializar acá evita descartar notas y favoritos
    // que el usuario acaba de guardar en la página anterior.
    if (currentUserId === null || nextUserId === currentUserId) {
        currentUserId = nextUserId;

        return;
    }

    currentUserId = nextUserId;
    resetNotes();
    resetFavorites();
    router.flushAll();
});

initializeTheme();
initializeFlashToast();
