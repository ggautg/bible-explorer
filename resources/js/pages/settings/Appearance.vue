<script setup lang="ts">
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import SettingsLayout from '@/layouts/SettingsLayout.vue';

/**
 * Página de configuración de apariencia.
 */
</script>

<template>
    <SettingsLayout
        title="Apariencia"
        description="Configurá cómo se ve la aplicación."
    >
        <section class="space-y-6">
            <Heading
                variant="small"
                title="Tema de la interfaz"
                description="Elegí la apariencia de la aplicación"
            />
            <AppearanceTabs />
        </section>
    </SettingsLayout>
</template>
