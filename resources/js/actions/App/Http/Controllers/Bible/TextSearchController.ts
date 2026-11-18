import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Bible\TextSearchController::search
 * @see app/Http/Controllers/Bible/TextSearchController.php:24
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
 * @see app/Http/Controllers/Bible/TextSearchController.php:24
 * @route '/buscar'
 */
search.url = (options?: RouteQueryOptions) => {
    return search.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\TextSearchController::search
 * @see app/Http/Controllers/Bible/TextSearchController.php:24
 * @route '/buscar'
 */
search.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: search.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Bible\TextSearchController::search
 * @see app/Http/Controllers/Bible/TextSearchController.php:24
 * @route '/buscar'
 */
search.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: search.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Bible\TextSearchController::search
 * @see app/Http/Controllers/Bible/TextSearchController.php:24
 * @route '/buscar'
 */
    const searchForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: search.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Bible\TextSearchController::search
 * @see app/Http/Controllers/Bible/TextSearchController.php:24
 * @route '/buscar'
 */
        searchForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: search.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Bible\TextSearchController::search
 * @see app/Http/Controllers/Bible/TextSearchController.php:24
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
const TextSearchController = { search }

export default TextSearchController