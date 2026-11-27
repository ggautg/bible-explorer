<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Ban,
    CheckCircle2,
    Flag,
    Search,
    ShieldCheck,
    Trash2,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Stats = {
    users: number;
    admins: number;
    banned: number;
    reports: number;
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
    is_banned: boolean;
    banned_until: string | null;
    posts_count: number;
    followers_count: number;
};

type AdminReport = {
    id: number;
    reporter: string;
    reported: string;
    body: string | null;
    reason: string;
    time: string | null;
};

type AdminPost = {
    id: number;
    body: string | null;
    author: string;
};

type TabId = 'reports' | 'users' | 'posts';

const props = defineProps<{
    stats: Stats;
    banDurations: Record<string, string>;
    users: AdminUser[];
    reports: AdminReport[];
    posts: AdminPost[];
}>();

const currentUserId = usePage<{ auth: { user: { id: number } } }>().props.auth
    .user.id;

type UserFilter = 'all' | 'admins' | 'banned';

const tab = ref<TabId>(props.reports.length ? 'reports' : 'users');
const userSearch = ref('');
const userFilter = ref<UserFilter>('all');
const postSearch = ref('');
const userFilters: { id: UserFilter; label: string }[] = [
    { id: 'all', label: 'Todos' },
    { id: 'admins', label: 'Admins' },
    { id: 'banned', label: 'Baneados' },
];

const tabs = computed(() => [
    { id: 'reports' as const, label: 'Reportes', count: props.reports.length },
    { id: 'users' as const, label: 'Usuarios', count: props.users.length },
    {
        id: 'posts' as const,
        label: 'Publicaciones',
        count: props.posts.length,
    },
]);

const metrics = computed(() => [
    {
        key: 'users',
        label: 'Usuarios',
        value: props.stats.users,
        accent: 'bg-sky-500',
    },
    {
        key: 'admins',
        label: 'Admins',
        value: props.stats.admins,
        accent: 'bg-violet-500',
    },
    {
        key: 'reports',
        label: 'Reportes',
        value: props.stats.reports,
        accent: 'bg-amber-500',
    },
    {
        key: 'banned',
        label: 'Suspendidos',
        value: props.stats.banned,
        accent: 'bg-destructive',
    },
    {
        key: 'posts',
        label: 'Publicaciones',
        value: props.stats.posts,
        accent: 'bg-emerald-500',
    },
    {
        key: 'churches',
        label: 'Iglesias',
        value: props.stats.churches,
        accent: 'bg-teal-500',
    },
    {
        key: 'follows',
        label: 'Seguidores',
        value: props.stats.follows,
        accent: 'bg-indigo-500',
    },
    {
        key: 'likes',
        label: 'Me gusta',
        value: props.stats.likes,
        accent: 'bg-rose-500',
    },
]);

const filteredUsers = computed(() => {
    const query = userSearch.value.trim().toLowerCase();

    return props.users.filter((user) => {
        if (userFilter.value === 'admins' && !user.is_admin) {
            return false;
        }

        if (userFilter.value === 'banned' && !user.is_banned) {
            return false;
        }

        return (
            !query ||
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query)
        );
    });
});

const filteredPosts = computed(() => {
    const query = postSearch.value.trim().toLowerCase();

    if (!query) {
        return props.posts;
    }

    return props.posts.filter(
        (post) =>
            post.author.toLowerCase().includes(query) ||
            (post.body ?? '').toLowerCase().includes(query),
    );
});

const goToTab = (key: string): void => {
    if (key === 'reports' || key === 'banned') {
        tab.value = 'reports';
    } else if (key === 'users' || key === 'admins') {
        tab.value = 'users';
    } else {
        tab.value = 'posts';
    }
};

const shortDate = (iso: string | null): string =>
    iso
        ? new Date(iso).toLocaleString('es-AR', {
              day: '2-digit',
              month: '2-digit',
              hour: '2-digit',
              minute: '2-digit',
          })
        : '';

