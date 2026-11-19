<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { BookOpen, Sparkles } from '@lucide/vue';

const props = defineProps({
  /** Placeholder del campo de texto. */
  placeholder: { type: String, default: '¿Qué estás meditando hoy?' },
  /** Texto del botón de publicar. */
  submitLabel: { type: String, default: 'Publicar' },
  /** Endpoint de envío. */
  action: { type: String, default: '/posts' },
  /** Modo compacto (para respuestas). */
  compact: { type: Boolean, default: false },
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

const form = useForm({
  body: '',
  book_code: null,
  book_name: null,
  chapter: null,
  verse: null,
});

function submit() {
  form.post(props.action, {
    preserveScroll: true,
    onSuccess: () => form.reset('body', 'book_code', 'book_name', 'chapter', 'verse'),
  });
}

function initial(name) {
  return (name || '?').trim().charAt(0).toUpperCase();
}
</script>

<template>
  <section v-if="user" class="rounded-2xl border border-bible-border bg-bible-panel/70 p-4 md:p-5" aria-label="Crear publicación">
    <form class="flex gap-3" @submit.prevent="submit">
      <div class="shrink-0">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-bible-border bg-bible-bg text-sm font-semibold text-bible-accent-bright" aria-hidden="true">
          {{ initial(user.name) }}
        </div>
      </div>
      <div class="min-w-0 flex-1 space-y-3">
        <label class="sr-only" for="post-body">Escribí tu publicación</label>
        <textarea
          id="post-body"
          v-model="form.body"
          :rows="compact ? 3 : 4"
          required
          maxlength="2000"
          class="w-full resize-y rounded-xl border border-bible-border bg-bible-bg/60 px-4 py-3 text-sm leading-relaxed text-bible-text placeholder:text-bible-muted/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent md:text-[15px]"
          :placeholder="placeholder"
        />
        <p v-if="form.errors.body" class="text-sm text-red-400">{{ form.errors.body }}</p>
        <div class="flex flex-wrap items-center justify-between gap-3">
          <p class="inline-flex items-center gap-1.5 text-xs text-bible-muted">
            <BookOpen class="h-3.5 w-3.5" aria-hidden="true" />
            Podés citar un versículo desde la lectura y compartirlo.
          </p>
          <button
            type="submit"
            class="inline-flex items-center gap-2 rounded-full bg-bible-accent px-5 py-2 text-sm font-medium text-bible-bg transition-colors hover:bg-bible-accent-bright disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bible-accent"
            :disabled="form.processing || !form.body.trim()"
          >
            <Sparkles v-if="!compact" class="h-4 w-4" aria-hidden="true" />
            {{ form.processing ? 'Publicando…' : submitLabel }}
          </button>
        </div>
      </div>
    </form>
  </section>
</template>