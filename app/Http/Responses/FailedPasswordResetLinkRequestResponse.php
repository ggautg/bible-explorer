<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Http\Responses\FailedPasswordResetLinkRequestResponse as FortifyFailedResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Respuesta de Fortify que no revela si el correo existe en la base.
 */
class FailedPasswordResetLinkRequestResponse extends FortifyFailedResponse
{
    /**
     * Renderiza la respuesta sin distinguir cuentas inexistentes.
     *
     * @param  Request  $request  Solicitud actual.
     * @return Response
     */
    public function toResponse($request)
    {
        if ($this->status === Password::INVALID_USER) {
            $message = trans(Password::RESET_LINK_SENT);

            return $request->wantsJson()
                ? new JsonResponse(['message' => $message], 200)
                : back()->with('status', $message);
        }

        return parent::toResponse($request);
    }
}
