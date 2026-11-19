<script setup lang="ts">
import { Form, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { send } from '@/routes/verification';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';

/**
 * Página de configuración del perfil del usuario.
 */
defineProps<{
    mustVerifyEmail: boolean;
    status?: string;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user as { name: string; email: string; email_verified_at: string | null } | null);
</script>

<template>
    <SettingsLayout title="Perfil" description="Actualizá tu nombre y correo electrónico.">
        <div class="space-y-10">
            <section class="space-y-6">
                <Heading
                    variant="small"
                    title="Información del perfil"
                    description="Modificá los datos básicos de tu cuenta"
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="name">Nombre</Label>
                        <Input
                            id="name"
                            name="name"
                            :default-value="user?.name"
                            required
                            autocomplete="name"
                            placeholder="Tu nombre"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Correo electrónico</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            :default-value="user?.email"
                            required
                            autocomplete="username"
                            placeholder="correo@ejemplo.com"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && user && !user.email_verified_at" class="space-y-2 text-sm">
                        <p class="text-muted-foreground">
                            Tu correo electrónico no está verificado.
                            <Form v-bind="send.form()" class="inline" v-slot="{ processing: sending }">
                                <button
                                    type="submit"
                                    class="ml-1 underline underline-offset-4 hover:text-foreground"
                                    :disabled="sending"
                                >
                                    Reenviar correo de verificación
                                </button>
                            </Form>
                        </p>
                        <p
                            v-if="status === 'verification-link-sent'"
                            class="font-medium text-emerald-500"
                        >
                            Te enviamos un nuevo enlace de verificación a tu correo.
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            Guardar cambios
                        </Button>
                        <p v-if="recentlySuccessful" class="text-sm text-muted-foreground">Guardado.</p>
                    </div>
                </Form>
            </section>

            <DeleteUser />
        </div>
    </SettingsLayout>
</template>
