import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Bible\NoteController::index
 * @see app/Http/Controllers/Bible/NoteController.php:24
 * @route '/api/notes'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/notes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Bible\NoteController::index
 * @see app/Http/Controllers/Bible/NoteController.php:24
 * @route '/api/notes'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\NoteController::index
 * @see app/Http/Controllers/Bible/NoteController.php:24
 * @route '/api/notes'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Bible\NoteController::index
 * @see app/Http/Controllers/Bible/NoteController.php:24
 * @route '/api/notes'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Bible\NoteController::index
 * @see app/Http/Controllers/Bible/NoteController.php:24
 * @route '/api/notes'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Bible\NoteController::index
 * @see app/Http/Controllers/Bible/NoteController.php:24
 * @route '/api/notes'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Bible\NoteController::index
 * @see app/Http/Controllers/Bible/NoteController.php:24
 * @route '/api/notes'
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
* @see \App\Http\Controllers\Bible\NoteController::upsert
 * @see app/Http/Controllers/Bible/NoteController.php:39
 * @route '/api/notes'
 */
export const upsert = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: upsert.url(options),
    method: 'put',
})

upsert.definition = {
    methods: ["put"],
    url: '/api/notes',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Bible\NoteController::upsert
 * @see app/Http/Controllers/Bible/NoteController.php:39
 * @route '/api/notes'
 */
upsert.url = (options?: RouteQueryOptions) => {
    return upsert.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\NoteController::upsert
 * @see app/Http/Controllers/Bible/NoteController.php:39
 * @route '/api/notes'
 */
upsert.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: upsert.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Bible\NoteController::upsert
 * @see app/Http/Controllers/Bible/NoteController.php:39
 * @route '/api/notes'
 */
    const upsertForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: upsert.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Bible\NoteController::upsert
 * @see app/Http/Controllers/Bible/NoteController.php:39
 * @route '/api/notes'
 */
        upsertForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: upsert.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    upsert.form = upsertForm
/**
* @see \App\Http\Controllers\Bible\NoteController::destroy
 * @see app/Http/Controllers/Bible/NoteController.php:74
 * @route '/api/notes'
 */
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/notes',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Bible\NoteController::destroy
 * @see app/Http/Controllers/Bible/NoteController.php:74
 * @route '/api/notes'
 */
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Bible\NoteController::destroy
 * @see app/Http/Controllers/Bible/NoteController.php:74
 * @route '/api/notes'
 */
destroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Bible\NoteController::destroy
 * @see app/Http/Controllers/Bible/NoteController.php:74
 * @route '/api/notes'
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
* @see \App\Http\Controllers\Bible\NoteController::destroy
 * @see app/Http/Controllers/Bible/NoteController.php:74
 * @route '/api/notes'
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
const NoteController = { index, upsert, destroy }

export default NoteController