<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class AuthRedirectController extends Controller
{
    #[OA\Get(
        path: "/secretary",
        operationId: "secretaryLoginRedirect",
        tags: ["Authentication"],
        summary: "Secretary Login Redirect",
        responses: [
            new OA\Response(response: 302, description: "Redirect to login")
        ]
    )]
    public function secretaryLogin()
    {
        return redirect()->to('/admin/login');
    }

    #[OA\Get(
        path: "/login",
        operationId: "loginRedirect",
        tags: ["Authentication"],
        summary: "Login Redirect",
        responses: [
            new OA\Response(response: 302, description: "Redirect to login")
        ]
    )]
    public function login()
    {
        return redirect()->to('/admin/login');
    }

    #[OA\Post(
        path: "/logout",
        operationId: "logout",
        tags: ["Authentication"],
        summary: "Logout",
        responses: [
            new OA\Response(response: 302, description: "Logged out")
        ]
    )]
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
