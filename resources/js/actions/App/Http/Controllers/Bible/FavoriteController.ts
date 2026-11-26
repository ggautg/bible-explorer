import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Bible\FavoriteController::index
 * @see app/Http/Controllers/Bible/FavoriteController.php:24
 * @route '/api/favorites'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/favorites',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Bible\FavoriteController::index
 * @see app/Http/Controllers/Bible/FavoriteController.php:24
 * @route '/api/favorites'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\FavoriteController::index
 * @see app/Http/Controllers/Bible/FavoriteController.php:24
 * @route '/api/favorites'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Bible\FavoriteController::index
 * @see app/Http/Controllers/Bible/FavoriteController.php:24
 * @route '/api/favorites'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Bible\FavoriteController::index
 * @see app/Http/Controllers/Bible/FavoriteController.php:24
 * @route '/api/favorites'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Bible\FavoriteController::index
 * @see app/Http/Controllers/Bible/FavoriteController.php:24
 * @route '/api/favorites'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Bible\FavoriteController::index
 * @see app/Http/Controllers/Bible/FavoriteController.php:24
 * @route '/api/favorites'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\Bible\FavoriteController::store
 * @see app/Http/Controllers/Bible/FavoriteController.php:41
 * @route '/api/favorites'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/favorites',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Bible\FavoriteController::store
 * @see app/Http/Controllers/Bible/FavoriteController.php:41
 * @route '/api/favorites'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\FavoriteController::store
 * @see app/Http/Controllers/Bible/FavoriteController.php:41
 * @route '/api/favorites'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Bible\FavoriteController::store
 * @see app/Http/Controllers/Bible/FavoriteController.php:41
 * @route '/api/favorites'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Bible\FavoriteController::store
 * @see app/Http/Controllers/Bible/FavoriteController.php:41
 * @route '/api/favorites'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Bible\FavoriteController::destroy
 * @see app/Http/Controllers/Bible/FavoriteController.php:72
 * @route '/api/favorites'
 */
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/favorites',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Bible\FavoriteController::destroy
 * @see app/Http/Controllers/Bible/FavoriteController.php:72
 * @route '/api/favorites'
 */
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\FavoriteController::destroy
 * @see app/Http/Controllers/Bible/FavoriteController.php:72
 * @route '/api/favorites'
 */
destroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Bible\FavoriteController::destroy
 * @see app/Http/Controllers/Bible/FavoriteController.php:72
 * @route '/api/favorites'
 */
    const destroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Bible\FavoriteController::destroy
 * @see app/Http/Controllers/Bible/FavoriteController.php:72
 * @route '/api/favorites'
 */
        destroyForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const FavoriteController = { index, store, destroy }

export default FavoriteController