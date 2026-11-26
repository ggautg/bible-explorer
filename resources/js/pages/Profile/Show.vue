<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    BookMarked,
    BookOpen,
    Church,
    MessageCircle,
    UserMinus,
    UserPlus,
} from '@lucide/vue';
import { computed } from 'vue';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';
import ProfileGoalsSection from '@/components/Profile/ProfileGoalsSection.vue';
import PostCard from '@/components/Social/PostCard.vue';
import PostComposer from '@/components/Social/PostComposer.vue';

/**
 * Perfil público de usuario con línea de tiempo tipo red social.
 */
const props = defineProps({
    /** Datos del perfil. */
    profile: { type: Object, required: true },
    /** Pestaña activa: posts | respuestas | me-gusta */
    tab: { type: String, default: 'posts' },
    /** Publicaciones para la pestaña activa. */
    posts: { type: Array, default: () => [] },
    /** Metas personales del perfil (opcional). */
    profileGoals: { type: Object, default: null },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user ?? null);

const tabs = [
    { key: 'posts', label: 'Posts' },
    { key: 'respuestas', label: 'Respuestas' },
    { key: 'me-gusta', label: 'Me gusta' },
];
const visibleTabs = computed(() =>
    props.profile.isOwn ? tabs : tabs.filter((t) => t.key !== 'me-gusta'),
);

function authorInitial(name) {
    return (name || '?').trim().charAt(0).toUpperCase();
}

function toggleFollow() {
    router.post(
        `/usuarios/${props.profile.slug}/seguir`,
        {},
        { preserveScroll: true },
    );
}

const emptyMessage = computed(() => {
    if (props.tab === 'respuestas') {
        return 'Todavía no hay respuestas.';
    }

    if (props.tab === 'me-gusta') {
        return 'Todavía no hay publicaciones con me gusta.';
    }

    return 'Todavía no hay publicaciones.';
});
</script>

<template>
    <Head :title="`Perfil de ${profile.name}`" />

    <BiblePageShell
        back-href="/comunidad"
        back-label="← Comunidad"
        max-width-class="max-w-7xl"
    >
        <div class="space-y-6 md:space-y-8">
            <!-- Cabecera del perfil -->
            <section
                class="overflow-hidden rounded-3xl border border-bible-border bg-gradient-to-br from-bible-panel/90 via-bible-panel/75 to-bible-bg/60 shadow-sm"
            >
                <div class="p-6 md:p-8 lg:p-10">
                    <div
                        class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
                    >
                        <div
                            class="flex flex-col gap-5 sm:flex-row sm:items-center"
                        >
                            <div class="shrink-0">
                                <img
                                    v-if="profile.avatar"
                                    :src="profile.avatar"
                                    :alt="`Foto de ${profile.name}`"
                                    class="h-24 w-24 rounded-3xl border border-bible-border object-cover shadow-md ring-1 ring-bible-accent/20 md:h-28 md:w-28"
                                />
                                <div
                                    v-else
                                    class="flex h-24 w-24 items-center justify-center rounded-3xl border border-bible-border bg-bible-panel text-3xl font-semibold text-bible-accent-bright md:h-28 md:w-28"
                                    aria-hidden="true"
                                >
                                    {{ authorInitial(profile.name) }}
                                </div>
                            </div>

                            <div class="min-w-0">
                                <p
                                    class="mb-1 text-xs font-medium tracking-[0.2em] text-bible-accent uppercase"
                                >
                                    Perfil
                                </p>
                                <h1
                                    class="font-bible-display text-4xl leading-tight font-semibold text-bible-accent-bright md:text-5xl"
                                >
                                    {{ profile.name }}
                                </h1>
                                <p
                                    v-if="profile.church"
                                    class="mt-2 flex items-center gap-2 text-sm font-medium text-bible-text/90 md:text-base"
                                >
                                    <Church
                                        class="h-4 w-4 text-bible-accent-bright"
                                        aria-hidden="true"
                                    />
                                    <span>{{ profile.church }}</span>
                                </p>
                                <div
                                    class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-bible-muted"
                                >
                                    <span
                                        ><span
                                            class="font-semibold text-bible-text"
                                            >{{ profile.postsCount ?? 0 }}</span
                                        >
                                        posts</span
                                    >
                                    <span
                                        ><span
                                            class="font-semibold text-bible-text"
                                            >{{
                                                profile.followersCount ?? 0
                                            }}</span
                                        >
                                        seguidores</span
                                    >
                                    <span
                                        ><span
                                            class="font-semibold text-bible-text"
                                            >{{
                                                profile.followingCount ?? 0
                                            }}</span
                                        >
                                        siguiendo</span
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 sm:justify-end">
                            <button
                                v-if="currentUser && !profile.isOwn"
                                type="button"
                                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                                :class="
                                    profile.isFollowing
                                        ? 'border border-bible-border bg-bible-bg/40 text-bible-text hover:border-bible-error/50 hover:text-bible-error'
                                        : 'bg-bible-accent text-bible-bg hover:bg-bible-accent-bright'
                                "
                                :aria-pressed="Boolean(profile.isFollowing)"
                                @click="toggleFollow"
                            >
                                <component
                                    :is="
                                        profile.isFollowing
                                            ? UserMinus
                                            : UserPlus
                                    "
                                    class="h-4 w-4"
                                    aria-hidden="true"
                                />
                                {{
                                    profile.isFollowing
                                        ? 'Dejar de seguir'
                                        : 'Seguir'
                                }}
                            </button>
                            <Link
                                href="/devocionario"
                                class="inline-flex items-center gap-2 rounded-lg border border-bible-accent/40 bg-bible-accent/10 px-4 py-2 text-sm font-medium text-bible-accent-bright transition-colors hover:bg-bible-accent/20"
                            >
                                <BookMarked
                                    class="h-4 w-4"
                                    aria-hidden="true"
                                />
                                Devocionario
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Pestañas de perfil -->
                <nav
                    class="flex border-t border-bible-border/70"
                    aria-label="Secciones del perfil"
                >
                    <Link
                        v-for="t in visibleTabs"
                        :key="t.key"
                        :href="`/perfil/${profile.slug}?tab=${t.key}`"
                        preserve-scroll
                        class="flex-1 px-4 py-3.5 text-center text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none focus-visible:ring-inset"
                        :class="
                            tab === t.key
                                ? 'border-b-2 border-bible-accent bg-bible-bg/20 text-bible-accent-bright'
                                : 'text-bible-muted hover:bg-bible-bg/30 hover:text-bible-text'
                        "
                    >
                        {{ t.label }}
                    </Link>
                </nav>
            </section>

            <div
                class="grid grid-cols-1 gap-8 xl:grid-cols-[minmax(0,1.65fr)_360px] xl:items-start"
            >
                <main class="space-y-5">
                    <ProfileGoalsSection
                        v-if="profileGoals && tab === 'posts'"
                        :profile-name="profile.name"
                        :goals="profileGoals"
                    />

                    <PostComposer
                        v-if="profile.isOwn"
                        :placeholder="`¿Qué estás meditando, ${profile.name.split(' ')[0]}?`"
                        submit-label="Publicar"
                    />

                    <div v-if="posts.length" class="space-y-4">
                        <PostCard
                            v-for="post in posts"
                            :key="post.id"
                            :post="post"
                        />
                    </div>

                    <div
                        v-else
                        class="rounded-3xl border border-dashed border-bible-border/70 bg-bible-panel/40 px-6 py-14 text-center"
                    >
                        <MessageCircle
                            class="mx-auto mb-4 h-10 w-10 text-bible-muted/70"
                            aria-hidden="true"
                        />
                        <p class="text-lg font-medium text-bible-text">
                            {{ emptyMessage }}
                        </p>
                        <p class="mt-1 text-sm text-bible-muted">
                            {{
                                tab === 'posts' && profile.isOwn
                                    ? 'Compartí tu primera reflexión con la comunidad.'
                                    : 'Cuando haya actividad, va a aparecer acá.'
                            }}
                        </p>
                    </div>
                </main>

                <aside class="space-y-5 xl:sticky xl:top-24 xl:self-start">
                    <section
                        class="rounded-3xl border border-bible-border bg-bible-panel/80 p-6 shadow-sm"
                    >
                        <h2
                            class="mb-4 text-sm font-semibold tracking-wide text-bible-accent uppercase"
                        >
                            Actividad
                        </h2>
                        <dl class="space-y-4 text-sm">
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <dt class="text-bible-muted">Publicaciones</dt>
                                <dd
                                    class="text-base font-semibold text-bible-text"
                                >
                                    {{ profile.postsCount ?? 0 }}
                                </dd>
                            </div>
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <dt class="text-bible-muted">Seguidores</dt>
                                <dd
                                    class="text-base font-semibold text-bible-text"
                                >
                                    {{ profile.followersCount ?? 0 }}
                                </dd>
                            </div>
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <dt class="text-bible-muted">Siguiendo</dt>
                                <dd
                                    class="text-base font-semibold text-bible-text"
                                >
                                    {{ profile.followingCount ?? 0 }}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section
                        class="rounded-3xl border border-bible-border bg-bible-panel/60 p-6"
                    >
                        <h2
                            class="mb-2 text-sm font-semibold tracking-wide text-bible-accent uppercase"
                        >
                            Explorar la Palabra
                        </h2>
                        <p
                            class="text-sm leading-relaxed text-bible-muted md:text-[15px]"
                        >
                            Leé, buscá versículos y meditá en tu devocionario
                            diario.
                        </p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <Link
                                href="/leer"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-bible-border px-3.5 py-2 text-sm font-medium text-bible-text hover:border-bible-accent/50 hover:text-bible-accent-bright"
                            >
                                <BookOpen class="h-4 w-4" aria-hidden="true" />
                                Leer
                            </Link>
                            <Link
                                href="/buscar"
                                class="inline-flex items-center rounded-xl border border-bible-border px-3.5 py-2 text-sm font-medium text-bible-text hover:border-bible-accent/50 hover:text-bible-accent-bright"
                            >
                                Buscar
                            </Link>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </BiblePageShell>
</template>
