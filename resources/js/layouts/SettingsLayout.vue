<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import Heading from '@/components/Heading.vue';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';

/**
 * Diseño para las páginas de configuración de la cuenta.
 * Incluye navegación entre perfil, seguridad y apariencia.
 */
const props = defineProps<{
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

    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <div class="px-4 py-6 md:px-8">
                <Heading
                    :title="title ?? 'Configuración'"
                    :description="description ?? 'Administra tu perfil y la seguridad de tu cuenta.'"
                />

                <div class="flex flex-col gap-8 lg:flex-row lg:gap-12">
                    <aside class="w-full lg:w-52">
                        <nav class="flex flex-row gap-1 overflow-x-auto lg:flex-col" aria-label="Configuración">
                            <Link
                                v-for="item in navItems"
                                :key="item.href"
                                :href="item.href"
                                :class="cn(
                                    'rounded-md px-3 py-2 text-sm font-medium transition-colors whitespace-nowrap',
                                    isActive(item.href)
                                        ? 'bg-accent text-accent-foreground'
                                        : 'text-muted-foreground hover:bg-accent/60 hover:text-foreground',
                                )"
                            >
                                {{ item.title }}
                            </Link>
                        </nav>
                    </aside>

                    <Separator class="lg:hidden" />

                    <div class="flex-1 md:max-w-2xl">
                        <slot />
                    </div>
                </div>
            </div>
        </AppContent>
    </AppShell>
</template>
