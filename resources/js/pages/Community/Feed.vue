<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { BookMarked, Users } from '@lucide/vue';
import { computed } from 'vue';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';
import PostCard from '@/components/Social/PostCard.vue';
import PostComposer from '@/components/Social/PostComposer.vue';

/**
 * Inicio/Comunidad estilo red social.
 * Muestra el compositor, pestañas de feed y publicaciones.
 */
const props = defineProps({
    /** Pestaña activa: para_ti | siguiendo | mios */
    tab: { type: String, default: 'para_ti' },
    /** Personas para descubrir/seguir. */
    people: { type: Array, default: () => [] },
    /** Publicaciones del feed. */
    posts: { type: Array, default: () => [] },
});

const tabs = [
    {
        key: 'para_ti',
        label: 'Para ti',
        description: 'Publicaciones de toda la comunidad',
    },
    {
        key: 'siguiendo',
        label: 'Siguiendo',
        description: 'Publicaciones de las personas que seguís',
    },
    {
        key: 'mios',
        label: 'Mis posts',
        description: 'Tus publicaciones y rebibleos',
    },
];

const activeTab = computed(
    () => tabs.find((t) => t.key === props.tab) || tabs[0],
);
const followingCount = computed(
    () => props.people.filter((p) => p.following).length,
);

function initial(name) {
    return (name || '?').trim().charAt(0).toUpperCase();
}

