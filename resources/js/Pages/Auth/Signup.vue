<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Button from '@/Components/ui/Button.vue';
import { ref, computed } from 'vue';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  company_name: '',
  country_iso: 'CI',
  agree_terms: false,
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Password strength calculator
const passwordStrength = computed(() => {
  const password = form.password;
  if (!password) return { level: 0, label: '', color: '' };
  
  let strength = 0;
  if (password.length >= 8) strength++;
  if (password.length >= 12) strength++;
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
  if (/\d/.test(password)) strength++;
  if (/[^A-Za-z0-9]/.test(password)) strength++;
  
  if (strength <= 2) return { level: strength, label: 'Faible', color: 'bg-red-500' };
  if (strength <= 3) return { level: strength, label: 'Moyen', color: 'bg-yellow-500' };
  return { level: strength, label: 'Fort', color: 'bg-success-500' };
});

const submit = () => {
  form.post(route('signup.store'), {
    onSuccess: () => {
      // Redirection automatique vers onboarding
    },
  });
};
</script>

<template>
  <Head title="Créer un compte - S-Remind" />

  <div class="min-h-screen flex">
    
    <!-- Left Side: Image + Message avec Gradient Mesh Animé -->
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
      
      <!-- Animated Gradient Mesh Background -->
      <div class="absolute inset-0 bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900">
        <!-- Animated Blobs -->
        <div class="absolute top-0 -left-4 w-72 h-72 bg-accent-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div class="absolute top-0 -right-4 w-72 h-72 bg-secondary-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-primary-400 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
      </div>

      <!-- Pattern Overlay -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
      </div>

      <!-- Logo -->
      <div class="absolute top-8 left-8 flex items-center space-x-2 z-10">
        <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center border border-white/30">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
          </svg>
        </div>
        <span class="text-2xl font-bold text-white">S-Remind</span>
      </div>

      <!-- Content with Glassmorphism -->
      <div class="relative z-10 flex flex-col justify-center px-16 py-24 text-white">
        
        <!-- Main Message -->
        <h1 class="text-5xl font-bold leading-tight mb-6 animate-fade-in">
          Automatisez vos rappels SMS pour ne manquer aucun rendez-vous
        </h1>

        <!-- Description -->
        <p class="text-xl text-primary-100 leading-relaxed mb-12 max-w-lg animate-slide-up">
          S-Remind vous aide à synchroniser automatiquement vos données CommCare 
          et à envoyer des rappels SMS personnalisés à vos bénéficiaires. 
          Augmentez votre taux de présence et gagnez du temps.
        </p>

        <!-- Stats/Benefits with Staggered Animation -->
        <div class="space-y-4">
          <div class="flex items-center space-x-3 group animate-slide-up" style="animation-delay: 0.1s;">
            <div class="w-10 h-10 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center flex-shrink-0 border border-white/20 group-hover:scale-110 transition-transform">
              <svg class="w-5 h-5 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <span class="text-lg">Configuration en 5 minutes</span>
          </div>

          <div class="flex items-center space-x-3 group animate-slide-up" style="animation-delay: 0.2s;">
            <div class="w-10 h-10 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center flex-shrink-0 border border-white/20 group-hover:scale-110 transition-transform">
              <svg class="w-5 h-5 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <span class="text-lg">Support multi-pays (9 pays africains)</span>
          </div>

          <div class="flex items-center space-x-3 group animate-slide-up" style="animation-delay: 0.3s;">
            <div class="w-10 h-10 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center flex-shrink-0 border border-white/20 group-hover:scale-110 transition-transform">
              <svg class="w-5 h-5 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <span class="text-lg">Analytics en temps réel</span>
          </div>
        </div>

      </div>
    </div>

    <!-- Right Side: Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center px-4 sm:px-6 lg:px-16 py-12 bg-white">
      <div class="w-full max-w-md">

        <!-- Mobile Logo -->
        <div class="lg:hidden flex items-center justify-center mb-8">
          <div class="flex items-center space-x-2">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-accent-500 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
            </div>
            <span class="text-2xl font-bold text-dark-900">S-Remind</span>
          </div>
        </div>

        <!-- Title -->
        <div class="mb-8">
          <h2 class="text-3xl font-bold text-dark-900 mb-2">
            Créer votre compte
          </h2>
          <p class="text-dark-600">
            Commencez votre essai gratuit dès maintenant
          </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-5">
          
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-dark-700 mb-1.5">
              Nom complet
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-3 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all hover:border-dark-300"
              placeholder="Jean Dupont"
            />
            <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600">
              {{ form.errors.name }}
            </p>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-dark-700 mb-1.5">
              Email professionnel
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-3 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all hover:border-dark-300"
              placeholder="jean@entreprise.com"
            />
            <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Company Name -->
          <div>
            <label for="company_name" class="block text-sm font-medium text-dark-700 mb-1.5">
              Nom de l'entreprise
            </label>
            <input
              id="company_name"
              v-model="form.company_name"
              type="text"
              required
              class="w-full px-4 py-3 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all hover:border-dark-300"
              placeholder="Centre Hospitalier Cocody"
            />
            <p v-if="form.errors.company_name" class="mt-1.5 text-sm text-red-600">
              {{ form.errors.company_name }}
            </p>
          </div>

          <!-- Country -->
          <div>
            <label for="country_iso" class="block text-sm font-medium text-dark-700 mb-1.5">
              Pays
            </label>
            <select
              id="country_iso"
              v-model="form.country_iso"
              required
              class="w-full px-4 py-3 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all hover:border-dark-300"
            >
              <option value="CI">🇨🇮 Côte d'Ivoire</option>
              <option value="GH">🇬🇭 Ghana</option>
              <option value="NG">🇳🇬 Nigeria</option>
              <option value="SN">🇸🇳 Sénégal</option>
              <option value="ML">🇲🇱 Mali</option>
              <option value="BF">🇧🇫 Burkina Faso</option>
              <option value="CM">🇨🇲 Cameroun</option>
              <option value="TG">🇹🇬 Togo</option>
              <option value="BJ">🇧🇯 Bénin</option>
            </select>
            <p v-if="form.errors.country_iso" class="mt-1.5 text-sm text-red-600">
              {{ form.errors.country_iso }}
            </p>
          </div>

          <!-- Password with Toggle -->
          <div>
            <label for="password" class="block text-sm font-medium text-dark-700 mb-1.5">
              Mot de passe
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                class="w-full px-4 py-3 pr-12 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all hover:border-dark-300"
                placeholder="••••••••"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-dark-400 hover:text-dark-600 transition-colors"
              >
                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
            
            <!-- Password Strength Indicator -->
            <div v-if="form.password" class="mt-2">
              <div class="flex items-center justify-between text-xs mb-1">
                <span class="text-dark-600">Force du mot de passe</span>
                <span :class="[passwordStrength.level >= 3 ? 'text-success-600' : passwordStrength.level >= 2 ? 'text-yellow-600' : 'text-red-600', 'font-medium']">
                  {{ passwordStrength.label }}
                </span>
              </div>
              <div class="h-1.5 bg-dark-100 rounded-full overflow-hidden">
                <div 
                  :class="[passwordStrength.color, 'h-full transition-all duration-300']"
                  :style="{ width: `${(passwordStrength.level / 5) * 100}%` }"
                ></div>
              </div>
            </div>
            
            <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Password Confirmation with Toggle -->
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-dark-700 mb-1.5">
              Confirmer le mot de passe
            </label>
            <div class="relative">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showPasswordConfirmation ? 'text' : 'password'"
                required
                class="w-full px-4 py-3 pr-12 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all hover:border-dark-300"
                placeholder="••••••••"
              />
              <button
                type="button"
                @click="showPasswordConfirmation = !showPasswordConfirmation"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-dark-400 hover:text-dark-600 transition-colors"
              >
                <svg v-if="!showPasswordConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Terms -->
          <div class="flex items-start">
            <input
              id="agree_terms"
              v-model="form.agree_terms"
              type="checkbox"
              required
              class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-dark-300 rounded transition-colors"
            />
            <label for="agree_terms" class="ml-2 block text-sm text-dark-600">
              J'accepte les 
              <a href="/terms" class="text-primary-600 hover:text-primary-700 font-medium transition-colors">
                conditions d'utilisation
              </a>
              et la 
              <a href="/privacy" class="text-primary-600 hover:text-primary-700 font-medium transition-colors">
                politique de confidentialité
              </a>
            </label>
          </div>
          <p v-if="form.errors.agree_terms" class="text-sm text-red-600">
            {{ form.errors.agree_terms }}
          </p>

          <!-- Submit Button avec Animation -->
          <Button
            type="submit"
            variant="primary"
            size="lg"
            class="w-full group"
            :disabled="form.processing"
          >
            <span v-if="!form.processing" class="flex items-center justify-center">
              Créer mon compte
              <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </span>
            <span v-else class="flex items-center justify-center">
              <svg class="animate-spin h-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Création en cours...
            </span>
          </Button>

          <!-- Login Link -->
          <p class="text-center text-sm text-dark-600 mt-6">
            Déjà un compte ? 
            <a href="/login" class="font-medium text-primary-600 hover:text-primary-700 transition-colors">
              Se connecter
            </a>
          </p>

        </form>

      </div>
    </div>

  </div>
</template>

<style scoped>
@keyframes blob {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  25% {
    transform: translate(20px, -50px) scale(1.1);
  }
  50% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  75% {
    transform: translate(50px, 50px) scale(1.05);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}
</style>
