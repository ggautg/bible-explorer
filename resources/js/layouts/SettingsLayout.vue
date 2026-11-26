<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';

/**
 * Layout general para las páginas de configuración de la cuenta.
 * Usa el mismo contenedor visual que el perfil para mantener coherencia.
 */
defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage();
const currentUrl = computed(() => page.url);

const navItems = [
    { title: 'Perfil', href: '/settings/profile' },
    { title: 'Seguridad', href: '/settings/security' },
    { title: 'Apariencia', href: '/settings/appearance' },
];

function isActive(href: string): boolean {
    return currentUrl.value.startsWith(href);
}
</script>

<template>
    <Head :title="title ?? 'Configuración'" />

    <BiblePageShell
        back-href="/comunidad"
        back-label="← Comunidad"
        max-width-class="max-w-7xl"
    >
        <template #header>
            <h1
                class="font-bible-display text-3xl font-semibold text-bible-accent-bright"
            >
                {{ title ?? 'Configuración' }}
            </h1>
            <p v-if="description" class="text-sm text-bible-muted">
                {{ description }}
            </p>
            <p v-else class="text-sm text-bible-muted">
                Administrá tu perfil, seguridad y apariencia.
            </p>
        </template>

        <div class="space-y-6">
            <nav
                class="flex flex-wrap gap-2 rounded-2xl border border-bible-border bg-bible-panel/50 p-2"
                aria-label="Secciones de configuración"
            >
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="rounded-xl px-4 py-2.5 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                    :class="
                        isActive(item.href)
                            ? 'bg-bible-accent text-bible-bg'
                            : 'text-bible-muted hover:bg-bible-bg/50 hover:text-bible-accent-bright'
                    "
                >
                    {{ item.title }}
                </Link>
                <Link
                    href="/devocionario"
                    class="rounded-xl px-4 py-2.5 text-sm font-medium text-bible-muted transition-colors hover:bg-bible-bg/50 hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                >
                    Devocionario
                </Link>
            </nav>

            <div
                class="rounded-2xl border border-bible-border bg-bible-panel/50 p-5 md:p-7"
            >
                <slot />
            </div>
        </div>
    </BiblePageShell>
</template>
