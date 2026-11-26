<script setup>
import { Link } from '@inertiajs/vue3';
import { Menu, X } from '@lucide/vue';
import { ref } from 'vue';
import NavHeader from '@/components/Bible/NavHeader.vue';

/**
 * Contenedor de página con navegación lateral vertical.
 *
 * - Desktop: menú lateral fijo (sticky) con opciones apiladas.
 * - Mobile: menú colapsable para no ocupar toda la pantalla.
 * Mantiene coherencia visual, ancho máximo y cabecera de contenido.
 */
defineProps({
    /** Enlace de retroceso opcional para la cabecera del contenido. */
    backHref: { type: String, default: null },
    /** Texto visible del enlace de retroceso. */
    backLabel: { type: String, default: '← Volver' },
    /** Ancho máximo del contenido (columna principal). */
    maxWidthClass: { type: String, default: 'max-w-5xl' },
    /** Oculta la navegación lateral cuando no es necesaria. */
    hideNav: { type: Boolean, default: false },
});

/** Estado del menú móvil. */
const mobileOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-bible-bg font-bible-body text-bible-text">
        <div class="mx-auto w-full max-w-[1400px] px-4 py-4 md:px-6 md:py-8">
            <!-- Barra superior móvil -->
            <div
                v-if="!hideNav"
                class="mb-4 flex items-center justify-between rounded-2xl border border-bible-border bg-bible-panel/50 px-4 py-3 lg:hidden"
            >
                <div>
                    <p
                        class="font-bible-display text-lg leading-tight font-semibold text-bible-accent-bright"
                    >
                        Explorador Bíblico
                    </p>
                    <p class="text-xs text-bible-muted">
                        Comunidad y herramientas
                    </p>
                </div>
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-bible-border text-bible-muted transition-colors hover:bg-bible-bg/50 hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                    :aria-expanded="mobileOpen ? 'true' : 'false'"
                    aria-controls="mobile-nav"
                    aria-label="Abrir menú de navegación"
                    @click="mobileOpen = !mobileOpen"
                >
                    <component
                        :is="mobileOpen ? X : Menu"
                        class="h-5 w-5"
                        aria-hidden="true"
                    />
                </button>
            </div>

            <div
                class="flex flex-col gap-6 lg:flex-row lg:items-start lg:gap-8"
            >
                <aside
                    v-if="!hideNav"
                    id="mobile-nav"
                    :class="[
                        'w-full shrink-0 lg:sticky lg:top-8 lg:block lg:w-64 lg:self-start',
                        mobileOpen ? 'block' : 'hidden',
                    ]"
                    aria-label="Menú lateral"
                >
                    <div
                        class="rounded-2xl border border-bible-border bg-bible-panel/50 p-3 md:p-4"
                    >
                        <div class="mb-4 hidden px-3 lg:block">
                            <p
                                class="font-bible-display text-lg leading-tight font-semibold text-bible-accent-bright"
                            >
                                Explorador Bíblico
                            </p>
                            <p class="text-xs text-bible-muted">
                                Comunidad y herramientas
                            </p>
                        </div>
                        <NavHeader @navigate="mobileOpen = false" />
                    </div>
                </aside>

                <main class="min-w-0 flex-1">
                    <div :class="['mx-auto w-full', maxWidthClass]">
                        <div
                            v-if="backHref || $slots.header"
                            class="mb-6 space-y-2 border-b border-bible-border/70 pb-5"
                        >
                            <Link
                                v-if="backHref"
                                :href="backHref"
                                class="inline-flex items-center rounded text-sm text-bible-muted transition-colors hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                            >
                                {{ backLabel }}
                            </Link>
                            <slot name="header" />
                        </div>
                        <slot />
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>
