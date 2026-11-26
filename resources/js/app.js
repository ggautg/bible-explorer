import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import Sonner from '@/components/ui/sonner/Sonner.vue';
import { initializeTheme } from '@/composables/useAppearance';
import { resetFavorites } from '@/composables/useFavorites';
import { resetNotes } from '@/composables/useNotes';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Explorador Bíblico';
let currentUserId = null;

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

    if (nextUserId === currentUserId) {
        return;
    }

    currentUserId = nextUserId;
    resetNotes();
    resetFavorites();
    router.flushAll();
});

initializeTheme();
initializeFlashToast();
