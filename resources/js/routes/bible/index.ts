import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Bible\GraphController::graph
 * @see app/Http/Controllers/Bible/GraphController.php:32
 * @route '/bible/{book}/{chapter}/{verse}'
 */
export const graph = (args: { book: string | number, chapter: string | number, verse: string | number } | [book: string | number, chapter: string | number, verse: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: graph.url(args, options),
    method: 'get',
})

graph.definition = {
    methods: ["get","head"],
    url: '/bible/{book}/{chapter}/{verse}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Bible\GraphController::graph
 * @see app/Http/Controllers/Bible/GraphController.php:32
 * @route '/bible/{book}/{chapter}/{verse}'
 */
graph.url = (args: { book: string | number, chapter: string | number, verse: string | number } | [book: string | number, chapter: string | number, verse: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    book: args[0],
                    chapter: args[1],
                    verse: args[2],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        book: args.book,
                                chapter: args.chapter,
                                verse: args.verse,
                }

    return graph.definition.url
            .replace('{book}', parsedArgs.book.toString())
            .replace('{chapter}', parsedArgs.chapter.toString())
            .replace('{verse}', parsedArgs.verse.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\GraphController::graph
 * @see app/Http/Controllers/Bible/GraphController.php:32
 * @route '/bible/{book}/{chapter}/{verse}'
 */
graph.get = (args: { book: string | number, chapter: string | number, verse: string | number } | [book: string | number, chapter: string | number, verse: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: graph.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Bible\GraphController::graph
 * @see app/Http/Controllers/Bible/GraphController.php:32
 * @route '/bible/{book}/{chapter}/{verse}'
 */
graph.head = (args: { book: string | number, chapter: string | number, verse: string | number } | [book: string | number, chapter: string | number, verse: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: graph.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Bible\GraphController::graph
 * @see app/Http/Controllers/Bible/GraphController.php:32
 * @route '/bible/{book}/{chapter}/{verse}'
 */
    const graphForm = (args: { book: string | number, chapter: string | number, verse: string | number } | [book: string | number, chapter: string | number, verse: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: graph.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Bible\GraphController::graph
 * @see app/Http/Controllers/Bible/GraphController.php:32
 * @route '/bible/{book}/{chapter}/{verse}'
 */
        graphForm.get = (args: { book: string | number, chapter: string | number, verse: string | number } | [book: string | number, chapter: string | number, verse: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: graph.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Bible\GraphController::graph
 * @see app/Http/Controllers/Bible/GraphController.php:32
 * @route '/bible/{book}/{chapter}/{verse}'
 */
        graphForm.head = (args: { book: string | number, chapter: string | number, verse: string | number } | [book: string | number, chapter: string | number, verse: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: graph.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    graph.form = graphForm
/**
* @see \App\Http\Controllers\Bible\ReaderController::books
 * @see app/Http/Controllers/Bible/ReaderController.php:22
 * @route '/leer'
 */
export const books = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: books.url(options),
    method: 'get',
})

books.definition = {
    methods: ["get","head"],
    url: '/leer',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Bible\ReaderController::books
 * @see app/Http/Controllers/Bible/ReaderController.php:22
 * @route '/leer'
 */
books.url = (options?: RouteQueryOptions) => {
    return books.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\ReaderController::books
 * @see app/Http/Controllers/Bible/ReaderController.php:22
 * @route '/leer'
 */
books.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: books.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Bible\ReaderController::books
 * @see app/Http/Controllers/Bible/ReaderController.php:22
 * @route '/leer'
 */
books.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: books.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Bible\ReaderController::books
 * @see app/Http/Controllers/Bible/ReaderController.php:22
 * @route '/leer'
 */
    const booksForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: books.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Bible\ReaderController::books
 * @see app/Http/Controllers/Bible/ReaderController.php:22
 * @route '/leer'
 */
        booksForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: books.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Bible\ReaderController::books
 * @see app/Http/Controllers/Bible/ReaderController.php:22
 * @route '/leer'
 */
        booksForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: books.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    books.form = booksForm
/**
* @see \App\Http\Controllers\Bible\ReaderController::chapters
 * @see app/Http/Controllers/Bible/ReaderController.php:35
 * @route '/leer/{book}'
 */
export const chapters = (args: { book: string | number } | [book: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: chapters.url(args, options),
    method: 'get',
})

chapters.definition = {
    methods: ["get","head"],
    url: '/leer/{book}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Bible\ReaderController::chapters
 * @see app/Http/Controllers/Bible/ReaderController.php:35
 * @route '/leer/{book}'
 */
chapters.url = (args: { book: string | number } | [book: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { book: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    book: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        book: args.book,
                }

    return chapters.definition.url
            .replace('{book}', parsedArgs.book.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\ReaderController::chapters
 * @see app/Http/Controllers/Bible/ReaderController.php:35
 * @route '/leer/{book}'
 */
chapters.get = (args: { book: string | number } | [book: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: chapters.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Bible\ReaderController::chapters
 * @see app/Http/Controllers/Bible/ReaderController.php:35
 * @route '/leer/{book}'
 */
chapters.head = (args: { book: string | number } | [book: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: chapters.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Bible\ReaderController::chapters
 * @see app/Http/Controllers/Bible/ReaderController.php:35
 * @route '/leer/{book}'
 */
    const chaptersForm = (args: { book: string | number } | [book: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: chapters.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Bible\ReaderController::chapters
 * @see app/Http/Controllers/Bible/ReaderController.php:35
 * @route '/leer/{book}'
 */
        chaptersForm.get = (args: { book: string | number } | [book: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: chapters.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Bible\ReaderController::chapters
 * @see app/Http/Controllers/Bible/ReaderController.php:35
 * @route '/leer/{book}'
 */
        chaptersForm.head = (args: { book: string | number } | [book: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: chapters.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    chapters.form = chaptersForm
/**
* @see \App\Http\Controllers\Bible\ReaderController::chapter
 * @see app/Http/Controllers/Bible/ReaderController.php:63
 * @route '/leer/{book}/{chapter}'
 */
export const chapter = (args: { book: string | number, chapter: string | number } | [book: string | number, chapter: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: chapter.url(args, options),
    method: 'get',
})

chapter.definition = {
    methods: ["get","head"],
    url: '/leer/{book}/{chapter}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Bible\ReaderController::chapter
 * @see app/Http/Controllers/Bible/ReaderController.php:63
 * @route '/leer/{book}/{chapter}'
 */
chapter.url = (args: { book: string | number, chapter: string | number } | [book: string | number, chapter: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    book: args[0],
                    chapter: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        book: args.book,
                                chapter: args.chapter,
                }

    return chapter.definition.url
            .replace('{book}', parsedArgs.book.toString())
            .replace('{chapter}', parsedArgs.chapter.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\ReaderController::chapter
 * @see app/Http/Controllers/Bible/ReaderController.php:63
 * @route '/leer/{book}/{chapter}'
 */
chapter.get = (args: { book: string | number, chapter: string | number } | [book: string | number, chapter: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: chapter.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Bible\ReaderController::chapter
 * @see app/Http/Controllers/Bible/ReaderController.php:63
 * @route '/leer/{book}/{chapter}'
 */
chapter.head = (args: { book: string | number, chapter: string | number } | [book: string | number, chapter: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: chapter.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Bible\ReaderController::chapter
 * @see app/Http/Controllers/Bible/ReaderController.php:63
 * @route '/leer/{book}/{chapter}'
 */
    const chapterForm = (args: { book: string | number, chapter: string | number } | [book: string | number, chapter: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: chapter.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Bible\ReaderController::chapter
 * @see app/Http/Controllers/Bible/ReaderController.php:63
 * @route '/leer/{book}/{chapter}'
 */
        chapterForm.get = (args: { book: string | number, chapter: string | number } | [book: string | number, chapter: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: chapter.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Bible\ReaderController::chapter
 * @see app/Http/Controllers/Bible/ReaderController.php:63
 * @route '/leer/{book}/{chapter}'
 */
        chapterForm.head = (args: { book: string | number, chapter: string | number } | [book: string | number, chapter: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: chapter.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    chapter.form = chapterForm
/**
* @see \App\Http\Controllers\Bible\ReaderController::favorites
 * @see app/Http/Controllers/Bible/ReaderController.php:108
 * @route '/favoritos'
 */
export const favorites = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: favorites.url(options),
    method: 'get',
})

favorites.definition = {
    methods: ["get","head"],
    url: '/favoritos',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Bible\ReaderController::favorites
 * @see app/Http/Controllers/Bible/ReaderController.php:108
 * @route '/favoritos'
 */
favorites.url = (options?: RouteQueryOptions) => {
    return favorites.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\ReaderController::favorites
 * @see app/Http/Controllers/Bible/ReaderController.php:108
 * @route '/favoritos'
 */
favorites.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: favorites.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Bible\ReaderController::favorites
 * @see app/Http/Controllers/Bible/ReaderController.php:108
 * @route '/favoritos'
 */
favorites.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: favorites.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Bible\ReaderController::favorites
 * @see app/Http/Controllers/Bible/ReaderController.php:108
 * @route '/favoritos'
 */
    const favoritesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: favorites.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Bible\ReaderController::favorites
 * @see app/Http/Controllers/Bible/ReaderController.php:108
 * @route '/favoritos'
 */
        favoritesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: favorites.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Bible\ReaderController::favorites
 * @see app/Http/Controllers/Bible/ReaderController.php:108
 * @route '/favoritos'
 */
        favoritesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: favorites.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    favorites.form = favoritesForm
/**
* @see \App\Http\Controllers\Bible\ReaderController::notes
 * @see app/Http/Controllers/Bible/ReaderController.php:120
 * @route '/notas'
 */
export const notes = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: notes.url(options),
    method: 'get',
})

notes.definition = {
    methods: ["get","head"],
    url: '/notas',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Bible\ReaderController::notes
 * @see app/Http/Controllers/Bible/ReaderController.php:120
 * @route '/notas'
 */
notes.url = (options?: RouteQueryOptions) => {
    return notes.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\ReaderController::notes
 * @see app/Http/Controllers/Bible/ReaderController.php:120
 * @route '/notas'
 */
notes.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: notes.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Bible\ReaderController::notes
 * @see app/Http/Controllers/Bible/ReaderController.php:120
 * @route '/notas'
 */
notes.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: notes.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Bible\ReaderController::notes
 * @see app/Http/Controllers/Bible/ReaderController.php:120
 * @route '/notas'
 */
    const notesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: notes.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Bible\ReaderController::notes
 * @see app/Http/Controllers/Bible/ReaderController.php:120
 * @route '/notas'
 */
        notesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: notes.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Bible\ReaderController::notes
 * @see app/Http/Controllers/Bible/ReaderController.php:120
 * @route '/notas'
 */
        notesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: notes.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    notes.form = notesForm
/**
* @see \App\Http\Controllers\Bible\TextSearchController::search
 * @see app/Http/Controllers/Bible/TextSearchController.php:28
 * @route '/buscar'
 */
export const search = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: search.url(options),
    method: 'get',
})

search.definition = {
    methods: ["get","head"],
    url: '/buscar',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Bible\TextSearchController::search
 * @see app/Http/Controllers/Bible/TextSearchController.php:28
 * @route '/buscar'
 */
search.url = (options?: RouteQueryOptions) => {
    return search.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\TextSearchController::search
 * @see app/Http/Controllers/Bible/TextSearchController.php:28
 * @route '/buscar'
 */
search.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: search.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Bible\TextSearchController::search
 * @see app/Http/Controllers/Bible/TextSearchController.php:28
 * @route '/buscar'
 */
search.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: search.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Bible\TextSearchController::search
 * @see app/Http/Controllers/Bible/TextSearchController.php:28
 * @route '/buscar'
 */
    const searchForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: search.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Bible\TextSearchController::search
 * @see app/Http/Controllers/Bible/TextSearchController.php:28
 * @route '/buscar'
 */
        searchForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: search.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Bible\TextSearchController::search
 * @see app/Http/Controllers/Bible/TextSearchController.php:28
 * @route '/buscar'
 */
        searchForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: search.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    search.form = searchForm
const bible = {
    graph: Object.assign(graph, graph),
books: Object.assign(books, books),
chapters: Object.assign(chapters, chapters),
chapter: Object.assign(chapter, chapter),
favorites: Object.assign(favorites, favorites),
notes: Object.assign(notes, notes),
search: Object.assign(search, search),
}

export default bible