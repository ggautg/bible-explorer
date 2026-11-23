import { createInertiaApp } from "@inertiajs/vue3";
import { createApp, h } from "vue";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { initializeTheme } from "@/composables/useAppearance";
import { initializeFlashToast } from "@/lib/flashToast";
import Sonner from "@/components/ui/sonner/Sonner.vue";

const appName = import.meta.env.VITE_APP_NAME || "Explorador Bíblico";

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            "./pages/" + name + ".vue",
            import.meta.glob("./pages/**/*.vue"),
        ).then((page) => {
            page.default.layout = null;
            return page;
        }),
    setup({ el, App, props, plugin }) {
        createApp({
            render: () => [h(App, props), h(Sonner, { position: "top-right", richColors: true, closeButton: true })],
        })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: "#c9a227",
    },
});

initializeTheme();
initializeFlashToast();
