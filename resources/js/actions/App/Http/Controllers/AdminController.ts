import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminController::index
 * @see app/Http/Controllers/AdminController.php:20
 * @route '/admin'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AdminController::index
 * @see app/Http/Controllers/AdminController.php:20
 * @route '/admin'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::index
 * @see app/Http/Controllers/AdminController.php:20
 * @route '/admin'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AdminController::index
 * @see app/Http/Controllers/AdminController.php:20
 * @route '/admin'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AdminController::index
 * @see app/Http/Controllers/AdminController.php:20
 * @route '/admin'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AdminController::index
 * @see app/Http/Controllers/AdminController.php:20
 * @route '/admin'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AdminController::index
 * @see app/Http/Controllers/AdminController.php:20
 * @route '/admin'
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
* @see \App\Http\Controllers\AdminController::toggleRole
 * @see app/Http/Controllers/AdminController.php:78
 * @route '/admin/usuarios/{user}/rol'
 */
export const toggleRole = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleRole.url(args, options),
    method: 'post',
})

toggleRole.definition = {
    methods: ["post"],
    url: '/admin/usuarios/{user}/rol',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminController::toggleRole
 * @see app/Http/Controllers/AdminController.php:78
 * @route '/admin/usuarios/{user}/rol'
 */
toggleRole.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { user: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.id
                : args.user,
                }

    return toggleRole.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::toggleRole
 * @see app/Http/Controllers/AdminController.php:78
 * @route '/admin/usuarios/{user}/rol'
 */
toggleRole.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleRole.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AdminController::toggleRole
 * @see app/Http/Controllers/AdminController.php:78
 * @route '/admin/usuarios/{user}/rol'
 */
    const toggleRoleForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggleRole.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AdminController::toggleRole
 * @see app/Http/Controllers/AdminController.php:78
 * @route '/admin/usuarios/{user}/rol'
 */
        toggleRoleForm.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggleRole.url(args, options),
            method: 'post',
        })
    
    toggleRole.form = toggleRoleForm
/**
* @see \App\Http\Controllers\AdminController::ban
 * @see app/Http/Controllers/AdminController.php:90
 * @route '/admin/usuarios/{user}/ban'
 */
export const ban = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: ban.url(args, options),
    method: 'post',
})

ban.definition = {
    methods: ["post"],
    url: '/admin/usuarios/{user}/ban',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminController::ban
 * @see app/Http/Controllers/AdminController.php:90
 * @route '/admin/usuarios/{user}/ban'
 */
ban.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { user: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.id
                : args.user,
                }

    return ban.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::ban
 * @see app/Http/Controllers/AdminController.php:90
 * @route '/admin/usuarios/{user}/ban'
 */
ban.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: ban.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AdminController::ban
 * @see app/Http/Controllers/AdminController.php:90
 * @route '/admin/usuarios/{user}/ban'
 */
    const banForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: ban.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AdminController::ban
 * @see app/Http/Controllers/AdminController.php:90
 * @route '/admin/usuarios/{user}/ban'
 */
        banForm.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: ban.url(args, options),
            method: 'post',
        })
    
    ban.form = banForm
/**
* @see \App\Http\Controllers\AdminController::unban
 * @see app/Http/Controllers/AdminController.php:102
 * @route '/admin/usuarios/{user}/desbanear'
 */
export const unban = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: unban.url(args, options),
    method: 'post',
})

unban.definition = {
    methods: ["post"],
    url: '/admin/usuarios/{user}/desbanear',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminController::unban
 * @see app/Http/Controllers/AdminController.php:102
 * @route '/admin/usuarios/{user}/desbanear'
 */
unban.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { user: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.id
                : args.user,
                }

    return unban.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::unban
 * @see app/Http/Controllers/AdminController.php:102
 * @route '/admin/usuarios/{user}/desbanear'
 */
unban.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: unban.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AdminController::unban
 * @see app/Http/Controllers/AdminController.php:102
 * @route '/admin/usuarios/{user}/desbanear'
 */
    const unbanForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: unban.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AdminController::unban
 * @see app/Http/Controllers/AdminController.php:102
 * @route '/admin/usuarios/{user}/desbanear'
 */
        unbanForm.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: unban.url(args, options),
            method: 'post',
        })
    
    unban.form = unbanForm
/**
* @see \App\Http\Controllers\AdminController::resolveReport
 * @see app/Http/Controllers/AdminController.php:112
 * @route '/admin/reportes/{report}/resolver'
 */
export const resolveReport = (args: { report: number | { id: number } } | [report: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resolveReport.url(args, options),
    method: 'post',
})

resolveReport.definition = {
    methods: ["post"],
    url: '/admin/reportes/{report}/resolver',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminController::resolveReport
 * @see app/Http/Controllers/AdminController.php:112
 * @route '/admin/reportes/{report}/resolver'
 */
resolveReport.url = (args: { report: number | { id: number } } | [report: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { report: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { report: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    report: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        report: typeof args.report === 'object'
                ? args.report.id
                : args.report,
                }

    return resolveReport.definition.url
            .replace('{report}', parsedArgs.report.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::resolveReport
 * @see app/Http/Controllers/AdminController.php:112
 * @route '/admin/reportes/{report}/resolver'
 */
resolveReport.post = (args: { report: number | { id: number } } | [report: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resolveReport.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AdminController::resolveReport
 * @see app/Http/Controllers/AdminController.php:112
 * @route '/admin/reportes/{report}/resolver'
 */
    const resolveReportForm = (args: { report: number | { id: number } } | [report: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: resolveReport.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AdminController::resolveReport
 * @see app/Http/Controllers/AdminController.php:112
 * @route '/admin/reportes/{report}/resolver'
 */
        resolveReportForm.post = (args: { report: number | { id: number } } | [report: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: resolveReport.url(args, options),
            method: 'post',
        })
    
    resolveReport.form = resolveReportForm
/**
* @see \App\Http\Controllers\AdminController::destroyPost
 * @see app/Http/Controllers/AdminController.php:143
 * @route '/admin/posts/{post}'
 */
export const destroyPost = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyPost.url(args, options),
    method: 'delete',
})

destroyPost.definition = {
    methods: ["delete"],
    url: '/admin/posts/{post}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\AdminController::destroyPost
 * @see app/Http/Controllers/AdminController.php:143
 * @route '/admin/posts/{post}'
 */
destroyPost.url = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { post: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { post: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    post: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        post: typeof args.post === 'object'
                ? args.post.id
                : args.post,
                }

    return destroyPost.definition.url
            .replace('{post}', parsedArgs.post.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::destroyPost
 * @see app/Http/Controllers/AdminController.php:143
 * @route '/admin/posts/{post}'
 */
destroyPost.delete = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyPost.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\AdminController::destroyPost
 * @see app/Http/Controllers/AdminController.php:143
 * @route '/admin/posts/{post}'
 */
    const destroyPostForm = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyPost.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AdminController::destroyPost
 * @see app/Http/Controllers/AdminController.php:143
 * @route '/admin/posts/{post}'
 */
        destroyPostForm.delete = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyPost.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyPost.form = destroyPostForm
const AdminController = { index, toggleRole, ban, unban, resolveReport, destroyPost }

export default AdminController