import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Bible\SearchController::books
 * @see app/Http/Controllers/Bible/SearchController.php:36
 * @route '/api/bible/books-search'
 */
export const books = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: books.url(options),
    method: 'get',
})

books.definition = {
    methods: ["get","head"],
    url: '/api/bible/books-search',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Bible\SearchController::books
 * @see app/Http/Controllers/Bible/SearchController.php:36
 * @route '/api/bible/books-search'
 */
books.url = (options?: RouteQueryOptions) => {
    return books.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\SearchController::books
 * @see app/Http/Controllers/Bible/SearchController.php:36
 * @route '/api/bible/books-search'
 */
books.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: books.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Bible\SearchController::books
 * @see app/Http/Controllers/Bible/SearchController.php:36
 * @route '/api/bible/books-search'
 */
books.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: books.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Bible\SearchController::books
 * @see app/Http/Controllers/Bible/SearchController.php:36
 * @route '/api/bible/books-search'
 */
    const booksForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: books.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Bible\SearchController::books
 * @see app/Http/Controllers/Bible/SearchController.php:36
 * @route '/api/bible/books-search'
 */
        booksForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: books.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Bible\SearchController::books
 * @see app/Http/Controllers/Bible/SearchController.php:36
 * @route '/api/bible/books-search'
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
* @see \App\Http\Controllers\Bible\SearchController::structure
 * @see app/Http/Controllers/Bible/SearchController.php:65
 * @route '/api/bible/structure'
 */
export const structure = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: structure.url(options),
    method: 'get',
})

structure.definition = {
    methods: ["get","head"],
    url: '/api/bible/structure',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Bible\SearchController::structure
 * @see app/Http/Controllers/Bible/SearchController.php:65
 * @route '/api/bible/structure'
 */
structure.url = (options?: RouteQueryOptions) => {
    return structure.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\SearchController::structure
 * @see app/Http/Controllers/Bible/SearchController.php:65
 * @route '/api/bible/structure'
 */
structure.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: structure.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Bible\SearchController::structure
 * @see app/Http/Controllers/Bible/SearchController.php:65
 * @route '/api/bible/structure'
 */
structure.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: structure.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Bible\SearchController::structure
 * @see app/Http/Controllers/Bible/SearchController.php:65
 * @route '/api/bible/structure'
 */
    const structureForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: structure.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Bible\SearchController::structure
 * @see app/Http/Controllers/Bible/SearchController.php:65
 * @route '/api/bible/structure'
 */
        structureForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: structure.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Bible\SearchController::structure
 * @see app/Http/Controllers/Bible/SearchController.php:65
 * @route '/api/bible/structure'
 */
        structureForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: structure.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    structure.form = structureForm
/**
* @see \App\Http\Controllers\Bible\SearchController::resolve
 * @see app/Http/Controllers/Bible/SearchController.php:110
 * @route '/api/bible/resolve'
 */
export const resolve = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: resolve.url(options),
    method: 'get',
})

resolve.definition = {
    methods: ["get","head"],
    url: '/api/bible/resolve',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Bible\SearchController::resolve
 * @see app/Http/Controllers/Bible/SearchController.php:110
 * @route '/api/bible/resolve'
 */
resolve.url = (options?: RouteQueryOptions) => {
    return resolve.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\SearchController::resolve
 * @see app/Http/Controllers/Bible/SearchController.php:110
 * @route '/api/bible/resolve'
 */
resolve.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: resolve.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Bible\SearchController::resolve
 * @see app/Http/Controllers/Bible/SearchController.php:110
 * @route '/api/bible/resolve'
 */
resolve.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: resolve.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Bible\SearchController::resolve
 * @see app/Http/Controllers/Bible/SearchController.php:110
 * @route '/api/bible/resolve'
 */
    const resolveForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: resolve.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Bible\SearchController::resolve
 * @see app/Http/Controllers/Bible/SearchController.php:110
 * @route '/api/bible/resolve'
 */
        resolveForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: resolve.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Bible\SearchController::resolve
 * @see app/Http/Controllers/Bible/SearchController.php:110
 * @route '/api/bible/resolve'
 */
        resolveForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: resolve.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    resolve.form = resolveForm
const SearchController = { books, structure, resolve }

export default SearchController