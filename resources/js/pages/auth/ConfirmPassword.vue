<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import password from '@/routes/password';

/**
 * Página de confirmación de contraseña para acciones sensibles.
 */
</script>

<template>
    <AuthLayout
        title="Confirmar contraseña"
        description="Esta es un área segura. Confirmá tu contraseña para continuar."
    >
        <Form
            v-bind="password.confirm.store.form()"
            reset-on-success
            v-slot="{ errors, processing }"
            class="flex flex-col gap-5"
        >
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="password">Contraseña</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        autofocus
                        autocomplete="current-password"
                        placeholder="Tu contraseña"
                    />
                    <InputError :message="errors.password" />
                </div>

                <Button type="submit" class="w-full" :disabled="processing">
                    <Spinner v-if="processing" />
                    Confirmar
                </Button>
            </div>
        </Form>
    </AuthLayout>
</template>
