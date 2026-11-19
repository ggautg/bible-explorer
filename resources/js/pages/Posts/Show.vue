<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { MessageCircle } from '@lucide/vue';
import BiblePageShell from '@/components/Bible/BiblePageShell.vue';
import PostComposer from '@/components/Social/PostComposer.vue';
import PostCard from '@/components/Social/PostCard.vue';

/**
 * Detalle de una publicación con su hilo de respuestas.
 */
const props = defineProps({
  /** Publicación principal. */
  post: { type: Object, required: true },
  /** Respuestas a la publicación. */
  replies: { type: Array, default: () => [] },
});
</script>

<template>
  <Head title="Publicación" />

  <BiblePageShell back-href="/comunidad" back-label="← Comunidad" max-width-class="max-w-3xl">
    <template #header>
      <div>
        <h1 class="font-bible-display text-2xl font-semibold text-bible-accent-bright">Publicación</h1>
        <p v-if="post.author?.name" class="text-sm text-bible-muted">
          De
          <Link
            v-if="post.author.slug"
            :href="`/perfil/${post.author.slug}`"
            class="text-bible-accent-bright hover:underline"
          >{{ post.author.name }}</Link>
          <span v-else>{{ post.author.name }}</span>
        </p>
      </div>
    </template>

    <div class="space-y-5">
      <PostCard :post="post" />

      <section class="rounded-2xl border border-bible-border bg-bible-panel/60 p-4 md:p-5" aria-label="Responder">
        <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-bible-accent">
          <MessageCircle class="h-4 w-4" aria-hidden="true" />
          Responder
        </h2>
        <PostComposer
          :action="`/posts/${post.id}/responder`"
          placeholder="Escribí tu respuesta…"
          submit-label="Responder"
          compact
        />
      </section>

      <section v-if="replies.length" class="space-y-4" aria-label="Respuestas">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-bible-muted">Respuestas ({{ replies.length }})</h2>
        <PostCard v-for="reply in replies" :key="reply.id" :post="reply" />
      </section>

      <div
        v-else
        class="rounded-2xl border border-dashed border-bible-border/70 bg-bible-panel/40 px-6 py-10 text-center"
      >
        <p class="text-sm text-bible-muted">Todavía no hay respuestas. ¡Sé el primero en responder!</p>
      </div>
    </div>
  </BiblePageShell>
</template>
