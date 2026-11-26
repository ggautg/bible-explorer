<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    HeartHandshake,
    MessageCircle,
    Sparkles,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import StarfieldBackground from '@/components/Bible/StarfieldBackground.vue';
import TestimonialCard from '@/components/Bible/TestimonialCard.vue';
import testimonials from '@/helpers/testimonials.js';
import { logout } from '@/routes';

/**
 * Página de bienvenida del explorador bíblico.
 * Presenta la propuesta de valor, el versículo del día y accesos principales.
 */
defineProps({
    /** Versículo destacado del día, o null si aún no hay datos cargados. */
    verseOfTheDay: {
        type: Object,
        default: null,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

/** Pilares principales mostrados en la sección de propuesta. */
const highlights = [
    {
        icon: Users,
        title: 'Comunidad viva',
        description:
            'Compartí reflexiones, seguí a otros creyentes y crecé junto a tu iglesia.',
    },
    {
        icon: BookOpen,
        title: 'Biblia conectada',
        description:
            'Leé, buscá y descubrí relaciones entre pasajes con herramientas simples.',
    },
    {
        icon: Sparkles,
        title: 'Hábito diario',
        description:
            'Volvé cada día al versículo destacado y sostené tu ritmo de lectura y oración.',
    },
];
</script>

<template>
    <Head title="Inicio" />

    <div
        class="relative min-h-screen overflow-hidden bg-bible-bg font-bible-body text-bible-text"
    >
        <StarfieldBackground />

        <!-- Capa decorativa suave para dar profundidad sin perder legibilidad. -->
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(201,162,39,0.10),transparent_55%),radial-gradient(ellipse_at_bottom_right,rgba(201,162,39,0.06),transparent_45%)]"
            aria-hidden="true"
        />

        <!-- Navegación superior: marca y accesos de cuenta. -->
        <header
            class="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 lg:px-8"
        >
            <Link
                href="/"
                class="flex items-center gap-3 rounded-md focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
            >
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-bible-accent/30 bg-bible-panel/70 text-bible-accent-bright shadow-sm"
                >
                    <BookOpen class="h-5 w-5" aria-hidden="true" />
                </span>
                <div class="leading-tight">
                    <p
                        class="font-bible-display text-lg font-semibold text-bible-accent-bright"
                    >
                        Explorador Bíblico
                    </p>
                    <p class="text-xs text-bible-muted">Comunidad y Palabra</p>
                </div>
            </Link>

            <nav
                class="flex items-center gap-2 text-sm"
                aria-label="Accesos principales"
            >
                <template v-if="user">
                    <span class="hidden text-bible-muted sm:inline"
                        >Hola,
                        <span class="font-medium text-bible-text">{{
                            user.name
                        }}</span></span
                    >
                    <Link
                        href="/comunidad"
                        class="inline-flex items-center justify-center rounded-full border border-bible-border px-4 py-2 font-medium text-bible-text transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                    >
                        Ir a la comunidad
                    </Link>
                    <Link
                        :href="logout()"
                        method="post"
                        as="button"
                        class="inline-flex items-center justify-center rounded-full px-4 py-2 font-medium text-bible-muted transition-colors hover:text-bible-error focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                    >
                        Cerrar sesión
                    </Link>
                </template>
                <template v-else>
                    <Link
                        href="/login"
                        class="inline-flex items-center justify-center rounded-full px-4 py-2 font-medium text-bible-muted transition-colors hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                    >
                        Iniciar sesión
                    </Link>
                    <Link
                        href="/register"
                        class="inline-flex items-center justify-center rounded-full bg-bible-accent px-4 py-2 font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                    >
                        Crear cuenta
                    </Link>
                </template>
            </nav>
        </header>

        <main
            class="relative z-10 mx-auto w-full max-w-7xl px-6 pb-16 lg:px-8 lg:pb-24"
        >
            <!-- Hero: mensaje principal, acciones y versículo del día. -->
            <section
                class="grid items-center gap-10 py-8 lg:grid-cols-[1.05fr_0.95fr] lg:gap-14 lg:py-14"
            >
                <div class="max-w-2xl">
                    <p
                        class="mb-4 inline-flex items-center gap-2 rounded-full border border-bible-accent/25 bg-bible-panel/60 px-3 py-1 text-xs font-medium tracking-[0.18em] text-bible-accent uppercase"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full bg-bible-accent"
                            aria-hidden="true"
                        />
                        Red social cristiana
                    </p>

                    <h1
                        class="font-bible-display text-4xl leading-[1.1] font-semibold tracking-tight text-bible-accent-bright sm:text-5xl lg:text-6xl"
                    >
                        Leé la Biblia.
                        <span class="block text-bible-text"
                            >Compartí lo que Dios te habla.</span
                        >
                    </h1>

                    <p
                        class="mt-6 max-w-xl text-base leading-relaxed text-bible-muted md:text-lg"
                    >
                        Un espacio moderno para leer las Escrituras, conectar
                        con otros creyentes y mantener viva tu vida espiritual,
                        todos los días.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link
                            href="/register"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-bible-accent px-6 py-3 text-sm font-semibold text-bible-bg transition-all hover:bg-bible-accent-bright hover:shadow-[0_0_0_4px_rgba(201,162,39,0.12)] focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                        >
                            Empezar gratis
                            <ArrowRight class="h-4 w-4" aria-hidden="true" />
                        </Link>
                        <Link
                            href="/leer"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-bible-border bg-bible-panel/30 px-6 py-3 text-sm font-semibold text-bible-text transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                        >
                            Explorar la Biblia
                        </Link>
                    </div>

                    <!-- Indicadores rápidos de confianza/producto. -->
                    <div
                        class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm text-bible-muted"
                    >
                        <div class="flex items-center gap-2">
                            <Users
                                class="h-4 w-4 text-bible-accent"
                                aria-hidden="true"
                            />
                            Comunidad y perfiles
                        </div>
                        <div class="flex items-center gap-2">
                            <BookOpen
                                class="h-4 w-4 text-bible-accent"
                                aria-hidden="true"
                            />
                            Lectura y búsqueda
                        </div>
                        <div class="flex items-center gap-2">
                            <HeartHandshake
                                class="h-4 w-4 text-bible-accent"
                                aria-hidden="true"
                            />
                            Acompañamiento
                        </div>
                    </div>
                </div>

                <!-- Tarjeta destacada: versículo del día con acción directa. -->
                <div class="relative">
                    <div
                        class="absolute -inset-4 rounded-[2rem] bg-bible-accent/5 blur-2xl"
                        aria-hidden="true"
                    />
                    <article
                        class="relative overflow-hidden rounded-3xl border border-bible-border bg-bible-panel/75 p-6 shadow-[0_20px_60px_-20px_rgba(0,0,0,0.45)] backdrop-blur-sm md:p-8"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <p
                                class="text-xs font-semibold tracking-[0.2em] text-bible-accent uppercase"
                            >
                                Versículo del día
                            </p>
                            <span
                                class="rounded-full border border-bible-border bg-bible-bg/40 px-3 py-1 text-xs text-bible-muted"
                                >Reina Valera Gómez</span
                            >
                        </div>

                        <template v-if="verseOfTheDay">
                            <blockquote class="mt-6">
                                <p
                                    class="font-bible-display text-2xl leading-relaxed text-bible-text md:text-[2rem] md:leading-snug"
                                >
                                    “{{ verseOfTheDay.text }}”
                                </p>
                            </blockquote>
                            <div
                                class="mt-8 flex flex-wrap items-end justify-between gap-4 border-t border-bible-border/70 pt-5"
                            >
                                <div>
                                    <p class="text-sm text-bible-muted">
                                        Referencia
                                    </p>
                                    <p
                                        class="text-base font-semibold text-bible-accent-bright"
                                    >
                                        {{ verseOfTheDay.bookName }}
                                        {{ verseOfTheDay.chapter }}:{{
                                            verseOfTheDay.verse
                                        }}
                                    </p>
                                </div>
                                <Link
                                    :href="`/bible/${verseOfTheDay.book}/${verseOfTheDay.chapter}/${verseOfTheDay.verse}`"
                                    class="inline-flex items-center gap-2 rounded-full border border-bible-accent/30 px-4 py-2 text-sm font-medium text-bible-accent-bright transition-colors hover:bg-bible-accent/10 focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                                >
                                    Leer en contexto
                                    <ArrowRight
                                        class="h-4 w-4"
                                        aria-hidden="true"
                                    />
                                </Link>
                            </div>
                        </template>

                        <template v-else>
                            <p
                                class="mt-6 text-base leading-relaxed text-bible-muted"
                            >
                                El versículo del día estará disponible cuando se
                                cargue el contenido bíblico en la base de datos.
                            </p>
                            <Link
                                href="/leer"
                                class="mt-6 inline-flex items-center gap-2 text-sm font-medium text-bible-accent-bright hover:underline"
                            >
                                Ir a la lectura
                                <ArrowRight
                                    class="h-4 w-4"
                                    aria-hidden="true"
                                />
                            </Link>
                        </template>
                    </article>
                </div>
            </section>

            <!-- Propuesta de valor: tres pilares claros y escaneables. -->
            <section class="py-10 lg:py-14" aria-labelledby="propuesta-title">
                <div class="mb-8 max-w-2xl">
                    <p
                        class="text-xs font-semibold tracking-[0.2em] text-bible-accent uppercase"
                    >
                        Todo en un solo lugar
                    </p>
                    <h2
                        id="propuesta-title"
                        class="mt-2 font-bible-display text-3xl font-semibold text-bible-accent-bright md:text-4xl"
                    >
                        Una experiencia simple, moderna y centrada en la Palabra
                    </h2>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <article
                        v-for="item in highlights"
                        :key="item.title"
                        class="group rounded-2xl border border-bible-border bg-bible-panel/55 p-6 transition-colors hover:border-bible-accent/35 hover:bg-bible-panel/75"
                    >
                        <div
                            class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl border border-bible-accent/20 bg-bible-bg/40 text-bible-accent"
                        >
                            <component
                                :is="item.icon"
                                class="h-5 w-5"
                                aria-hidden="true"
                            />
                        </div>
                        <h3 class="text-lg font-semibold text-bible-text">
                            {{ item.title }}
                        </h3>
                        <p
                            class="mt-2 text-sm leading-relaxed text-bible-muted"
                        >
                            {{ item.description }}
                        </p>
                    </article>
                </div>
            </section>

            <!-- Comunidad y acompañamiento: bloque de acción humana, maquetado funcional. -->
            <section class="py-10 lg:py-14">
                <div
                    class="grid gap-6 rounded-3xl border border-bible-border bg-bible-panel/45 p-6 md:grid-cols-[1fr_auto] md:items-center md:p-8"
                >
                    <div>
                        <p
                            class="text-xs font-semibold tracking-[0.2em] text-bible-accent uppercase"
                        >
                            Acompañamiento
                        </p>
                        <h2
                            class="mt-2 font-bible-display text-2xl font-semibold text-bible-accent-bright md:text-3xl"
                        >
                            ¿Necesitás hablar con alguien de tu iglesia?
                        </h2>
                        <p
                            class="mt-3 max-w-2xl text-sm leading-relaxed text-bible-muted md:text-base"
                        >
                            Estamos preparando un espacio para conectarte con
                            pastores y pedir oración de forma cercana y
                            confidencial.
                        </p>
                        <p class="mt-3 text-xs text-bible-muted-dim">
                            Maquetado de interfaz (próximamente disponible).
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-bible-accent px-5 py-3 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                        >
                            <MessageCircle class="h-4 w-4" aria-hidden="true" />
                            Iniciar conversación
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-bible-border px-5 py-3 text-sm font-medium text-bible-text transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                        >
                            <HeartHandshake
                                class="h-4 w-4"
                                aria-hidden="true"
                            />
                            Pedir oración
                        </button>
                    </div>
                </div>
            </section>

            <!-- Testimonios: prueba social con el mismo componente existente. -->
            <section class="py-10 lg:py-14" aria-labelledby="testimonios-title">
                <div
                    class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between"
                >
                    <div class="max-w-2xl">
                        <p
                            class="text-xs font-semibold tracking-[0.2em] text-bible-accent uppercase"
                        >
                            Testimonios
                        </p>
                        <h2
                            id="testimonios-title"
                            class="mt-2 font-bible-display text-3xl font-semibold text-bible-accent-bright md:text-4xl"
                        >
                            Personas reales, fe en movimiento
                        </h2>
                    </div>
                    <p class="max-w-sm text-sm text-bible-muted">
                        Historias de quienes encontraron en la Palabra un lugar
                        para volver.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <TestimonialCard
                        v-for="(testimonial, index) in testimonials"
                        :key="index"
                        :testimonial="testimonial"
                    />
                </div>
            </section>

            <!-- Cierre con CTA final, claro y sin fricción. -->
            <section class="pt-6 lg:pt-10">
                <div
                    class="relative overflow-hidden rounded-3xl border border-bible-accent/20 bg-gradient-to-br from-bible-panel/80 to-bible-panel/40 p-8 text-center md:p-12"
                >
                    <div
                        class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(201,162,39,0.12),transparent_50%)]"
                        aria-hidden="true"
                    />
                    <div class="relative">
                        <h2
                            class="font-bible-display text-3xl font-semibold text-bible-accent-bright md:text-4xl"
                        >
                            Empezá hoy tu recorrido en la Biblia
                        </h2>
                        <p
                            class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-bible-muted md:text-base"
                        >
                            Creá tu cuenta, seguí a otros creyentes y convertí
                            la lectura en un hábito compartido.
                        </p>
                        <div class="mt-7 flex flex-wrap justify-center gap-3">
                            <Link
                                href="/register"
                                class="inline-flex items-center justify-center gap-2 rounded-full bg-bible-accent px-6 py-3 text-sm font-semibold text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                            >
                                Crear mi cuenta
                                <ArrowRight
                                    class="h-4 w-4"
                                    aria-hidden="true"
                                />
                            </Link>
                            <Link
                                href="/login"
                                class="inline-flex items-center justify-center rounded-full border border-bible-border px-6 py-3 text-sm font-semibold text-bible-text transition-colors hover:border-bible-accent hover:text-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none"
                            >
                                Ya tengo cuenta
                            </Link>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
