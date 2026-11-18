<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

/**
 * Página de verificación de correo electrónico.
 */
defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Verifica tu correo"
        description="Confirma tu dirección de correo para continuar"
    >
        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 rounded-md border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-center text-sm font-medium text-emerald-400"
        >
            Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
        </div>

        <div class="space-y-5 text-sm text-muted-foreground">
            <p>
                Antes de continuar, revisa tu correo y haz clic en el enlace de verificación que te enviamos.
                Si no lo recibiste, puedes solicitar otro.
            </p>

            <Form v-bind="send.form()" v-slot="{ processing }" class="space-y-4">
                <Button type="submit" class="w-full" :disabled="processing">
                    <Spinner v-if="processing" />
                    Reenviar correo de verificación
                </Button>
            </Form>

            <div class="text-center">
                <TextLink :href="logout()" method="post" as="button">Cerrar sesión</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
