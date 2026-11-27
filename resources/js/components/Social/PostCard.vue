<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { Flag, Heart, MessageCircle, Repeat2, Share } from '@lucide/vue';
import { computed, onUnmounted, ref } from 'vue';

const props = defineProps({
    /** Publicación serializada desde el backend. */
    post: { type: Object, required: true },
    /** Muestra el post original embebido si es rebibleo. */
    showOriginal: { type: Boolean, default: true },
    /** Modo compacto. */
    compact: { type: Boolean, default: false },
});

const page = usePage();
const viewer = computed(() => page.props.auth?.user ?? null);
const copied = ref(false);
let copiedTimer = null;

onUnmounted(() => clearTimeout(copiedTimer));

/** Etiquetas legibles de las opciones de comunidad. */
const KIND_LABELS = {
    versiculo: 'Versículo',
    peticion_oracion: 'Pedido de oración',
    testimonio: 'Testimonio',
    agradecimiento: 'Agradecimiento',
    rebibleada: 'Rebibleada',
};

const isRebible = computed(() => props.post.isRebible && props.post.original);
const displayPost = computed(() =>
    isRebible.value ? props.post.original : props.post,
);
const kindLabel = computed(() => {
    const kind = isRebible.value ? props.post.kind : displayPost.value.kind;

    return KIND_LABELS[kind] ?? null;
});
const actor = computed(() => props.post.author);

function initial(name) {
    return (name || '?').trim().charAt(0).toUpperCase();
}

function toggleLike(target) {
    if (!viewer.value) {
        return;
    }

    router.post(`/posts/${target.id}/like`, {}, { preserveScroll: true });
}

function toggleRebible(target) {
    if (!viewer.value) {
        return;
    }

    router.post(`/posts/${target.id}/rebiblear`, {}, { preserveScroll: true });
}

async function sharePost(target) {
    const url = `${window.location.origin}/posts/${target.id}`;

    try {
        await navigator.clipboard.writeText(url);
        copied.value = true;
        clearTimeout(copiedTimer);
        copiedTimer = setTimeout(() => {
            copied.value = false;
        }, 1800);
    } catch {
        // fallback silencioso
    }
}

function reportPost(target) {
    const reason = window.prompt('¿Por qué reportás esta publicación?');

    if (!reason || !reason.trim()) {
        return;
    }

    router.post(
        `/posts/${target.id}/reportar`,
        { reason: reason.trim() },
        { preserveScroll: true },
    );
}

function verseHref(verse) {
    if (!verse?.book || !verse?.chapter || !verse?.verse) {
        return null;
    }

    return `/bible/${verse.book}/${verse.chapter}/${verse.verse}`;
}
</script>

