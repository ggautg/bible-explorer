<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/password';

/**
 * Página para definir una nueva contraseña mediante token de recuperación.
 */
const props = defineProps<{
    token: string;
    email: string;
    passwordRules?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Restablecer contraseña"
        description="Ingresá tu nueva contraseña para recuperar el acceso"
    >
        <Form
            v-bind="update.form()"
            :reset-on-success="['password', 'password_confirmation']"
            :transform="(data) => ({ ...data, token: props.token, email: props.email })"
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
                        :model-value="props.email"
                        readonly
                        autocomplete="email"
                        class="opacity-80"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Nueva contraseña</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        autofocus
                        autocomplete="new-password"
                        placeholder="Nueva contraseña"
                    />
                    <p v-if="passwordRules" class="text-xs text-muted-foreground">{{ passwordRules }}</p>
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirmar nueva contraseña</Label>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Repetí la nueva contraseña"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <input type="hidden" name="token" :value="props.token" />

                <Button type="submit" class="w-full" :disabled="processing">
                    <Spinner v-if="processing" />
                    Restablecer contraseña
                </Button>
            </div>
        </Form>
    </AuthLayout>
</template>
