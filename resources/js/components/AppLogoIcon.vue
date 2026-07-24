<script setup lang="ts">
defineProps<{
    class?: string
}>()
</script>

<template>
    <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" :class="$props.class">
        <g stroke="currentColor" stroke-width="1.3" stroke-opacity="0.55">
            <line x1="20" y1="20" x2="8" y2="12" />
            <line x1="20" y1="20" x2="32" y2="10" />
            <line x1="20" y1="20" x2="11" y2="30" />
            <line x1="20" y1="20" x2="30" y2="29" />
        </g>
        <circle cx="20" cy="20" r="4" fill="currentColor" />
        <circle cx="8" cy="12" r="2" fill="currentColor" fill-opacity="0.75" />
        <circle cx="32" cy="10" r="2" fill="currentColor" fill-opacity="0.75" />
        <circle cx="11" cy="30" r="2" fill="currentColor" fill-opacity="0.75" />
        <circle cx="30" cy="29" r="2" fill="currentColor" fill-opacity="0.75" />
    </svg>
</template>