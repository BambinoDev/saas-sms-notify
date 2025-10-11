<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthController extends Controller
{
    /**
     * Afficher login admin
     */
    public function showLogin(): Response
    {
        return Inertia::render('Admin/Auth/Login');
    }

    /**
     * Traiter login admin
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Vérifier que c'est bien un superadmin
            if (!$user->is_superadmin) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Accès réservé aux administrateurs.',
                ]);
            }
            
            $request->session()->regenerate();
            
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'email' => 'Identifiants incorrects.',
        ])->onlyInput('email');
    }

    /**
     * Déconnexion admin
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login');
    }
}

