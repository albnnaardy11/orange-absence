<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LogoutResponse implements Responsable
{
    public function toResponse($request): Response
    {
        return redirect()->to('/');
    }
}
