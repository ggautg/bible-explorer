<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

/**
 * Diseño base para las páginas de autenticación.
 * Presenta el contenido en una tarjeta centrada con el tema oscuro de la app.
 */
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <Head :title="title" />

    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 text-foreground md:p-10"
    >
        <div class="w-full max-w-md space-y-6">
            <div class="flex flex-col items-center gap-3 text-center">
                <Link
                    href="/"
                    class="inline-flex items-center gap-2 text-bible-accent-bright hover:opacity-90"
                >
                    <span class="font-bible-display text-xl font-semibold"
                        >Explorador Bíblico</span
                    >
                </Link>
                <div class="space-y-1">
                    <h1 class="text-xl font-semibold tracking-tight">
                        {{ title }}
                    </h1>
                    <p v-if="description" class="text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>
            </div>

            <div
                class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            >
                <slot />
            </div>

            <div
                v-if="$slots.footer"
                class="text-center text-sm text-muted-foreground"
            >
                <slot name="footer" />
            </div>
        </div>
    </div>
</template>
