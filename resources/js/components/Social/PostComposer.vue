<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { BookOpen, Send } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import VerseReferencePicker from './VerseReferencePicker.vue';

/**
 * Compositor de publicaciones/respuestas.
 *
 * Reutilizable para el feed y para respuestas en detalle de publicación.
 * Maneja estado de envío, errores y limpieza del formulario.
 * Las opciones de comunidad son un tipo de publicación; la rebibleada se
 * genera automáticamente con el botón "Rebiblear" de cada tarjeta.
 */
const props = defineProps({
    /** Texto guía del campo. */
    placeholder: { type: String, default: '¿Qué estás meditando hoy?' },
    /** Texto del botón de envío. */
    submitLabel: { type: String, default: 'Publicar' },
    /** Endpoint al que se envía el formulario. */
    action: { type: String, default: '/posts' },
    /** Modo compacto para respuestas. */
    compact: { type: Boolean, default: false },
    /** Muestra el selector de opciones de comunidad. */
    showKinds: { type: Boolean, default: true },
});

/**
 * Opciones de comunidad. `value` debe coincidir con Post::SELECTABLE_KINDS.
 * los textos guía ayudan a completar cada intención.
 */
const KINDS = [
    {
        value: 'reflexion',
        label: 'Reflexión',
        hint: '¿Qué estás meditando hoy?',
    },
    {
        value: 'versiculo',
        label: 'Versículo',
        hint: 'Compartí un versículo que te haya hablado hoy.',
    },
    {
        value: 'peticion_oracion',
        label: 'Pedido de oración',
        hint: 'Contá qué necesitás que acompañemos en oración.',
    },
    {
        value: 'testimonio',
        label: 'Testimonio',
        hint: 'Contá lo que Dios hizo en tu vida.',
    },
    {
        value: 'agradecimiento',
        label: 'Agradecimiento',
        hint: '¿Qué agradecés hoy?',
    },
];

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const activeHint = computed(
    () =>
        KINDS.find((kind) => kind.value === form.kind)?.hint ??
        props.placeholder,
);

const form = useForm({
    body: '',
    kind: 'reflexion',
    book_code: null,
    chapter: null,
    verse: null,
});

const verseError = ref('');

/** El nombre del libro se deriva en el servidor; acá sólo va el código. */
function onVerseChange(reference) {
    form.book_code = reference.book_code;
    form.chapter = reference.chapter;
    form.verse = reference.verse;
    verseError.value = '';
}

// Si se cambia de opción, se descarta la referencia para no publicarla oculta.
watch(
    () => form.kind,
    (kind) => {
        if (kind !== 'versiculo') {
            form.book_code = null;
            form.chapter = null;
            form.verse = null;
            verseError.value = '';
        }
    },
);

/** Envía la publicación y limpia el formulario al completar. */
function submit() {
    if (form.processing || !form.body.trim()) {
        return;
    }

    if (
        form.kind === 'versiculo' &&
        (!form.book_code || !form.chapter || !form.verse)
    ) {
        verseError.value =
            'Elegí libro, capítulo y versículo antes de publicar.';

        return;
    }

    form.post(props.action, {
        preserveScroll: true,
        onSuccess: () =>
            form.reset('body', 'kind', 'book_code', 'chapter', 'verse'),
    });
}

/** Inicial para avatar de respaldo. */
function initial(name) {
    return (name || '?').trim().charAt(0).toUpperCase();
}
</script>

<template>
    <section
        v-if="user"
        class="rounded-2xl border border-bible-border bg-bible-panel/70 p-4 shadow-sm md:p-5"
        aria-label="Crear publicación"
    >
        <form class="flex gap-3" @submit.prevent="submit">
            <div class="shrink-0" aria-hidden="true">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-bible-border bg-bible-bg text-sm font-semibold text-bible-accent-bright"
                >
                    {{ initial(user.name) }}
                </div>
            </div>

            <div class="min-w-0 flex-1 space-y-3">
                <fieldset v-if="showKinds">
                    <legend class="mb-2 text-xs font-medium text-bible-muted">
                        ¿Qué querés compartir?
                    </legend>
                    <span class="sr-only" aria-live="polite">
                        {{
                            form.kind === 'versiculo'
                                ? 'Elegí un versículo.'
                                : ''
                        }}
                    </span>
                    <div class="flex flex-wrap gap-2" role="group">
                        <label
                            v-for="kind in KINDS"
                            :key="kind.value"
                            class="inline-flex cursor-pointer items-center rounded-full border px-3 py-1.5 text-xs font-medium transition-colors"
                            :class="
                                form.kind === kind.value
                                    ? 'border-bible-accent bg-bible-accent/15 text-bible-accent-bright'
                                    : 'border-bible-border text-bible-muted hover:border-bible-accent/50 hover:text-bible-text'
                            "
                        >
                            <input
                                v-model="form.kind"
                                type="radio"
                                name="kind"
                                :value="kind.value"
                                class="sr-only"
                            />
                            {{ kind.label }}
                        </label>
                    </div>
                </fieldset>

                <VerseReferencePicker
                    v-if="form.kind === 'versiculo'"
                    :book-code="form.book_code"
                    :chapter="form.chapter"
                    :verse="form.verse"
                    @change="onVerseChange"
                />
                <p
                    v-if="verseError"
                    class="text-sm text-bible-error"
                    role="alert"
                >
                    {{ verseError }}
                </p>

                <label class="sr-only" :for="`post-body-${action}`"
                    >Escribí tu publicación</label
                >
                <textarea
                    :id="`post-body-${action}`"
                    v-model="form.body"
                    :rows="compact ? 2 : 3"
                    required
                    maxlength="2000"
                    class="w-full resize-y rounded-xl border border-bible-border bg-bible-bg/60 px-4 py-3 text-sm leading-relaxed text-bible-text transition-colors placeholder:text-bible-muted/70 focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none md:text-[15px]"
                    :placeholder="activeHint"
                />
                <p
                    v-if="form.errors.body"
                    class="text-sm text-bible-error"
                    role="status"
                >
                    {{ form.errors.body }}
                </p>
                <p
                    v-if="form.errors.kind"
                    class="text-sm text-bible-error"
                    role="status"
                >
                    {{ form.errors.kind }}
                </p>
                <p
                    v-if="form.errors.book_code"
                    class="text-sm text-bible-error"
                    role="status"
                >
                    {{ form.errors.book_code }}
                </p>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p
                        class="inline-flex items-center gap-1.5 text-xs text-bible-muted"
                    >
                        <BookOpen class="h-3.5 w-3.5" aria-hidden="true" />
                        <span>{{ form.body.length }}/2000</span>
                    </p>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-full bg-bible-accent px-5 py-2 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright focus-visible:ring-2 focus-visible:ring-bible-accent focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="form.processing || !form.body.trim()"
                    >
                        <Send class="h-4 w-4" aria-hidden="true" />
                        {{ form.processing ? 'Publicando…' : submitLabel }}
                    </button>
                </div>
            </div>
        </form>
    </section>
</template>
