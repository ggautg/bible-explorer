<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // La clave única (follower_id, followed_id) permite alternar sin
        // check-then-write: borrar primero e insertar con ignore evita duplicados.
        $removed = DB::table('follows')
            ->where('follower_id', $viewer->id)
            ->where('followed_id', $user->id)
            ->delete();

        if ($removed > 0) {
            $message = "Dejaste de seguir a {$user->name}.";
        } else {
            DB::table('follows')->insertOrIgnore([
                'follower_id' => $viewer->id,
                'followed_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $message = "Ahora seguís a {$user->name}.";
        }

        return back()->with('toast', ['type' => 'success', 'message' => $message]);
    }
}
