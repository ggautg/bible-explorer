<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import login from '@/routes/login';
import { request } from '@/routes/password';

/**
 * Página de inicio de sesión.
 * Permite autenticarse con correo y contraseña.
 */
defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <AuthLayout
        title="Iniciá sesión"
        description="Ingresá tu correo y contraseña para acceder a tu cuenta"
    >
        <div
            v-if="status"
            class="mb-4 rounded-md border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-center text-sm font-medium text-emerald-400"
        >
            {{ status }}
        </div>

        <Form
            v-bind="login.store.form()"
            :reset-on-success="['password']"
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
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="correo@ejemplo.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between gap-2">
                        <Label for="password">Contraseña</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm"
                            :tabindex="5"
                        >
                            ¿Olvidaste tu contraseña?
                        </TextLink>
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Contraseña"
                    />
                    <InputError :message="errors.password" />
                </div>

                <label class="flex items-center gap-3 text-sm">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Recordarme</span>
                </label>

                <Button
                    type="submit"
                    class="mt-1 w-full"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Iniciá sesión
                </Button>
            </div>
        </Form>

        <template #footer>
            ¿No tenés cuenta?
            <TextLink :href="register()" :tabindex="6">Creá tu cuenta</TextLink>
        </template>
    </AuthLayout>
</template>
