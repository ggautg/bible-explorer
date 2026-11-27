<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ShieldCheck, Trash2 } from '@lucide/vue';

type Stats = {
    users: number;
    admins: number;
    churches: number;
    posts: number;
    follows: number;
    likes: number;
};

type AdminUser = {
    id: number;
    name: string;
    email: string;
    church: string | null;
    is_admin: boolean;
    posts_count: number;
    followers_count: number;
};

type AdminPost = {
    id: number;
    body: string | null;
    author: string;
};

defineProps<{
    stats: Stats;
    users: AdminUser[];
    posts: AdminPost[];
}>();

const currentUserId = usePage<{ auth: { user: { id: number } } }>().props.auth
    .user.id;

const toggleRole = (userId: number): void => {
    router.post(`/admin/usuarios/${userId}/rol`, {}, { preserveScroll: true });
};

const deletePost = (postId: number): void => {
    if (window.confirm('¿Borrar esta publicación?')) {
        router.delete(`/admin/posts/${postId}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Panel admin" />

    <div class="mx-auto max-w-6xl space-y-8 p-6">
        <header class="flex items-center gap-3">
            <ShieldCheck class="h-7 w-7 text-primary" />
            <div>
                <h1 class="text-2xl font-semibold">Panel admin</h1>
                <p class="text-sm text-muted-foreground">
                    Fantasma en la red, omnipresente en el sistema: ve todo,
                    nadie lo ve ni lo sigue.
                </p>
            </div>
        </header>

        <section
            class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6"
            aria-label="Métricas de la red"
        >
            <div
                v-for="(value, label) in stats"
                :key="label"
                class="rounded-lg border bg-card p-4"
            >
                <p
                    class="text-xs tracking-wide text-muted-foreground uppercase"
                >
                    {{ label }}
                </p>
                <p class="text-2xl font-semibold">{{ value }}</p>
            </div>
        </section>

        <section class="rounded-lg border">
            <h2 class="border-b p-4 text-lg font-medium">Usuarios</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-muted-foreground">
                        <tr>
                            <th class="p-3">Persona</th>
                            <th class="p-3">Iglesia</th>
                            <th class="p-3">Posts</th>
                            <th class="p-3">Seguidores</th>
                            <th class="p-3">Rol</th>
                            <th class="p-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="border-t"
                        >
                            <td class="p-3">
                                <span class="font-medium">{{ user.name }}</span>
                                <span
                                    class="block text-xs text-muted-foreground"
                                    >{{ user.email }}</span
                                >
                            </td>
                            <td class="p-3">{{ user.church ?? '—' }}</td>
                            <td class="p-3">{{ user.posts_count }}</td>
                            <td class="p-3">{{ user.followers_count }}</td>
                            <td class="p-3">
                                <span
                                    v-if="user.is_admin"
                                    class="rounded bg-primary/10 px-2 py-1 text-xs text-primary"
                                    >Admin fantasma</span
                                >
                                <span
                                    v-else
                                    class="text-xs text-muted-foreground"
                                    >Miembro</span
                                >
                            </td>
                            <td class="p-3">
                                <button
                                    type="button"
                                    class="text-sm underline"
                                    :disabled="user.id === currentUserId"
                                    :aria-label="`Cambiar rol de ${user.name}`"
                                    @click="toggleRole(user.id)"
                                >
                                    {{
                                        user.is_admin
                                            ? 'Quitar admin'
                                            : 'Hacer admin'
                                    }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-lg border">
            <h2 class="border-b p-4 text-lg font-medium">
                Últimas publicaciones
            </h2>
            <ul class="divide-y">
                <li
                    v-for="post in posts"
                    :key="post.id"
                    class="flex items-center gap-4 p-3 text-sm"
                >
                    <span class="min-w-0 flex-1">
                        <strong class="mr-2">{{ post.author }}</strong>
                        <span class="text-muted-foreground">{{
                            post.body ?? '(rebibleo)'
                        }}</span>
                    </span>
                    <button
                        type="button"
                        class="inline-flex items-center gap-1 text-sm text-destructive"
                        :aria-label="`Borrar publicación ${post.id}`"
                        @click="deletePost(post.id)"
                    >
                        <Trash2 class="h-4 w-4" />
                        Borrar
                    </button>
                </li>
            </ul>
        </section>
    </div>
</template>
