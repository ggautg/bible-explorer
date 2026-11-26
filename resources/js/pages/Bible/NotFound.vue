<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, Compass, MapPinOff } from '@lucide/vue';
import StarfieldBackground from '@/components/Bible/StarfieldBackground.vue';

/**
 * Página mostrada cuando una referencia bíblica no existe o es inválida.
 * Ofrece salidas claras para que el usuario retome la navegación.
 */
</script>

<template>
    <Head title="Referencia no encontrada" />

    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-bible-bg p-6 font-bible-body text-bible-text"
    >
        <StarfieldBackground :opacity="0.35" />

        <main
            class="relative w-full max-w-lg rounded-3xl border border-bible-border bg-bible-panel/85 p-8 text-center shadow-lg backdrop-blur-sm"
        >
            <div
                class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl border border-bible-accent/20 bg-bible-bg/40 text-bible-accent"
            >
                <MapPinOff class="h-7 w-7" aria-hidden="true" />
            </div>
            <p
                class="text-xs font-medium tracking-[0.2em] text-bible-accent uppercase"
            >
                Referencia no encontrada
            </p>
            <h1
                class="mt-3 font-bible-display text-3xl font-semibold text-bible-accent-bright"
            >
                Esa estrella no está en el mapa
            </h1>
            <p class="mt-4 text-sm leading-relaxed text-bible-muted">
                La referencia que buscaste no existe o la dirección tiene un
                error. Podés volver a la lectura o explorar una conexión
                conocida.
            </p>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <Link
                    href="/leer"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-bible-accent px-5 py-2.5 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                >
                    <BookOpen class="h-4 w-4" aria-hidden="true" />
                    Ir a leer
                </Link>
                <Link
                    href="/bible/GEN/1/1"
                    class="inline-flex items-center justify-center gap-2 rounded-full border border-bible-border px-5 py-2.5 text-sm font-medium text-bible-text transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                >
                    <Compass class="h-4 w-4" aria-hidden="true" />
                    Ver un ejemplo
                </Link>
            </div>
        </main>
    </div>
</template>
