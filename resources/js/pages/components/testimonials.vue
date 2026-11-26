<script setup>
defineProps({
    testimonial: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="rounded-lg border border-bible-border bg-bible-panel p-6">
        <!-- Comillas de apertura -->
        <svg
            width="28"
            height="20"
            viewBox="0 0 28 20"
            class="mb-3 text-bible-accent opacity-60"
            aria-hidden="true"
        >
            <path
                fill="currentColor"
                d="M0 20V11.4C0 7.73 0.933 4.867 2.8 2.8C4.667 0.933 7.133 0 10.2 0V4.2C8.333 4.2 6.9 4.733 5.9 5.8C4.9 6.733 4.4 8.067 4.4 9.8H10.2V20H0ZM17.8 20V11.4C17.8 7.73 18.733 4.867 20.6 2.8C22.467 0.933 24.933 0 28 0V4.2C26.133 4.2 24.7 4.733 23.7 5.8C22.7 6.733 22.2 8.067 22.2 9.8H28V20H17.8Z"
            />
        </svg>

        <p class="font-bible-display text-lg leading-relaxed text-bible-text">
            {{ testimonial.text }}
        </p>

        <!-- Comillas de cierre -->
        <svg
            width="28"
            height="20"
            viewBox="0 0 28 20"
            class="mt-3 mb-5 ml-auto rotate-180 text-bible-accent opacity-60"
            aria-hidden="true"
        >
            <path
                fill="currentColor"
                d="M0 20V11.4C0 7.73 0.933 4.867 2.8 2.8C4.667 0.933 7.133 0 10.2 0V4.2C8.333 4.2 6.9 4.733 5.9 5.8C4.9 6.733 4.4 8.067 4.4 9.8H10.2V20H0ZM17.8 20V11.4C17.8 7.73 18.733 4.867 20.6 2.8C22.467 0.933 24.933 0 28 0V4.2C26.133 4.2 24.7 4.733 23.7 5.8C22.7 6.733 22.2 8.067 22.2 9.8H28V20H17.8Z"
            />
        </svg>
        <div class="flex items-center justify-center gap-3">
            <img
                :src="testimonial.image"
                :alt="testimonial.name"
                class="h-10 w-10 rounded-full border border-bible-border object-cover"
            />

            <div class="flex flex-col">
                <span class="text-sm font-medium text-bible-text">
                    {{ testimonial.name }}
                </span>

                <span class="text-sm text-bible-muted">
                    {{ testimonial.role }}
                </span>
            </div>
        </div>
    </div>
</template>
