<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import twoFactorLogin from '@/routes/two-factor/login';

/**
 * Página de verificación en dos pasos durante el inicio de sesión.
 * Permite validar con código de autenticación o con código de recuperación.
 */
const recovery = ref(false);
const code = ref('');

const title = computed(() =>
    recovery.value ? 'Código de recuperación' : 'Verificación en dos pasos',
);
const description = computed(() =>
    recovery.value
        ? 'Ingresá uno de tus códigos de recuperación para acceder a tu cuenta.'
        : 'Ingresá el código de 6 dígitos de tu aplicación de autenticación.',
);
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <Form
            v-bind="twoFactorLogin.store.form()"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-5"
            @finish="code = ''"
        >
            <div class="grid gap-4">
                <template v-if="!recovery">
                    <input type="hidden" name="code" :value="code" />
                    <div class="flex flex-col items-center gap-3">
                        <Label for="otp" class="sr-only"
                            >Código de autenticación</Label
                        >
                        <InputOTP
                            id="otp"
                            v-model="code"
                            :maxlength="6"
                            :disabled="processing"
                            autofocus
                        >
                            <InputOTPGroup>
                                <InputOTPSlot
                                    v-for="i in 6"
                                    :key="i"
                                    :index="i - 1"
                                />
                            </InputOTPGroup>
                        </InputOTP>
                        <InputError :message="errors.code" />
                    </div>
                </template>

                <template v-else>
                    <div class="grid gap-2">
                        <Label for="recovery_code"
                            >Código de recuperación</Label
                        >
                        <Input
                            id="recovery_code"
                            name="recovery_code"
                            type="text"
                            required
                            autofocus
                            autocomplete="one-time-code"
                            placeholder="Ingresá un código de recuperación"
                        />
                        <InputError :message="errors.recovery_code" />
                    </div>
                </template>

                <Button
                    type="submit"
                    class="w-full"
                    :disabled="processing || (!recovery && code.length < 6)"
                >
                    <Spinner v-if="processing" />
                    Continuar
                </Button>
            </div>
        </Form>

        <template #footer>
            <button
                type="button"
                :aria-pressed="recovery"
                class="underline decoration-neutral-300 underline-offset-4 hover:decoration-current dark:decoration-neutral-500"
                @click="
                    recovery = !recovery;
                    code = '';
                "
            >
                {{
                    recovery
                        ? 'Usar un código de autenticación'
                        : 'Usar un código de recuperación'
                }}
            </button>
            <span class="mx-2 text-muted-foreground/50">·</span>
            <TextLink :href="logout()" method="post" as="button"
                >Cancelar</TextLink
            >
        </template>
    </AuthLayout>
</template>
