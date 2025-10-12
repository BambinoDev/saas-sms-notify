<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class SignupController extends Controller
{
    /**
     * Store a newly created user and organization
     */
    public function store(Request $request)
    {
        // 1. VALIDATION
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'company_name' => ['required', 'string', 'max:255'],
            'country_iso' => ['required', 'string', 'size:2'], // Code ISO: CI, BF, ML, etc.
            'agree_terms' => ['required', 'accepted'],
        ], [
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'agree_terms.accepted' => 'Vous devez accepter les conditions d\'utilisation.',
            'country_iso.size' => 'Code pays invalide.',
        ]);

        try {
            DB::beginTransaction();

            // 2. CRÉER USER
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_superadmin' => false,
                // 'locale' => $this->getLocaleForCountry($validated['country_iso']), // Colonne locale n'existe pas
            ]);

            // 3. CRÉER ORGANIZATION
            $slug = $this->generateUniqueSlug($validated['company_name']);
            
            $organization = Organization::create([
                'name' => $validated['company_name'],
                'slug' => $slug,
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
                
                // Branding par défaut
                'primary_color' => '#3B82F6', // Bleu
                'secondary_color' => '#10B981', // Vert
                
                // Config téléphone par défaut selon pays
                'primary_country' => $validated['country_iso'],
                'timezone' => $this->getTimezoneForCountry($validated['country_iso']),
                'allowed_prefixes' => $this->getDefaultPrefixesForCountry($validated['country_iso']),
                'phone_validation_mode' => 'strict',
                'mobile_only' => true,
                'auto_format_e164' => true,
                
                // Onboarding tracking
                'onboarding_completed' => false,
                'onboarding_step' => 1,
            ]);

            // 4. CRÉER SUBSCRIPTION TRIAL
            Subscription::create([
                'organization_id' => $organization->id,
                'plan' => 'starter', // Plan starter pour trial (contrainte DB)
                'status' => 'trial',
                'sms_limit' => 100, // 100 SMS pour trial
                'sms_used' => 0,
                'users_limit' => 1,
                'structures_limit' => 1,
                'price' => 0.00,
                'trial_ends_at' => now()->addDays(14),
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);

            // 5. ATTACHER USER À ORGANIZATION (role: owner)
            $organization->users()->attach($user->id, [
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // 6. LOGIN AUTOMATIQUE
            Auth::login($user, remember: true);

            // 7. REDIRECTION vers onboarding
            return redirect()->route('onboarding.welcome')
                ->with('success', 'Bienvenue sur S-Remind ! Configurons votre compte ensemble.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log l'erreur pour debug
            \Log::error('Signup failed', [
                'email' => $validated['email'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'error' => 'Une erreur est survenue lors de la création du compte. Veuillez réessayer.',
                ]);
        }
    }

    /**
     * Générer un slug unique pour l'organisation
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $counter = 1;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = Str::slug($name) . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Déterminer timezone selon pays
     */
    private function getTimezoneForCountry(string $countryCode): string
    {
        $timezones = [
            'CI' => 'Africa/Abidjan', // Côte d'Ivoire
            'BF' => 'Africa/Ouagadougou', // Burkina Faso
            'ML' => 'Africa/Bamako', // Mali
            'SN' => 'Africa/Dakar', // Sénégal
            'BJ' => 'Africa/Porto-Novo', // Bénin
            'TG' => 'Africa/Lome', // Togo
            'NE' => 'Africa/Niamey', // Niger
            'GN' => 'Africa/Conakry', // Guinée
            'CM' => 'Africa/Douala', // Cameroun
            'CD' => 'Africa/Kinshasa', // RDC
        ];

        return $timezones[$countryCode] ?? 'Africa/Abidjan';
    }

    /**
     * Déterminer locale selon pays
     */
    private function getLocaleForCountry(string $countryCode): string
    {
        // Tous les pays francophones d'Afrique → 'fr'
        $francophone = ['CI', 'BF', 'ML', 'SN', 'BJ', 'TG', 'NE', 'GN', 'CM', 'CD'];
        
        return in_array($countryCode, $francophone) ? 'fr' : 'en';
    }

    /**
     * Obtenir préfixes par défaut selon pays
     */
    private function getDefaultPrefixesForCountry(string $countryCode): array
    {
        $prefixes = [
            'CI' => ['01', '05', '07'], // Orange, MTN, Moov
            'BF' => ['01', '02', '03'], // Orange, Telecel, Telmob
            'ML' => ['01', '02', '03'], // Orange, Malitel, Telecel
            'SN' => ['01', '02', '03'], // Orange, Tigo, Expresso
            'BJ' => ['01', '02', '03'], // MTN, Moov, Libercom
            'TG' => ['01', '02', '03'], // Togocom, Moov
            'NE' => ['01', '02', '03'], // Niger Telecom, Airtel, Orange
            'GN' => ['01', '02', '03'], // Orange, MTN, Cellcom
            'CM' => ['01', '02', '03'], // Orange, MTN, Nexttel
            'CD' => ['01', '02', '03'], // Vodacom, Airtel, Orange
        ];

        return $prefixes[$countryCode] ?? ['01', '02', '03'];
    }
}
