import { ref } from 'vue'
import axios from 'axios'

const notes = ref([])
const loaded = ref(false)

function keyOf(v) {
  return `${v.book_code}-${v.chapter}-${v.verse}`
}

async function loadNotes() {
  const { data } = await axios.get('/api/notes')
  notes.value = data
  loaded.value = true
}

export function useNotes() {
  if (typeof window !== 'undefined' && !loaded.value) {
    loadNotes()
  }

  function noteFor(v) {
    return notes.value.find(n => keyOf(n) === keyOf(v))
  }

  async function saveNote(v, body) {
    const { data } = await axios.put('/api/notes', {
      book_code: v.book_code,
      book_name: v.bookName,
      chapter: v.chapter,
      verse: v.verse,
      body,
    })

    const index = notes.value.findIndex(n => keyOf(n) === keyOf(v))
    if (index >= 0) {
      notes.value[index] = data
    } else {
      notes.value = [...notes.value, data]
    }
  }

  async function deleteNote(v) {
    await axios.delete('/api/notes', {
      data: { book_code: v.book_code, chapter: v.chapter, verse: v.verse },
    })
    notes.value = notes.value.filter(n => keyOf(n) !== keyOf(v))
  }

  return { notes, noteFor, saveNote, deleteNote, loaded }
}