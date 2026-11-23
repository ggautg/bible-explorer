import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PostInteractionController::toggleLike
 * @see app/Http/Controllers/PostInteractionController.php:25
 * @route '/posts/{post}/like'
 */
export const toggleLike = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleLike.url(args, options),
    method: 'post',
})

toggleLike.definition = {
    methods: ["post"],
    url: '/posts/{post}/like',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PostInteractionController::toggleLike
 * @see app/Http/Controllers/PostInteractionController.php:25
 * @route '/posts/{post}/like'
 */
toggleLike.url = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return toggleLike.definition.url
            .replace('{post}', parsedArgs.post.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostInteractionController::toggleLike
 * @see app/Http/Controllers/PostInteractionController.php:25
 * @route '/posts/{post}/like'
 */
toggleLike.post = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleLike.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PostInteractionController::toggleLike
 * @see app/Http/Controllers/PostInteractionController.php:25
 * @route '/posts/{post}/like'
 */
    const toggleLikeForm = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggleLike.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PostInteractionController::toggleLike
 * @see app/Http/Controllers/PostInteractionController.php:25
 * @route '/posts/{post}/like'
 */
        toggleLikeForm.post = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggleLike.url(args, options),
            method: 'post',
        })
    
    toggleLike.form = toggleLikeForm
/**
* @see \App\Http\Controllers\PostInteractionController::toggleRebible
 * @see app/Http/Controllers/PostInteractionController.php:51
 * @route '/posts/{post}/rebiblear'
 */
export const toggleRebible = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleRebible.url(args, options),
    method: 'post',
})

toggleRebible.definition = {
    methods: ["post"],
    url: '/posts/{post}/rebiblear',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PostInteractionController::toggleRebible
 * @see app/Http/Controllers/PostInteractionController.php:51
 * @route '/posts/{post}/rebiblear'
 */
toggleRebible.url = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return toggleRebible.definition.url
            .replace('{post}', parsedArgs.post.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostInteractionController::toggleRebible
 * @see app/Http/Controllers/PostInteractionController.php:51
 * @route '/posts/{post}/rebiblear'
 */
toggleRebible.post = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleRebible.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PostInteractionController::toggleRebible
 * @see app/Http/Controllers/PostInteractionController.php:51
 * @route '/posts/{post}/rebiblear'
 */
    const toggleRebibleForm = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggleRebible.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PostInteractionController::toggleRebible
 * @see app/Http/Controllers/PostInteractionController.php:51
 * @route '/posts/{post}/rebiblear'
 */
        toggleRebibleForm.post = (args: { post: number | { id: number } } | [post: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggleRebible.url(args, options),
            method: 'post',
        })
    
    toggleRebible.form = toggleRebibleForm
const PostInteractionController = { toggleLike, toggleRebible }

export default PostInteractionController