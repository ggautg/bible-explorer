<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

/**
 * Controlador de configuración de seguridad.
 *
 * Gestiona la visualización de opciones de seguridad y la actualización de
 * la contraseña del usuario.
 */
class SecurityController extends Controller
{
    /**
     * Muestra la página de seguridad del usuario.
     *
     * Expone las capacidades y datos necesarios para gestionar contraseña,
     * verificación en dos pasos y llaves de acceso según las features activas.
     *
     * @param  TwoFactorAuthenticationRequest  $request  Solicitud validada para 2FA.
     * @return Response Página Inertia de seguridad.
     */
    public function edit(TwoFactorAuthenticationRequest $request): Response
    {
        $props = [
            'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),
            'canManagePasskeys' => Features::canManagePasskeys(),
            'passkeys' => Features::canManagePasskeys()
                ? $request->user()
                    ->passkeys()
                    ->select(['id', 'name', 'credential', 'created_at', 'last_used_at'])
                    ->latest()
                    ->get()
                    ->map(fn ($passkey) => [
                        'id' => $passkey->id,
                        'name' => $passkey->name,
                        'authenticator' => $passkey->authenticator,
                        'created_at_diff' => $passkey->created_at->diffForHumans(),
                        'last_used_at_diff' => $passkey->last_used_at?->diffForHumans(),
                    ])
                    ->values()
                    ->all()
                : [],
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ];

        if (Features::canManageTwoFactorAuthentication()) {
            $request->ensureStateIsValid();

            $props['twoFactorEnabled'] = $request->user()->hasEnabledTwoFactorAuthentication();
            $props['requiresConfirmation'] = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }

        return Inertia::render('settings/Security', $props);
    }

    /**
     * Actualiza la contraseña del usuario autenticado.
     *
     * @param  PasswordUpdateRequest  $request  Solicitud con la nueva contraseña validada.
     * @return RedirectResponse Redirección atrás con mensaje flash.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->password,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Contraseña actualizada correctamente.',
        ]);

        return back();
    }
}
