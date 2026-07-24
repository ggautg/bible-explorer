import { ref } from 'vue'
import axios from 'axios'

const favorites = ref([])
const loaded = ref(false)

function keyOf(v) {
  return `${v.book_code}-${v.chapter}-${v.verse}`
}

async function loadFavorites() {
  const { data } = await axios.get('/api/favorites')
  favorites.value = data
  loaded.value = true
}

export function useFavorites() {
  if (typeof window !== 'undefined' && !loaded.value) {
    loadFavorites()
  }

  function isFavorite(v) {
    return favorites.value.some(f => keyOf(f) === keyOf(v))
  }

  async function toggleFavorite(v) {
    if (isFavorite(v)) {
      await axios.delete('/api/favorites', {
        data: { book_code: v.book_code, chapter: v.chapter, verse: v.verse },
      })
      favorites.value = favorites.value.filter(f => keyOf(f) !== keyOf(v))
    } else {
      const { data } = await axios.post('/api/favorites', {
        book_code: v.book_code,
        book_name: v.bookName,
        chapter: v.chapter,
        verse: v.verse,
        text: v.text,
      })
      favorites.value = [...favorites.value, data]
    }
  }

  return { favorites, isFavorite, toggleFavorite, loaded }
}