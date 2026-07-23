<script setup>
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>

    <Head title="Estrella no encontrada" />
    <div class="bg-bible-bg min-h-screen text-bible-text font-bible-body flex items-center justify-center p-6">
        <div class="text-center max-w-md">
            <svg width="140" height="100" viewBox="0 0 140 100" class="mx-auto mb-8 opacity-40">
                <g stroke="#8A7B54" stroke-opacity="0.4" stroke-width="1" stroke-dasharray="3,4">
                    <line x1="70" y1="50" x2="25" y2="20" />
                    <line x1="70" y1="50" x2="115" y2="18" />
                    <line x1="70" y1="50" x2="40" y2="82" />
                </g>
                <circle cx="70" cy="50" r="5" fill="#6B6552" />
                <circle cx="25" cy="20" r="2.5" fill="#6B6552" />
                <circle cx="115" cy="18" r="2.5" fill="#6B6552" />
                <circle cx="40" cy="82" r="2.5" fill="#6B6552" />
                <line x1="55" y1="35" x2="85" y2="65" stroke="#B0553F" stroke-width="1.5" stroke-opacity="0.6" />
                <line x1="85" y1="35" x2="55" y2="65" stroke="#B0553F" stroke-width="1.5" stroke-opacity="0.6" />
            </svg>

            <h1 class="font-bible-display font-semibold text-2xl text-bible-accent-bright mb-3">
                Esa estrella no existe
            </h1>
            <p class="text-bible-muted mb-8 leading-relaxed">
                La referencia que buscás no está en el mapa — puede que el capítulo o el versículo
                no existan, o que la dirección tenga un error de tipeo.
            </p>

            <div class="flex gap-3 justify-center flex-wrap">
                <Link href="/leer"
                    class="bg-bible-accent text-bible-bg px-5 py-2.5 rounded-md text-sm hover:bg-bible-accent-bright transition-colors p-3">
                    Ir a Leer
                </Link>
                <Link href="/bible/GEN/1/1"
                    class="border border-bible-border text-bible-text px-5 py-2.5 p-3 rounded-md text-sm hover:border-bible-accent hover:text-bible-accent-bright transition-colors">
                    Ver una constelación
                </Link>
            </div>
        </div>
    </div>
</template>