<template>
    <article
        class="rounded-2xl border border-bible-border bg-bible-panel/60 p-4 transition-colors hover:bg-bible-panel/70 md:p-5"
    >
        <!-- Cabecera de rebibleo -->
        <div
            v-if="isRebible"
            class="mb-2 flex items-center gap-2 pl-12 text-xs font-medium text-bible-muted"
        >
            <Repeat2 class="h-3.5 w-3.5" aria-hidden="true" />
            <Link
                v-if="actor?.slug"
                :href="`/perfil/${actor.slug}`"
                class="hover:text-bible-accent-bright hover:underline"
                >{{ actor.name }}</Link
            >
            <span v-else>{{ actor?.name || 'Alguien' }}</span>
            <span>rebibleó</span>
            <span v-if="post.time" class="text-bible-muted"
                >· {{ post.time }}</span
            >
        </div>

        <div class="flex items-start gap-3">
            <component
                :is="displayPost.author?.slug ? Link : 'span'"
                :href="
                    displayPost.author?.slug
                        ? `/perfil/${displayPost.author.slug}`
                        : undefined
                "
                class="shrink-0"
            >
                <img
                    v-if="displayPost.author?.avatar"
                    :src="displayPost.author.avatar"
                    :alt="`Foto de ${displayPost.author?.name || 'Usuario'}`"
                    class="h-11 w-11 rounded-xl border border-bible-border object-cover"
                    loading="lazy"
                />
                <div
                    v-else
                    class="flex h-11 w-11 items-center justify-center rounded-xl border border-bible-border bg-bible-bg text-sm font-semibold text-bible-accent-bright"
                    aria-hidden="true"
                >
                    {{ initial(displayPost.author?.name) }}
                </div>
            </component>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <component
                        :is="displayPost.author?.slug ? Link : 'span'"
                        :href="
                            displayPost.author?.slug
                                ? `/perfil/${displayPost.author.slug}`
                                : undefined
                        "
                        class="font-semibold text-bible-text hover:text-bible-accent-bright hover:underline"
                    >
                        {{ displayPost.author?.name || 'Usuario' }}
                    </component>
                    <span
                        v-if="displayPost.author?.church"
                        class="text-xs text-bible-muted"
                        >· {{ displayPost.author.church }}</span
                    >
                    <span
                        v-if="displayPost.time"
                        class="text-xs text-bible-muted"
                        >· {{ displayPost.time }}</span
                    >
                </div>

                <span
                    v-if="kindLabel"
                    class="mt-1.5 inline-flex items-center rounded-full border border-bible-accent/30 bg-bible-accent/10 px-2.5 py-1 text-[11px] font-medium text-bible-accent-bright"
                >
                    {{ kindLabel }}
                </span>

                <div
                    v-if="displayPost.body"
                    class="mt-1.5 text-[15px] leading-relaxed whitespace-pre-line text-bible-text/95"
                >
                    {{ displayPost.body }}
                </div>
                <p
                    v-else-if="!isRebible"
                    class="mt-1.5 text-sm text-bible-muted italic"
                >
                    Publicación sin texto.
                </p>

                <div
                    v-if="displayPost.verse"
                    class="mt-3 rounded-xl border border-bible-accent/25 bg-bible-bg/40 px-3.5 py-3"
                >
                    <Link
                        v-if="verseHref(displayPost.verse)"
                        :href="verseHref(displayPost.verse)"
                        class="text-xs font-medium tracking-wide text-bible-accent uppercase hover:text-bible-accent-bright"
                    >
                        {{ displayPost.verse.reference }}
                    </Link>
                    <p
                        v-else
                        class="text-xs font-medium tracking-wide text-bible-accent uppercase"
                    >
                        {{ displayPost.verse.reference }}
                    </p>
                    <p
                        v-if="displayPost.verse.text"
                        class="mt-1 font-bible-display text-sm leading-relaxed text-bible-text"
                    >
                        “{{ displayPost.verse.text }}”
                    </p>
                </div>

                <!-- Acciones -->
                <div
                    class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-bible-muted"
                >
                    <Link
                        :href="`/posts/${displayPost.id}`"
                        class="group inline-flex items-center gap-1.5 rounded transition-colors hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                        title="Responder"
                    >
                        <MessageCircle class="h-4 w-4" aria-hidden="true" />
                        <span class="tabular-nums">{{
                            displayPost.counts?.replies ?? 0
                        }}</span>
                        <span class="sr-only">Respuestas</span>
                    </Link>

                    <button
                        type="button"
                        class="group inline-flex items-center gap-1.5 rounded transition-colors focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none disabled:opacity-50"
                        :class="
                            displayPost.viewer?.rebibled
                                ? 'text-emerald-400'
                                : 'hover:text-emerald-400'
                        "
                        :disabled="!viewer"
                        :aria-pressed="Boolean(displayPost.viewer?.rebibled)"
                        :title="
                            viewer
                                ? 'Rebiblear'
                                : 'Iniciá sesión para rebiblear'
                        "
                        @click="toggleRebible(displayPost)"
                    >
                        <Repeat2 class="h-4 w-4" aria-hidden="true" />
                        <span class="tabular-nums">{{
                            displayPost.counts?.rebibles ?? 0
                        }}</span>
                        <span class="sr-only">Rebibleos</span>
                    </button>

                    <button
                        type="button"
                        class="group inline-flex items-center gap-1.5 rounded transition-colors focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none disabled:opacity-50"
                        :class="
                            displayPost.viewer?.liked
                                ? 'text-bible-error'
                                : 'hover:text-bible-error'
                        "
                        :disabled="!viewer"
                        :aria-pressed="Boolean(displayPost.viewer?.liked)"
                        :title="
                            viewer
                                ? 'Me gusta'
                                : 'Iniciá sesión para dar me gusta'
                        "
                        @click="toggleLike(displayPost)"
                    >
                        <Heart
                            class="h-4 w-4"
                            :class="
                                displayPost.viewer?.liked ? 'fill-current' : ''
                            "
                            aria-hidden="true"
                        />
                        <span class="tabular-nums">{{
                            displayPost.counts?.likes ?? 0
                        }}</span>
                        <span class="sr-only">Me gusta</span>
                    </button>

                    <button
                        type="button"
                        class="group inline-flex items-center gap-1.5 rounded transition-colors hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                        :title="copied ? 'Enlace copiado' : 'Compartir'"
                        @click="sharePost(displayPost)"
                    >
                        <Share class="h-4 w-4" aria-hidden="true" />
                        <span class="text-xs">{{
                            copied ? 'Copiado' : 'Compartir'
                        }}</span>
                    </button>

                    <button
                        v-if="viewer && displayPost.author?.id !== viewer.id"
                        type="button"
                        class="group inline-flex items-center gap-1.5 rounded transition-colors hover:text-bible-error focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                        :aria-label="`Reportar publicación de ${displayPost.author?.name || 'Usuario'}`"
                        title="Reportar publicación"
                        @click="reportPost(displayPost)"
                    >
                        <Flag class="h-4 w-4" aria-hidden="true" />
                        <span class="text-xs">Reportar</span>
                    </button>
                </div>
            </div>
        </div>
    </article>
</template>
