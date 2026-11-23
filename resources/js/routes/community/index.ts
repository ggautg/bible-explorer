import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\PostController::feed
 * @see app/Http/Controllers/PostController.php:28
 * @route '/comunidad'
 */
export const feed = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: feed.url(options),
    method: 'get',
})

feed.definition = {
    methods: ["get","head"],
    url: '/comunidad',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PostController::feed
 * @see app/Http/Controllers/PostController.php:28
 * @route '/comunidad'
 */
feed.url = (options?: RouteQueryOptions) => {
    return feed.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::feed
 * @see app/Http/Controllers/PostController.php:28
 * @route '/comunidad'
 */
feed.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: feed.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PostController::feed
 * @see app/Http/Controllers/PostController.php:28
 * @route '/comunidad'
 */
feed.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: feed.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PostController::feed
 * @see app/Http/Controllers/PostController.php:28
 * @route '/comunidad'
 */
    const feedForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: feed.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PostController::feed
 * @see app/Http/Controllers/PostController.php:28
 * @route '/comunidad'
 */
        feedForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: feed.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PostController::feed
 * @see app/Http/Controllers/PostController.php:28
 * @route '/comunidad'
 */
        feedForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: feed.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    feed.form = feedForm
const community = {
    feed: Object.assign(feed, feed),
}

export default community