import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\AdminController::role
 * @see app/Http/Controllers/AdminController.php:58
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
 * @see app/Http/Controllers/AdminController.php:58
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
 * @see app/Http/Controllers/AdminController.php:58
 * @route '/admin/usuarios/{user}/rol'
 */
role.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: role.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\AdminController::role
 * @see app/Http/Controllers/AdminController.php:58
 * @route '/admin/usuarios/{user}/rol'
 */
    const roleForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: role.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\AdminController::role
 * @see app/Http/Controllers/AdminController.php:58
 * @route '/admin/usuarios/{user}/rol'
 */
        roleForm.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: role.url(args, options),
            method: 'post',
        })
    
    role.form = roleForm
const users = {
    role: Object.assign(role, role),
}

export default users