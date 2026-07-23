<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'Crear una cuenta',
        description: 'Completá tus datos para crear tu cuenta',
    },
});
</script>

<template>
    <Head title="Registrarse" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="name" class="text-bible-text">Nombre</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    name="name"
                    placeholder="Nombre completo"
                    class="bg-bible-panel border-bible-border text-bible-text placeholder:text-bible-muted-dim focus-visible:ring-bible-accent"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email" class="text-bible-text">Correo electrónico</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="2"
                    autocomplete="email"
                    name="email"
                    placeholder="tu@email.com"
                    class="bg-bible-panel border-bible-border text-bible-text placeholder:text-bible-muted-dim focus-visible:ring-bible-accent"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password" class="text-bible-text">Contraseña</Label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="3"
                    autocomplete="new-password"
                    name="password"
                    placeholder="Contraseña"
                    :passwordrules="passwordRules"
                    class="bg-bible-panel border-bible-border text-bible-text placeholder:text-bible-muted-dim focus-visible:ring-bible-accent"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation" class="text-bible-text">Confirmar contraseña</Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Confirmar contraseña"
                    :passwordrules="passwordRules"
                    class="bg-bible-panel border-bible-border text-bible-text placeholder:text-bible-muted-dim focus-visible:ring-bible-accent"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full bg-bible-accent text-bible-bg hover:bg-bible-accent-bright"
                tabindex="5"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                Crear cuenta
            </Button>
        </div>

        <div class="text-center text-sm text-bible-muted">
            ¿Ya tenés una cuenta?
            <TextLink
                :href="login()"
                class="underline underline-offset-4 text-bible-accent hover:text-bible-accent-bright"
                :tabindex="6"
                >Iniciar sesión</TextLink
            >
        </div>
    </Form>
</template>