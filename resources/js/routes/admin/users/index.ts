import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminController::role
 * @see app/Http/Controllers/AdminController.php:78
 * @route '/admin/usuarios/{user}/rol'
 */
export const role = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: role.url(args, options),
    method: 'post',
})

role.definition = {
    methods: ["post"],
    url: '/admin/usuarios/{user}/rol',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AdminController::role
 * @see app/Http/Controllers/AdminController.php:78
 * @route '/admin/usuarios/{user}/rol'
 */
role.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return role.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\AdminController::role
 * @see app/Http/Controllers/AdminController.php:78
 * @route '/admin/usuarios/{user}/rol'
 */
role.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: role.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AdminController::role
 * @see app/Http/Controllers/AdminController.php:78
 * @route '/admin/usuarios/{user}/rol'
 */
    const roleForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: role.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AdminController::role
 * @see app/Http/Controllers/AdminController.php:78
 * @route '/admin/usuarios/{user}/rol'
 */
        roleForm.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: role.url(args, options),
            method: 'post',
        })
    
    role.form = roleForm
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
const users = {
    role: Object.assign(role, role),
ban: Object.assign(ban, ban),
unban: Object.assign(unban, unban),
}

export default users