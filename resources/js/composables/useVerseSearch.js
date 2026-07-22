import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

export function useVerseSearch() {
  const searchQuery = ref('')
  const suggestions = ref([])
  const searchError = ref('')
  let debounceTimer = null

  watch(searchQuery, (value) => {
    clearTimeout(debounceTimer)
    searchError.value = ''

    if (/\d+:\d+$/.test(value.trim())) {
      suggestions.value = []
      return
    }

    if (value.trim().length < 1) {
      suggestions.value = []
      return
    }

    debounceTimer = setTimeout(async () => {
      const res = await fetch(`/api/bible/books-search?q=${encodeURIComponent(value)}`)
      suggestions.value = await res.json()
    }, 200)
  })

  function pickSuggestion(book) {
    searchQuery.value = `${book.name} `
    suggestions.value = []
  }

  async function submitSearch() {
    if (!searchQuery.value.trim()) return

    const res = await fetch(`/api/bible/resolve?ref=${encodeURIComponent(searchQuery.value)}`)
    const data = await res.json()

    if (!res.ok) {
      searchError.value = data.error
      return
    }

    router.visit(`/bible/${data.book}/${data.chapter}/${data.verse}`)
  }

  return { searchQuery, suggestions, searchError, pickSuggestion, submitSearch }
}