<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, BookOpen, LogIn, Share2, Sparkles } from '@lucide/vue';
import { computed } from 'vue';
import StarfieldBackground from '@/components/Bible/StarfieldBackground.vue';

/**
 * Página pública para visualizar un versículo compartido.
 * Funciona como puerta de entrada simple para visitantes no autenticados.
 */
defineProps({
    /** Información del versículo compartido. */
    verse: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
</script>

<template>
    <Head :title="`${verse.bookName} ${verse.chapter}:${verse.verse}`" />

    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-bible-bg p-6 font-bible-body text-bible-text"
    >
        <StarfieldBackground />
        <!-- Brillo suave para destacar la tarjeta central. -->
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(201,162,39,0.08),transparent_55%)]"
            aria-hidden="true"
        />

        <main
            class="relative w-full max-w-2xl rounded-3xl border border-bible-border bg-bible-panel/85 p-8 text-center shadow-[0_24px_80px_-30px_rgba(0,0,0,0.6)] backdrop-blur-sm md:p-10"
        >
            <div
                class="mx-auto mb-5 flex h-12 w-12 items-center justify-center rounded-2xl border border-bible-accent/20 bg-bible-bg/40 text-bible-accent"
            >
                <Share2 class="h-6 w-6" aria-hidden="true" />
            </div>
            <p
                class="mb-4 text-xs font-medium tracking-[0.2em] text-bible-accent uppercase"
            >
                Versículo compartido
            </p>

            <blockquote>
                <p
                    class="font-bible-display text-2xl leading-relaxed text-bible-text md:text-4xl md:leading-snug"
                >
                    “{{ verse.text }}”
                </p>
            </blockquote>

            <p
                class="mt-5 inline-flex items-center gap-2 rounded-full border border-bible-border bg-bible-bg/30 px-4 py-1.5 text-sm font-medium text-bible-accent-bright"
            >
                <BookOpen
                    class="h-4 w-4 text-bible-accent"
                    aria-hidden="true"
                />
                {{ verse.bookName }} {{ verse.chapter }}:{{ verse.verse }}
            </p>

            <div class="mt-8 flex flex-col items-center gap-3">
                <Link
                    v-if="user"
                    :href="`/bible/${verse.book}/${verse.chapter}/${verse.verse}`"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-bible-accent px-6 py-3 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                >
                    <Sparkles class="h-4 w-4" aria-hidden="true" />
                    Ver sus conexiones
                </Link>

                <template v-else>
                    <Link
                        href="/register"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-bible-accent px-6 py-3 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                    >
                        Creá tu cuenta para explorar
                        <ArrowRight class="h-4 w-4" aria-hidden="true" />
                    </Link>
                    <p
                        class="max-w-sm text-sm leading-relaxed text-bible-muted"
                    >
                        Registrate gratis para leer capítulos completos, guardar
                        favoritos, escribir notas y explorar conexiones.
                    </p>
                    <Link
                        href="/login"
                        class="inline-flex items-center gap-2 rounded text-sm text-bible-muted transition-colors hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                    >
                        <LogIn class="h-4 w-4" aria-hidden="true" />
                        Ya tengo una cuenta
                    </Link>
                </template>
            </div>
        </main>
    </div>
</template>
