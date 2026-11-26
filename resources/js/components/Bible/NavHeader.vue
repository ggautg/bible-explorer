<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookMarked,
    BookOpen,
    GitBranch,
    Heart,
    Home,
    LogOut,
    NotebookText,
    Search,
    Settings,
    User,
} from '@lucide/vue';
import { computed } from 'vue';
import { logout } from '@/routes';

/**
 * Menú de navegación principal en formato vertical.
 *
 * Prioriza la experiencia social (inicio y perfil) y mantiene accesibles
 * las herramientas bíblicas como grupo aparte.
 */
const emit = defineEmits(['navigate']);
const page = usePage();
const currentUser = computed(() => page.props.auth?.user ?? null);

/** Enlaces sociales principales. */
const socialItems = computed(() => {
    const items = [{ href: '/comunidad', label: 'Inicio', icon: Home }];

    if (currentUser.value?.slug) {
        items.push({
            href: `/perfil/${currentUser.value.slug}`,
            label: 'Mi perfil',
            icon: User,
        });
    }

    return items;
});

/** Herramientas bíblicas (aparte del foco social). */
const bibleItems = [
    { href: '/leer', label: 'Leer', icon: BookOpen },
    { href: '/buscar', label: 'Buscar', icon: Search },
    { href: '/devocionario', label: 'Devocionario', icon: BookMarked },
    { href: '/notas', label: 'Notas', icon: NotebookText },
    { href: '/favoritos', label: 'Favoritos', icon: Heart },
    { href: '/bible/GEN/1/1', label: 'Grafo', icon: GitBranch },
];

/** URL actual (ruta) para resaltar el apartado activo. */
const currentPath = computed(() => (page.url || '/').split('?')[0]);

/** Determina si un enlace corresponde a la sección actual. */
function isActive(href) {
    if (href === '/bible/GEN/1/1') {
        return currentPath.value.startsWith('/bible');
    }

    if (href === '/comunidad') {
        return (
            currentPath.value === '/comunidad' ||
            currentPath.value === '/inicio' ||
            currentPath.value.startsWith('/posts')
        );
    }

    return (
        currentPath.value === href || currentPath.value.startsWith(`${href}/`)
    );
}

const linkBase =
    'group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent';

function linkClass(href) {
    return isActive(href)
        ? `${linkBase} bg-bible-panel text-bible-accent-bright border border-bible-border font-medium`
        : `${linkBase} text-bible-muted hover:bg-bible-panel/60 hover:text-bible-accent-bright`;
}
</script>

<template>
    <nav class="flex flex-col gap-5" aria-label="Navegación principal">
        <div>
            <p
                class="px-3 pb-1.5 text-[11px] font-semibold tracking-[0.16em] text-bible-muted uppercase"
            >
                Comunidad
            </p>
            <div class="flex flex-col gap-1">
                <Link
                    v-for="item in socialItems"
                    :key="item.href"
                    :href="item.href"
                    :aria-current="isActive(item.href) ? 'page' : undefined"
                    :class="linkClass(item.href)"
                    @click="emit('navigate')"
                >
                    <component
                        :is="item.icon"
                        class="h-4.5 w-4.5 shrink-0"
                        aria-hidden="true"
                    />
                    <span>{{ item.label }}</span>
                </Link>
            </div>
        </div>

        <div>
            <p
                class="px-3 pb-1.5 text-[11px] font-semibold tracking-[0.16em] text-bible-muted uppercase"
            >
                Biblia
            </p>
            <div class="flex flex-col gap-1">
                <Link
                    v-for="item in bibleItems"
                    :key="item.href"
                    :href="item.href"
                    :aria-current="isActive(item.href) ? 'page' : undefined"
                    :class="linkClass(item.href)"
                    @click="emit('navigate')"
                >
                    <component
                        :is="item.icon"
                        class="h-4.5 w-4.5 shrink-0"
                        aria-hidden="true"
                    />
                    <span>{{ item.label }}</span>
                </Link>
            </div>
        </div>

        <div class="border-t border-bible-border/70 pt-3">
            <div class="flex flex-col gap-1">
                <Link
                    href="/settings/appearance"
                    :aria-current="
                        currentPath.startsWith('/settings') ? 'page' : undefined
                    "
                    :class="
                        currentPath.startsWith('/settings')
                            ? `${linkBase} border border-bible-border bg-bible-panel font-medium text-bible-accent-bright`
                            : `${linkBase} text-bible-muted hover:bg-bible-panel/60 hover:text-bible-accent-bright`
                    "
                    @click="emit('navigate')"
                >
                    <Settings class="h-4.5 w-4.5 shrink-0" aria-hidden="true" />
                    <span>Configuración</span>
                </Link>
                <Link
                    :href="logout()"
                    method="post"
                    as="button"
                    :class="`${linkBase} text-bible-muted hover:bg-bible-panel/60 hover:text-bible-error`"
                >
                    <LogOut class="h-4.5 w-4.5 shrink-0" aria-hidden="true" />
                    <span>Cerrar sesión</span>
                </Link>
            </div>
        </div>
    </nav>
</template>
