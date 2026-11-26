<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador de configuración de perfil.
 *
 * Permite ver, actualizar y eliminar la información básica del usuario.
 */
class ProfileController extends Controller
{
    /**
     * Muestra la página de configuración del perfil.
     *
     * @param  Request  $request  Solicitud autenticada.
     * @return Response Página Inertia de perfil.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Actualiza el nombre y correo electrónico del usuario.
     *
     * Si cambia el correo, se invalida la verificación para exigir una nueva.
     *
     * @param  ProfileUpdateRequest  $request  Datos validados del perfil.
     * @return RedirectResponse Redirección a la edición de perfil.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        $emailChanged = $request->user()->isDirty('email');

        if ($emailChanged) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if ($emailChanged) {
            $request->user()->sendEmailVerificationNotification();
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perfil actualizado correctamente.',
        ]);

        return to_route('profile.edit');
    }

    /**
     * Elimina la cuenta del usuario autenticado.
     *
     * Cierra sesión, borra el usuario e invalida la sesión.
     *
     * @param  ProfileDeleteRequest  $request  Solicitud con confirmación de contraseña.
     * @return RedirectResponse Redirección al inicio.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
