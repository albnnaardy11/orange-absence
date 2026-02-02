<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class QuickLoginController extends Controller
{
    #[OA\Get(
        path: "/",
        operationId: "getPortal",
        tags: ["Portal"],
        summary: "Display the portal",
        description: "Returns the portal view or redirects to dashboard if authenticated.",
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 302, description: "Redirect to dashboard")
        ]
    )]
    public function index()
    {
        // If already logged in, redirect to respective dashboard
        if (Auth::check()) {
            $user = Auth::user();
            
            if ($user->is_suspended) {
                return response()->view('errors.account-suspended', [], 403);
            }

            if ($user->hasAnyRole(['super_admin', 'secretary'])) {
                return redirect('/admin');
            }
            return redirect('/member');
        }

        return view('portal');
    }
}
