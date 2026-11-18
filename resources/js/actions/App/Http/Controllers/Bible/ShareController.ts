import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Bible\ShareController::show
 * @see app/Http/Controllers/Bible/ShareController.php:18
 * @route '/compartido/{book}/{chapter}/{verse}'
 */
export const show = (args: { book: string | number, chapter: string | number, verse: string | number } | [book: string | number, chapter: string | number, verse: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/compartido/{book}/{chapter}/{verse}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Bible\ShareController::show
 * @see app/Http/Controllers/Bible/ShareController.php:18
 * @route '/compartido/{book}/{chapter}/{verse}'
 */
show.url = (args: { book: string | number, chapter: string | number, verse: string | number } | [book: string | number, chapter: string | number, verse: string | number ], options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{book}', parsedArgs.book.toString())
            .replace('{chapter}', parsedArgs.chapter.toString())
            .replace('{verse}', parsedArgs.verse.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\ShareController::show
 * @see app/Http/Controllers/Bible/ShareController.php:18
 * @route '/compartido/{book}/{chapter}/{verse}'
 */
show.get = (args: { book: string | number, chapter: string | number, verse: string | number } | [book: string | number, chapter: string | number, verse: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Bible\ShareController::show
 * @see app/Http/Controllers/Bible/ShareController.php:18
 * @route '/compartido/{book}/{chapter}/{verse}'
 */
show.head = (args: { book: string | number, chapter: string | number, verse: string | number } | [book: string | number, chapter: string | number, verse: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Bible\ShareController::show
 * @see app/Http/Controllers/Bible/ShareController.php:18
 * @route '/compartido/{book}/{chapter}/{verse}'
 */
    const showForm = (args: { book: string | number, chapter: string | number, verse: string | number } | [book: string | number, chapter: string | number, verse: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Bible\ShareController::show
 * @see app/Http/Controllers/Bible/ShareController.php:18
 * @route '/compartido/{book}/{chapter}/{verse}'
 */
        showForm.get = (args: { book: string | number, chapter: string | number, verse: string | number } | [book: string | number, chapter: string | number, verse: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Bible\ShareController::show
 * @see app/Http/Controllers/Bible/ShareController.php:18
 * @route '/compartido/{book}/{chapter}/{verse}'
 */
        showForm.head = (args: { book: string | number, chapter: string | number, verse: string | number } | [book: string | number, chapter: string | number, verse: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
const ShareController = { show }

export default ShareController