<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuickLoginController extends Controller
{
    /**
     * Display the portal with login options.
     */
    public function index()
    {
        // If already logged in, redirect to respective dashboard
        // if (Auth::check()) {
        //     $user = Auth::user();
        //     if ($user->hasAnyRole(['super_admin', 'secretary'])) {
        //         return redirect('/admin');
        //     }
        //     return redirect('/member');
        // }

        return view('portal');
    }
}
