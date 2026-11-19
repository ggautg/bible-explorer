<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';

/**
 * Página para solicitar el enlace de restablecimiento de contraseña.
 */
defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="¿Olvidaste tu contraseña?"
        description="Ingresá tu correo y te vamos a enviar un enlace para restablecerla"
    >
        <div
            v-if="status"
            class="mb-4 rounded-md border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-center text-sm font-medium text-emerald-400"
        >
            {{ status }}
        </div>

        <Form
            v-bind="email.form()"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-5"
        >
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="email">Correo electrónico</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="correo@ejemplo.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <Button type="submit" class="w-full" :disabled="processing">
                    <Spinner v-if="processing" />
                    Enviar enlace de restablecimiento
                </Button>
            </div>
        </Form>

        <template #footer>
            <TextLink :href="login()">Volver a iniciar sesión</TextLink>
        </template>
    </AuthLayout>
</template>
