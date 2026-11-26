<script setup lang="ts">
import { Moon, Sun } from '@lucide/vue';
import { computed } from 'vue';
import { BIBLICAL_THEMES, useAppearance } from '@/composables/useAppearance';

/**
 * Selector de temas bíblicos de la interfaz.
 * Permite elegir entre 3 temas claros y 3 oscuros.
 */
const { theme, setTheme } = useAppearance();

const lightThemes = computed(() =>
    BIBLICAL_THEMES.filter((t) => t.mode === 'light'),
);
const darkThemes = computed(() =>
    BIBLICAL_THEMES.filter((t) => t.mode === 'dark'),
);

const previews: Record<string, string[]> = {
    pergamino: ['#f6efe1', '#e7d7b8', '#8a5a2b'],
    olivo: ['#f2f6ee', '#d8e4c8', '#4f6b3a'],
    jordan: ['#eef6fb', '#cfe6f5', '#2f6f95'],
    getsemani: ['#0b0e1a', '#171c34', '#c9a227'],
    sinai: ['#1a140f', '#2a2118', '#c58a3a'],
    patmos: ['#081427', '#10243f', '#4ea1d3'],
};
</script>

<template>
    <div class="space-y-6">
        <section class="space-y-3">
            <div
                class="flex items-center gap-2 text-sm font-medium text-foreground"
            >
                <Sun class="h-4 w-4" aria-hidden="true" />
                <h3>Temas claros</h3>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <button
                    v-for="item in lightThemes"
                    :key="item.id"
                    type="button"
                    @click="setTheme(item.id)"
                    :aria-pressed="theme === item.id"
                    class="rounded-xl border p-3 text-left transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    :class="
                        theme === item.id
                            ? 'border-primary bg-accent/60'
                            : 'border-border bg-card hover:border-primary/50 hover:bg-accent/30'
                    "
                >
                    <div class="mb-3 flex gap-1.5">
                        <span
                            v-for="(color, i) in previews[item.id]"
                            :key="i"
                            class="h-4 w-4 rounded-full border border-border/60"
                            :style="{ backgroundColor: color }"
                            aria-hidden="true"
                        ></span>
                    </div>
                    <p class="text-sm font-semibold text-foreground">
                        {{ item.label }}
                    </p>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        {{ item.description }}
                    </p>
                </button>
            </div>
        </section>

        <section class="space-y-3">
            <div
                class="flex items-center gap-2 text-sm font-medium text-foreground"
            >
                <Moon class="h-4 w-4" aria-hidden="true" />
                <h3>Temas oscuros</h3>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <button
                    v-for="item in darkThemes"
                    :key="item.id"
                    type="button"
                    @click="setTheme(item.id)"
                    :aria-pressed="theme === item.id"
                    class="rounded-xl border p-3 text-left transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    :class="
                        theme === item.id
                            ? 'border-primary bg-accent/60'
                            : 'border-border bg-card hover:border-primary/50 hover:bg-accent/30'
                    "
                >
                    <div class="mb-3 flex gap-1.5">
                        <span
                            v-for="(color, i) in previews[item.id]"
                            :key="i"
                            class="h-4 w-4 rounded-full border border-border/60"
                            :style="{ backgroundColor: color }"
                            aria-hidden="true"
                        ></span>
                    </div>
                    <p class="text-sm font-semibold text-foreground">
                        {{ item.label }}
                    </p>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        {{ item.description }}
                    </p>
                </button>
            </div>
        </section>
    </div>
</template>