function toggleFollow(person) {
    if (!person?.slug) {
        return;
    }

    router.post(
        `/usuarios/${person.slug}/seguir`,
        {},
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Comunidad" />

    <BiblePageShell max-width-class="max-w-7xl">
        <template #header>
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <h1
                        class="font-bible-display text-3xl font-semibold text-bible-accent-bright"
                    >
                        Comunidad
                    </h1>
                    <p class="text-sm text-bible-muted">
                        Compartí reflexiones, seguí a otros y conversá alrededor
                        de la Palabra.
                    </p>
                </div>
                <Link
                    href="/devocionario"
                    class="inline-flex w-fit items-center gap-2 rounded-lg border border-bible-accent/40 bg-bible-accent/10 px-4 py-2 text-sm font-medium text-bible-accent-bright transition-colors hover:bg-bible-accent/20 focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                >
                    <BookMarked class="h-4 w-4" aria-hidden="true" />
                    Ir a mi devocionario
                </Link>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <section class="space-y-4" aria-label="Feed de comunidad">
                <PostComposer
                    placeholder="¿Qué estás meditando hoy?"
                    submit-label="Publicar"
                />

                <!-- Pestañas -->
                <div
                    class="rounded-2xl border border-bible-border bg-bible-panel/50 p-2"
                >
                    <nav
                        class="flex flex-wrap gap-2"
                        aria-label="Pestañas del feed"
                    >
                        <Link
                            v-for="t in tabs"
                            :key="t.key"
                            :href="`/comunidad?tab=${t.key}`"
                            preserve-scroll
                            class="rounded-xl px-4 py-2.5 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                            :class="
                                tab === t.key
                                    ? 'bg-bible-accent text-bible-bg'
                                    : 'text-bible-muted hover:bg-bible-bg/50 hover:text-bible-accent-bright'
                            "
                        >
                            {{ t.label }}
                        </Link>
                    </nav>
                    <p class="px-2 pt-2 pb-1 text-xs text-bible-muted">
                        {{ activeTab.description }}
                    </p>
                </div>

                <!-- Publicaciones -->
                <div v-if="posts.length" class="space-y-4">
                    <PostCard
                        v-for="post in posts"
                        :key="post.id"
                        :post="post"
                    />
                </div>

                <div
                    v-else
                    class="rounded-2xl border border-dashed border-bible-border/70 bg-bible-panel/40 px-6 py-12 text-center"
                >
                    <Users
                        class="mx-auto mb-3 h-10 w-10 text-bible-muted/70"
                        aria-hidden="true"
                    />
                    <p class="text-base font-medium text-bible-text">
                        {{
                            tab === 'mios'
                                ? 'Todavía no publicaste nada'
                                : tab === 'siguiendo'
                                  ? 'Tu feed está tranquilo'
                                  : 'Aún no hay publicaciones'
                        }}
                    </p>
                    <p class="mt-1 text-sm text-bible-muted">
                        {{
                            tab === 'siguiendo'
                                ? 'Seguí a otras personas para ver su actividad acá.'
                                : 'Sé la primera persona en compartir una reflexión.'
                        }}
                    </p>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        <Link
                            href="/leer"
                            class="rounded-lg border border-bible-border px-3.5 py-2 text-sm text-bible-text hover:border-bible-accent/50 hover:text-bible-accent-bright"
                            >Leer la Biblia</Link
                        >
                        <Link
                            href="/devocionario"
                            class="rounded-lg border border-bible-accent/40 bg-bible-accent/10 px-3.5 py-2 text-sm text-bible-accent-bright hover:bg-bible-accent/20"
                            >Mi devocionario</Link
                        >
                    </div>
                </div>
            </section>

            <aside class="space-y-4 lg:sticky lg:top-6 lg:self-start">
                <div
                    class="rounded-2xl border border-bible-border bg-bible-panel/60 p-5"
                >
                    <h2
                        class="text-sm font-semibold tracking-wide text-bible-accent uppercase"
                    >
                        Personas
                    </h2>
                    <p class="mt-1 text-sm text-bible-muted">
                        Seguís a
                        <span class="font-medium text-bible-text">{{
                            followingCount
                        }}</span>
                        {{ followingCount === 1 ? 'persona' : 'personas' }}.
                    </p>

                    <ul class="mt-4 space-y-3">
                        <li
                            v-for="person in people"
                            :key="person.id || person.slug || person.name"
                        >
                            <div
                                class="flex items-center gap-3 rounded-xl border border-bible-border/60 bg-bible-bg/30 p-3"
                            >
                                <Link
                                    :href="
                                        person.slug
                                            ? `/perfil/${person.slug}`
                                            : '#'
                                    "
                                    class="shrink-0"
                                >
                                    <img
                                        v-if="person.avatar"
                                        :src="person.avatar"
                                        :alt="`Foto de ${person.name}`"
                                        class="h-10 w-10 rounded-xl border border-bible-border object-cover"
                                        loading="lazy"
                                    />
                                    <div
                                        v-else
                                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-bible-border bg-bible-panel text-sm font-semibold text-bible-accent-bright"
                                        aria-hidden="true"
                                    >
                                        {{ initial(person.name) }}
                                    </div>
                                </Link>
                                <div class="min-w-0 flex-1">
                                    <Link
                                        :href="
                                            person.slug
                                                ? `/perfil/${person.slug}`
                                                : '#'
                                        "
                                        class="block truncate text-sm font-medium text-bible-text hover:text-bible-accent-bright"
                                        >{{ person.name }}</Link
                                    >
                                    <p
                                        class="truncate text-xs text-bible-muted"
                                    >
                                        {{ person.church || 'Comunidad' }}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    class="shrink-0 rounded-full px-3 py-1 text-[11px] font-medium transition-colors focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                                    :class="
                                        person.following
                                            ? 'bg-bible-accent/15 text-bible-accent-bright hover:bg-bible-accent/25'
                                            : 'border border-bible-border text-bible-muted hover:border-bible-accent/50 hover:text-bible-accent-bright'
                                    "
                                    :aria-pressed="Boolean(person.following)"
                                    @click="toggleFollow(person)"
                                >
                                    {{
                                        person.following
                                            ? 'Siguiendo'
                                            : 'Seguir'
                                    }}
                                </button>
                            </div>
                        </li>
                        <li
                            v-if="!people.length"
                            class="text-sm text-bible-muted"
                        >
                            No hay otras personas por ahora.
                        </li>
                    </ul>
                </div>

                <div
                    class="rounded-2xl border border-bible-accent/25 bg-bible-panel/50 p-5"
                >
                    <h2 class="text-sm font-semibold text-bible-text">
                        ¿Necesitás hablar?
                    </h2>
                    <p class="mt-1 text-sm text-bible-muted">
                        Podés acercarte a un pastor de tu iglesia para conversar
                        o pedir oración.
                    </p>
                    <button
                        type="button"
                        class="mt-3 w-full rounded-md bg-bible-accent px-4 py-2.5 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                    >
                        Hablar con un pastor de mi iglesia
                    </button>
                    <p class="mt-2 text-xs text-bible-muted-dim">
                        Maquetado de interfaz.
                    </p>
                </div>
            </aside>
        </div>
    </BiblePageShell>
</template>
