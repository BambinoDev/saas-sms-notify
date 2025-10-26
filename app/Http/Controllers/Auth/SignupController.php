<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class SignupController extends Controller
{
    /**
     * Afficher le formulaire d'inscription
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Signup', [
            'countries' => $this->getCountries(),
        ]);
    }

    /**
     * Traiter l'inscription (créer organisation + utilisateur)
     */
    public function store(Request $request): RedirectResponse
    {
        // Validation
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'organization_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'size:2'], // Code pays ISO (ex: CI)
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ], [
            'name.required' => 'Votre nom est requis.',
            'organization_name.required' => 'Le nom de l\'organisation est requis.',
            'country.required' => 'Le pays est requis.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.required' => 'Le mot de passe est requis.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'terms.accepted' => 'Vous devez accepter les conditions d\'utilisation.',
        ]);

        try {
            DB::beginTransaction();

            // 1. Créer l'organisation
            $organization = Organization::create([
                'name' => $validated['organization_name'],
                'slug' => Str::slug($validated['organization_name']) . '-' . Str::random(6),
                'primary_country' => $validated['country'],
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(14), // 14 jours de trial
                'timezone' => $this->getTimezoneForCountry($validated['country']),
                'onboarding_completed' => false,
                'onboarding_step' => 1,
            ]);

            // 2. Créer l'utilisateur admin
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(), // Auto-vérifier pour simplifier
            ]);

            // 3. Associer l'utilisateur à l'organisation via la table pivot
            $organization->users()->attach($user->id);

            DB::commit();

            // 4. Connecter l'utilisateur
            Auth::login($user);

            // 5. Rediriger vers l'onboarding
            return redirect()->route('onboarding.welcome')
                ->with('success', 'Bienvenue ! Configurons votre compte.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['error' => 'Une erreur est survenue. Veuillez réessayer.']);
        }
    }

    /**
     * Liste des pays africains supportés
     */
    private function getCountries(): array
    {
        return [
            ['code' => 'CI', 'name' => 'Côte d\'Ivoire', 'flag' => '🇨🇮'],
            ['code' => 'SN', 'name' => 'Sénégal', 'flag' => '🇸🇳'],
            ['code' => 'ML', 'name' => 'Mali', 'flag' => '🇲🇱'],
            ['code' => 'BF', 'name' => 'Burkina Faso', 'flag' => '🇧🇫'],
            ['code' => 'BJ', 'name' => 'Bénin', 'flag' => '🇧🇯'],
            ['code' => 'TG', 'name' => 'Togo', 'flag' => '🇹🇬'],
            ['code' => 'NE', 'name' => 'Niger', 'flag' => '🇳🇪'],
            ['code' => 'GH', 'name' => 'Ghana', 'flag' => '🇬🇭'],
            ['code' => 'NG', 'name' => 'Nigeria', 'flag' => '🇳🇬'],
        ];
    }

    /**
     * Obtenir le timezone par défaut pour un pays
     */
    private function getTimezoneForCountry(string $countryCode): string
    {
        $timezones = [
            'CI' => 'Africa/Abidjan',
            'SN' => 'Africa/Dakar',
            'ML' => 'Africa/Bamako',
            'BF' => 'Africa/Ouagadougou',
            'BJ' => 'Africa/Porto-Novo',
            'TG' => 'Africa/Lome',
            'NE' => 'Africa/Niamey',
            'GH' => 'Africa/Accra',
            'NG' => 'Africa/Lagos',
        ];

        return $timezones[$countryCode] ?? 'Africa/Abidjan';
    }
}
