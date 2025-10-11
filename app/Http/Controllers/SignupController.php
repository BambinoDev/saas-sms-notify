<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SignupController extends Controller
{
    public function store(Request $request)
    {
        // TODO: Logique de création tenant + user
        // Pour l'instant, on redirige juste
        return redirect()->route('onboarding.welcome');
    }
}