const toggleRole = (userId: number): void => {
    router.post(`/admin/usuarios/${userId}/rol`, {}, { preserveScroll: true });
};

const ban = (userId: number, minutes: number): void => {
    router.post(
        `/admin/usuarios/${userId}/ban`,
        { minutes },
        { preserveScroll: true },
    );
};

const banFromSelect = (userId: number, event: Event): void => {
    const value = (event.target as HTMLSelectElement).value;

    if (value) {
        ban(userId, Number(value));
    }
};

const resolveReport = (
    reportId: number,
    action: 'ban' | 'delete' | 'dismiss',
    minutes?: number,
): void => {
    router.post(
        `/admin/reportes/${reportId}/resolver`,
        { action, minutes },
        { preserveScroll: true },
    );
};

const banFromReview = (reportId: number, event: Event): void => {
    const value = (event.target as HTMLSelectElement).value;

    if (value) {
        resolveReport(reportId, 'ban', Number(value));
    }
};

const unban = (userId: number): void => {
    router.post(
        `/admin/usuarios/${userId}/desbanear`,
        {},
        { preserveScroll: true },
    );
};

const deletePost = (postId: number): void => {
    if (window.confirm('¿Borrar esta publicación?')) {
        router.delete(`/admin/posts/${postId}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Panel admin" />

    <div class="mx-auto max-w-6xl space-y-6 p-4 md:p-8">
        <header class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <span
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-primary/30 bg-primary/10"
                >
                    <ShieldCheck class="h-6 w-6 text-primary" />
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-semibold tracking-tight">
                            Panel admin
                        </h1>
                        <Badge variant="secondary">Modo fantasma</Badge>
                    </div>
                    <p class="mt-1 max-w-xl text-sm text-muted-foreground">
                        Ve todo, nadie lo ve ni lo sigue. Revisá reportes,
                        moderá usuarios y publicaciones desde acá.
                    </p>
                </div>
            </div>
            <Button variant="outline" size="sm" as-child>
                <Link href="/comunidad">
                    <ArrowLeft />
                    Volver a la comunidad
                </Link>
            </Button>
        </header>

        <section aria-label="Métricas de la red">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
                <button
                    v-for="metric in metrics"
                    :key="metric.key"
                    type="button"
                    class="rounded-xl border bg-card p-3 text-left transition-colors hover:bg-accent/50 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    :aria-label="`Ver ${metric.label.toLowerCase()}`"
                    @click="goToTab(metric.key)"
                >
                    <span
                        class="block h-1.5 w-8 rounded-full"
                        :class="metric.accent"
                    />
                    <span
                        class="mt-2 block text-2xl font-semibold tabular-nums"
                        >{{ metric.value }}</span
                    >
                    <span class="text-xs text-muted-foreground">{{
                        metric.label
                    }}</span>
                </button>
            </div>
        </section>

        <div
            role="tablist"
            aria-label="Secciones del panel"
            class="flex flex-wrap gap-1 rounded-xl border bg-muted/30 p-1"
        >
            <button
                v-for="item in tabs"
                :key="item.id"
                type="button"
                role="tab"
                :aria-selected="tab === item.id"
                class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                :class="
                    tab === item.id
                        ? 'bg-background text-foreground shadow-xs'
                        : 'text-muted-foreground hover:text-foreground'
                "
                @click="tab = item.id"
            >
                {{ item.label }}
                <span
                    class="rounded-full bg-muted px-2 py-0.5 text-xs tabular-nums"
                    >{{ item.count }}</span
                >
            </button>
        </div>

        <section v-if="tab === 'reports'" role="tabpanel" class="space-y-3">
            <div class="flex items-center gap-2">
                <Flag class="h-5 w-5 text-amber-500" />
                <h2 class="text-lg font-medium">Casos para revisar</h2>
                <Badge v-if="reports.length" variant="destructive">
                    {{ reports.length }} pendientes
                </Badge>
            </div>

            <article
                v-for="(report, index) in reports"
                :key="report.id"
                class="rounded-xl border bg-card p-4 shadow-xs"
            >
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="flex flex-wrap items-center gap-2 text-sm">
                            <Badge variant="outline"
                                >Caso {{ index + 1 }}</Badge
                            >
                            <strong>{{ report.reporter }}</strong>
                            <span class="text-muted-foreground">reportó a</span>
                            <strong>{{ report.reported }}</strong>
                        </p>
                        <p
                            v-if="report.time"
                            class="mt-1 text-xs text-muted-foreground"
                        >
                            {{ report.time }}
                        </p>
                    </div>
                </div>

                <p
                    class="mt-3 rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-sm"
                >
                    <span class="font-medium">Motivo:</span> {{ report.reason }}
                </p>
                <blockquote
                    class="mt-2 border-l-2 border-border pl-3 text-sm text-muted-foreground"
                >
                    {{ report.body ?? '(rebibleo)' }}
                </blockquote>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <select
                        class="h-8 rounded-md border border-input bg-transparent px-2 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        :aria-label="`Elegir sanción para ${report.reported}`"
                        @change="banFromReview(report.id, $event)"
                    >
                        <option value="">Sancionar…</option>
                        <option
                            v-for="(label, minutes) in banDurations"
                            :key="minutes"
                            :value="minutes"
                        >
                            {{ label }}
                        </option>
                    </select>
                    <Button
                        variant="outline"
                        size="sm"
                        :aria-label="`Borrar publicación reportada de ${report.reported}`"
                        @click="resolveReport(report.id, 'delete')"
                    >
                        <Trash2 />
                        Borrar post
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        :aria-label="`Descartar reporte de ${report.reporter}`"
                        @click="resolveReport(report.id, 'dismiss')"
                    >
                        <CheckCircle2 />
                        Descartar
                    </Button>
                </div>
            </article>

            <div
                v-if="!reports.length"
                class="rounded-xl border border-dashed p-10 text-center"
            >
                <CheckCircle2 class="mx-auto h-8 w-8 text-muted-foreground" />
                <p class="mt-2 font-medium">Todo al día</p>
                <p class="text-sm text-muted-foreground">
                    No hay reportes pendientes de revisión.
                </p>
            </div>
        </section>

        <section v-else-if="tab === 'users'" role="tabpanel" class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-medium">
                    Usuarios
                    <span class="text-sm font-normal text-muted-foreground"
                        >({{ filteredUsers.length }} de
                        {{ users.length }})</span
                    >
                </h2>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative w-full sm:w-64">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="userSearch"
                            class="pl-8"
                            placeholder="Buscar por nombre o correo"
                            aria-label="Buscar usuarios"
                        />
                    </div>
                    <div
                        class="flex gap-1 rounded-lg border bg-muted/30 p-1 text-xs"
                    >
                        <button
                            v-for="filter in userFilters"
                            :key="filter.id"
                            type="button"
                            class="rounded px-2 py-1 transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            :class="
                                userFilter === filter.id
                                    ? 'bg-background font-medium shadow-xs'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                            @click="userFilter = filter.id"
                        >
                            {{ filter.label }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border">
                <table class="w-full text-left text-sm">
                    <thead
                        class="bg-muted/40 text-xs text-muted-foreground uppercase"
                    >
                        <tr>
                            <th class="p-3">Persona</th>
                            <th class="p-3">Estado</th>
                            <th class="p-3 text-center">Posts</th>
                            <th class="p-3 text-center">Seguidores</th>
                            <th class="p-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in filteredUsers"
                            :key="user.id"
                            class="border-t"
                        >
                            <td class="p-3">
                                <span class="font-medium">{{ user.name }}</span>
                                <span
                                    class="block text-xs text-muted-foreground"
                                    >{{ user.email }}
                                    <template v-if="user.church"
                                        >· {{ user.church }}</template
                                    ></span
                                >
                            </td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        :variant="
                                            user.is_admin
                                                ? 'default'
                                                : 'outline'
                                        "
                                    >
                                        {{
                                            user.is_admin
                                                ? 'Admin fantasma'
                                                : 'Miembro'
                                        }}
                                    </Badge>
                                    <Badge
                                        v-if="user.is_banned"
                                        variant="destructive"
                                    >
                                        <Ban />
                                        Hasta {{ shortDate(user.banned_until) }}
                                    </Badge>
                                </div>
                            </td>
                            <td class="p-3 text-center tabular-nums">
                                {{ user.posts_count }}
                            </td>
                            <td class="p-3 text-center tabular-nums">
                                {{ user.followers_count }}
                            </td>
                            <td class="p-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <Button
                                        variant="link"
                                        size="sm"
                                        class="h-8 px-1"
                                        :disabled="user.id === currentUserId"
                                        :title="
                                            user.id === currentUserId
                                                ? 'No podés cambiar tu propio rol'
                                                : undefined
                                        "
                                        :aria-label="`Cambiar rol de ${user.name}`"
                                        @click="toggleRole(user.id)"
                                    >
                                        {{
                                            user.is_admin
                                                ? 'Quitar admin'
                                                : 'Hacer admin'
                                        }}
                                    </Button>
                                    <select
                                        class="h-8 rounded-md border border-input bg-transparent px-2 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:opacity-50"
                                        :disabled="user.id === currentUserId"
                                        :title="
                                            user.id === currentUserId
                                                ? 'No podés banearte a vos mismo'
                                                : undefined
                                        "
                                        :aria-label="`Banear a ${user.name}`"
                                        @change="banFromSelect(user.id, $event)"
                                    >
                                        <option value="">Banear…</option>
                                        <option
                                            v-for="(
                                                label, minutes
                                            ) in banDurations"
                                            :key="minutes"
                                            :value="minutes"
                                        >
                                            {{ label }}
                                        </option>
                                    </select>
                                    <Button
                                        v-if="user.is_banned"
                                        variant="outline"
                                        size="sm"
                                        :aria-label="`Desbanear a ${user.name}`"
                                        @click="unban(user.id)"
                                    >
                                        Desbanear
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!filteredUsers.length">
                            <td
                                colspan="5"
                                class="p-8 text-center text-muted-foreground"
                            >
                                No hay usuarios que coincidan con la búsqueda.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section v-else role="tabpanel" class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-medium">
                    Últimas publicaciones
                    <span class="text-sm font-normal text-muted-foreground"
                        >({{ filteredPosts.length }} de
                        {{ posts.length }})</span
                    >
                </h2>
                <div class="relative w-full sm:w-72">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="postSearch"
                        class="pl-8"
                        placeholder="Buscar por autor o texto"
                        aria-label="Buscar publicaciones"
                    />
                </div>
            </div>

            <ul class="divide-y overflow-hidden rounded-xl border">
                <li
                    v-for="post in filteredPosts"
                    :key="post.id"
                    class="flex items-center gap-4 bg-card p-3 text-sm"
                >
                    <span class="min-w-0 flex-1">
                        <strong class="mr-2">{{ post.author }}</strong>
                        <span class="text-muted-foreground">{{
                            post.body ?? '(rebibleo)'
                        }}</span>
                    </span>
                    <Button
                        variant="ghost"
                        size="sm"
                        class="text-destructive hover:text-destructive"
                        :aria-label="`Borrar publicación ${post.id}`"
                        @click="deletePost(post.id)"
                    >
                        <Trash2 />
                        Borrar
                    </Button>
                </li>
                <li
                    v-if="!filteredPosts.length"
                    class="p-8 text-center text-muted-foreground"
                >
                    No hay publicaciones que coincidan con la búsqueda.
                </li>
            </ul>
        </section>
    </div>
</template>
