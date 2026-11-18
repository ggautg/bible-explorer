<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import register from '@/routes/register';

/**
 * Página de registro de nuevos usuarios.
 */
defineProps<{
    passwordRules?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Crear cuenta"
        description="Completa tus datos para registrarte"
    >
        <Form
            v-bind="register.store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-5"
        >
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="name">Nombre</Label>
                    <Input
                        id="name"
                        type="text"
                        name="name"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        placeholder="Tu nombre"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Correo electrónico</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        placeholder="correo@ejemplo.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Contraseña</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        placeholder="Contraseña"
                    />
                    <p v-if="passwordRules" class="text-xs text-muted-foreground">{{ passwordRules }}</p>
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirmar contraseña</Label>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        placeholder="Repite la contraseña"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-1 w-full"
                    :tabindex="5"
                    :disabled="processing"
                    data-test="register-button"
                >
                    <Spinner v-if="processing" />
                    Crear cuenta
                </Button>
            </div>
        </Form>

        <template #footer>
            ¿Ya tienes cuenta?
            <TextLink :href="login()" :tabindex="6">Iniciar sesión</TextLink>
        </template>
    </AuthLayout>
</template>
