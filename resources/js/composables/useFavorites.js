import { ref } from 'vue'

const STORAGE_KEY = 'bible-favorites'

const favorites = ref(loadFavorites())

function loadFavorites() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    return raw ? JSON.parse(raw) : []
  } catch {
    return []
  }
}

function persist() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(favorites.value))
}

function keyOf(v) {
  return `${v.book}-${v.chapter}-${v.verse}`
}

export function useFavorites() {
  function isFavorite(v) {
    return favorites.value.some(f => keyOf(f) === keyOf(v))
  }

  function toggleFavorite(v) {
    if (isFavorite(v)) {
      favorites.value = favorites.value.filter(f => keyOf(f) !== keyOf(v))
    } else {
      favorites.value = [...favorites.value, {
        book: v.book,
        bookName: v.bookName,
        chapter: v.chapter,
        verse: v.verse,
        text: v.text,
        savedAt: Date.now(),
      }]
    }
    persist()
  }

  return { favorites, isFavorite, toggleFavorite }
}