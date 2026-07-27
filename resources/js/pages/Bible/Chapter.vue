<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useNotes } from '@/composables/useNotes'
import NavHeader from '@/components/Bible/NavHeader.vue'
import { useToast } from '@/composables/useToast'
import ToastContainer from '@/components/Bible/ToastContainer.vue'

const props = defineProps({
  book: Object,
  chapter: Number,
  verses: Array,
  alternateVerses: Object,
  hasNext: Boolean,
  hasPrev: Boolean,
})

const { showToast } = useToast()

const showComparison = ref(false)

const { noteFor, saveNote, deleteNote } = useNotes()

import { useFavorites } from '@/composables/useFavorites'

const { isFavorite, toggleFavorite } = useFavorites()

const copiedVerse = ref(null)

async function shareVerse(v) {
  const shareUrl = `${window.location.origin}/compartido/${props.book.code}/${props.chapter}/${v.verse}`
  const shareText = `"${v.text}"\n${props.book.name} ${props.chapter}:${v.verse}\n\n${shareUrl}`

  await navigator.clipboard.writeText(shareText)
  showToast('Versículo copiado al portapapeles')
}


function favoriteData(v) {
  return { book_code: props.book.code, bookName: props.book.name, chapter: props.chapter, verse: v.verse, text: v.text }
}

const openNoteVerse = ref(null)
const draftText = ref('')

function toggleNoteBox(v) {
  if (openNoteVerse.value === v.verse) {
    openNoteVerse.value = null
    return
  }
  openNoteVerse.value = v.verse
  const existing = noteFor(favoriteData(v))
  draftText.value = existing ? existing.body : ''
}

async function submitNote(v) {
  if (!draftText.value.trim()) return
  await saveNote(favoriteData(v), draftText.value.trim())
  openNoteVerse.value = null
}

async function removeNote(v) {
  await deleteNote(favoriteData(v))
  openNoteVerse.value = null
}
</script>

<template>

  <Head :title="`${book.name} ${chapter}`" />
  <div class="bg-bible-bg min-h-screen text-bible-text p-4 md:p-8 font-bible-body">
    <NavHeader :back-href="`/leer/${book.code}`" :back-label="`← ${book.name}`">
      <h1 class="font-bible-display font-semibold text-2xl md:text-3xl text-bible-accent-bright mt-2">
        {{ book.name }} {{ chapter }}
      </h1>
    </NavHeader>

    <button @click="showComparison = !showComparison" class="text-bible-accent text-sm hover:underline mb-4 block">
      {{ showComparison ? 'Ocultar comparación' : 'Comparar con Palabra de Dios para ti →' }}
    </button>

    <div class="max-w-2xl font-bible-display text-lg md:text-xl leading-relaxed">
      <div v-for="v in verses" :key="v.verse" class="mb-3.5 group">
        <p class="relative">
          <span class="text-bible-accent font-bible-body text-xs align-super mr-1">{{ v.verse }}</span>
          {{ v.text }}
          <button @click="toggleNoteBox(v)" title="Nota personal" class="text-sm ml-1.5 transition-opacity"
            :class="noteFor(favoriteData(v)) ? 'text-bible-accent-bright opacity-100' : 'text-bible-muted-dim opacity-100 md:opacity-0 md:group-hover:opacity-100 hover:text-bible-accent-bright'">
            ✎
          </button>
          <button @click="toggleFavorite(favoriteData(v))"
            :title="isFavorite(favoriteData(v)) ? 'Quitar de favoritos' : 'Guardar en favoritos'"
            class="text-sm ml-1 transition-opacity"
            :class="isFavorite(favoriteData(v)) ? 'text-bible-accent-bright opacity-100' : 'text-bible-muted-dim opacity-100 md:opacity-0 md:group-hover:opacity-100 hover:text-bible-accent-bright'">
            ★
          </button>
          <Link :href="`/bible/${book.code}/${chapter}/${v.verse}`" title="Ver conexiones"
            class="text-bible-muted-dim no-underline text-sm ml-1 opacity-0 group-hover:opacity-100 hover:text-bible-accent-bright transition-opacity">
            ✦
          </Link>
          <button @click="shareVerse(v)" title="Compartir" class="text-sm ml-1 transition-opacity"
            :class="copiedVerse === v.verse ? 'text-bible-accent-bright opacity-100' : 'text-bible-muted-dim opacity-100 md:opacity-0 md:group-hover:opacity-100 hover:text-bible-accent-bright'">
            {{ copiedVerse === v.verse ? '✓' : '⇗' }}
          </button>
        </p>
        <p v-if="showComparison && alternateVerses[v.verse]"
          class="text-bible-muted text-base font-bible-body italic mt-1 pl-4 border-l-2 border-bible-border">
          {{ alternateVerses[v.verse] }}
        </p>


        <div v-if="openNoteVerse === v.verse" class="mt-2 bg-bible-panel border border-bible-border rounded-md p-3">
          <textarea v-model="draftText" rows="3" placeholder="Tu reflexión sobre este versículo..."
            class="w-full bg-transparent text-bible-text text-sm font-bible-body placeholder:text-bible-muted-dim resize-none focus:outline-none"></textarea>
          <div class="flex justify-between items-center mt-2">
            <button v-if="noteFor(favoriteData(v))" @click="removeNote(v)"
              class="text-bible-error text-xs hover:underline">
              Borrar nota
            </button>
            <span v-else></span>
            <button @click="submitNote(v)"
              class="bg-bible-accent text-bible-bg text-xs px-3 py-1.5 rounded hover:bg-bible-accent-bright">
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>

    <nav class="flex justify-between max-w-2xl mt-10">
      <Link v-if="hasPrev" :href="`/leer/${book.code}/${chapter - 1}`"
        class="text-bible-accent text-sm hover:underline">
        ← Capítulo anterior
      </Link>
      <span v-else></span>
      <Link v-if="hasNext" :href="`/leer/${book.code}/${chapter + 1}`"
        class="text-bible-accent text-sm hover:underline">
        Capítulo siguiente →
      </Link>
    </nav>
  </div>
  <ToastContainer />
</template>