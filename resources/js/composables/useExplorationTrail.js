import { ref, watch } from 'vue'

const STORAGE_KEY = 'bible-trail'

const trail = ref(loadTrail())

function loadTrail() {
  try {
    const raw = sessionStorage.getItem(STORAGE_KEY)
    return raw ? JSON.parse(raw) : []
  } catch {
    return []
  }
}

function persist() {
  sessionStorage.setItem(STORAGE_KEY, JSON.stringify(trail.value))
}

export function useExplorationTrail() {
  function visit(node) {
    const entry = { book: node.book, chapter: node.chapter, verse: node.verse }
    const key = `${entry.book}-${entry.chapter}-${entry.verse}`

    // Si ya estás en este versículo (por ejemplo, recargaste la página), no duplicamos
    const last = trail.value[trail.value.length - 1]
    if (last && `${last.book}-${last.chapter}-${last.verse}` === key) return

    trail.value = [...trail.value, entry]
    persist()
  }

  function clear() {
    trail.value = []
    persist()
  }

  return { trail, visit, clear }
}