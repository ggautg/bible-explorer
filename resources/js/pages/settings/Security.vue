<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import ManagePasskeys from '@/components/ManagePasskeys.vue';
import ManageTwoFactor from '@/components/ManageTwoFactor.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import userPassword from '@/routes/user-password';
import type { Passkey } from '@/types/auth';

/**
 * Página de configuración de seguridad.
 * Permite cambiar la contraseña y gestionar 2FA y passkeys.
 */
defineProps<{
    canManageTwoFactor?: boolean;
    canManagePasskeys?: boolean;
    passkeys?: Passkey[];
    passwordRules?: string;
    twoFactorEnabled?: boolean;
    requiresConfirmation?: boolean;
}>();
</script>

<template>
    <SettingsLayout
        title="Seguridad"
        description="Administrá tu contraseña, verificación en dos pasos y llaves de acceso."
    >
        <div class="space-y-10">
            <section class="space-y-6">
                <Heading
                    variant="small"
                    title="Actualizar contraseña"
                    description="Usá una contraseña larga y aleatoria para mantener tu cuenta segura"
                />

                <Form
                    v-bind="userPassword.update.form()"
                    class="space-y-6"
                    reset-on-success
                    reset-on-error
                    :options="{ preserveScroll: true }"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="current_password">Contraseña actual</Label>
                        <PasswordInput
                            id="current_password"
                            name="current_password"
                            autocomplete="current-password"
                            placeholder="Contraseña actual"
                            aria-describedby="error-current_password"
                            :aria-invalid="!!errors.current_password"
                        />
                        <InputError
                            id="error-current_password"
                            :message="errors.current_password"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Nueva contraseña</Label>
                        <PasswordInput
                            id="password"
                            name="password"
                            autocomplete="new-password"
                            placeholder="Nueva contraseña"
                            aria-describedby="error-password"
                            :aria-invalid="!!errors.password"
                        />
                        <p
                            v-if="passwordRules"
                            class="text-xs text-muted-foreground"
                        >
                            {{ passwordRules }}
                        </p>
                        <InputError
                            id="error-password"
                            :message="errors.password"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation"
                            >Confirmar nueva contraseña</Label
                        >
                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            autocomplete="new-password"
                            placeholder="Repetí la nueva contraseña"
                            aria-describedby="error-password_confirmation"
                            :aria-invalid="!!errors.password_confirmation"
                        />
                        <InputError
                            id="error-password_confirmation"
                            :message="errors.password_confirmation"
                        />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            Guardar contraseña
                        </Button>
                        <p
                            v-if="recentlySuccessful"
                            role="status"
                            class="text-sm text-muted-foreground"
                        >
                            Guardado.
                        </p>
                    </div>
                </Form>
            </section>

            <Separator v-if="canManageTwoFactor || canManagePasskeys" />

            <ManageTwoFactor
                v-if="canManageTwoFactor"
                :can-manage-two-factor="canManageTwoFactor"
                :two-factor-enabled="twoFactorEnabled"
                :requires-confirmation="requiresConfirmation"
            />

            <Separator v-if="canManageTwoFactor && canManagePasskeys" />

            <ManagePasskeys
                v-if="canManagePasskeys"
                :can-manage-passkeys="canManagePasskeys"
                :passkeys="passkeys"
            />
        </div>
    </SettingsLayout>
</template>
