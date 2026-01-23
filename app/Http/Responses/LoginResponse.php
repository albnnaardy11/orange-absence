<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse | Redirector | \Symfony\Component\HttpFoundation\Response
    {
        $user = auth()->user();

        if ($user->is_suspended) {
            return response()->view('errors.account-suspended', [], 403);
        }

        if ($user->hasAnyRole(['super_admin', 'secretary'])) {
            return redirect()->intended('/admin');
        }

        if ($user->hasRole('member')) {
            return redirect()->intended('/member');
        }

        return redirect()->to('/');
    }
}
