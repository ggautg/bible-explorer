<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controlador para seguir/dejar de seguir usuarios.
 */
class FollowController extends Controller
{
    /**
     * Alterna el estado de seguimiento entre el usuario autenticado y el perfil.
     *
     * @param  Request  $request  Solicitud autenticada.
     * @param  User  $user  Usuario a seguir o dejar de seguir.
     * @return RedirectResponse Redirección atrás con notificación.
     */
    public function toggle(Request $request, User $user): RedirectResponse
    {
        $viewer = $request->user();

        if ($viewer->id === $user->id) {
            return back();
        }

        if ($viewer->isFollowing($user)) {
            $viewer->unfollow($user);
            $message = "Dejaste de seguir a {$user->name}.";
        } else {
            $viewer->follow($user);
            $message = "Ahora seguís a {$user->name}.";
        }

        return back()->with('toast', ['type' => 'success', 'message' => $message]);
    }
}
