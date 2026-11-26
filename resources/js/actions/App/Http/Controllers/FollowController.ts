import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\FollowController::toggle
 * @see app/Http/Controllers/FollowController.php:22
 * @route '/usuarios/{user}/seguir'
 */
export const toggle = (args: { user: string | { slug: string } } | [user: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggle.url(args, options),
    method: 'post',
})

toggle.definition = {
    methods: ["post"],
    url: '/usuarios/{user}/seguir',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\FollowController::toggle
 * @see app/Http/Controllers/FollowController.php:22
 * @route '/usuarios/{user}/seguir'
 */
toggle.url = (args: { user: string | { slug: string } } | [user: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'slug' in args) {
            args = { user: args.slug }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.slug
                : args.user,
                }

    return toggle.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FollowController::toggle
 * @see app/Http/Controllers/FollowController.php:22
 * @route '/usuarios/{user}/seguir'
 */
toggle.post = (args: { user: string | { slug: string } } | [user: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggle.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\FollowController::toggle
 * @see app/Http/Controllers/FollowController.php:22
 * @route '/usuarios/{user}/seguir'
 */
    const toggleForm = (args: { user: string | { slug: string } } | [user: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggle.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\FollowController::toggle
 * @see app/Http/Controllers/FollowController.php:22
 * @route '/usuarios/{user}/seguir'
 */
        toggleForm.post = (args: { user: string | { slug: string } } | [user: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggle.url(args, options),
            method: 'post',
        })
    
    toggle.form = toggleForm
const FollowController = { toggle }

export default FollowController