<script setup>
import { Head } from '@inertiajs/vue3';
import Button from '@/Components/ui/Button.vue';
import GriotLogo from '@/Components/Branding/GriotLogo.vue';
import { ref, onMounted } from 'vue';

const isVisible = ref(false);

onMounted(() => {
  setTimeout(() => {
    isVisible.value = true;
  }, 100);
});
</script>

<template>
  <Head title="S-Remind - Automatisez vos envois SMS" />

  <div class="min-h-screen bg-gradient-to-br from-dark-50 via-white to-griot-blue-50/30">
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-dark-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Logo (transparent icon) -->
          <GriotLogo variant="icon" height="32" />

          <!-- Navigation -->
          <div class="flex items-center space-x-4">
            <Button variant="outline" size="sm">
              Démarrer l'essai gratuit
            </Button>
            <button class="p-2 hover:bg-dark-100 rounded-lg transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <main class="pt-32 pb-20 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          
          <!-- Left: Illustration -->
          <div 
            class="relative order-2 lg:order-1"
            :class="{ 'animate-fade-in': isVisible }"
          >
            <div class="relative w-full aspect-square max-w-lg mx-auto">
              <!-- Background glow -->
              <div class="absolute inset-0 bg-gradient-to-br from-griot-terracotta/20 via-griot-orange/20 to-griot-blue/20 rounded-3xl blur-3xl"></div>
              
              <!-- Card container -->
              <div class="relative bg-white/50 backdrop-blur-sm rounded-3xl border border-dark-100 p-12 shadow-2xl">
                
                <!-- Animated Calendar Icon -->
                <div class="relative mb-8 animate-pulse-slow">
                  <div class="w-48 h-48 mx-auto bg-gradient-to-br from-griot-terracotta to-griot-orange rounded-2xl shadow-2xl transform hover:scale-105 transition-transform duration-300">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white">
                      <div class="text-sm font-semibold mb-2">MARS 2025</div>
                      <div class="text-6xl font-bold">15</div>
                      <div class="text-sm mt-2">Vendredi</div>
                    </div>
                    <!-- Top bar -->
                    <div class="absolute top-4 left-6 right-6 h-1 bg-white/30 rounded-full"></div>
                    <div class="absolute top-4 left-8 w-4 h-6 bg-white/50 rounded-t-lg"></div>
                    <div class="absolute top-4 right-8 w-4 h-6 bg-white/50 rounded-t-lg"></div>
                  </div>
                </div>

                <!-- SMS Bubble -->
                <div class="animate-bounce-slow">
                  <div class="relative max-w-xs mx-auto bg-gradient-to-br from-success-500 to-success-600 text-white px-6 py-4 rounded-2xl rounded-bl-none shadow-xl">
                    <div class="flex items-start space-x-3">
                      <svg class="w-6 h-6 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                      </svg>
                      <div>
                        <p class="font-semibold text-sm">Rappel RDV</p>
                        <p class="text-sm opacity-90 mt-1">Votre rendez-vous est dans 2 jours</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Decorative lines -->
                <div class="absolute top-1/4 -left-8 w-24 h-0.5 bg-gradient-to-r from-transparent via-griot-terracotta/50 to-transparent"></div>
                <div class="absolute bottom-1/4 -right-8 w-24 h-0.5 bg-gradient-to-r from-transparent via-griot-orange/50 to-transparent"></div>
              </div>
            </div>
          </div>

          <!-- Right: Content -->
          <div 
            class="order-1 lg:order-2 space-y-8"
            :class="{ 'animate-slide-up': isVisible }"
          >
            <!-- Hero Title -->
            <div class="space-y-4">
              <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold text-dark-900 leading-tight">
                Automatisez vos 
                <span class="gradient-text block">envois de SMS</span>
                pour ne manquer aucune notification
              </h1>
            </div>

            <!-- Checklist -->
            <ul class="space-y-4">
              <li class="flex items-start space-x-3 group">
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-success-500/10 flex items-center justify-center group-hover:bg-success-500/20 transition-colors">
                  <svg class="w-4 h-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <span class="text-lg text-dark-600 group-hover:text-dark-900 transition-colors">
                  Synchronisation automatique avec CommCare
                </span>
              </li>

              <li class="flex items-start space-x-3 group">
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-success-500/10 flex items-center justify-center group-hover:bg-success-500/20 transition-colors">
                  <svg class="w-4 h-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <span class="text-lg text-dark-600 group-hover:text-dark-900 transition-colors">
                  Templates SMS personnalisables
                </span>
              </li>

              <li class="flex items-start space-x-3 group">
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-success-500/10 flex items-center justify-center group-hover:bg-success-500/20 transition-colors">
                  <svg class="w-4 h-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <span class="text-lg text-dark-600 group-hover:text-dark-900 transition-colors">
                  Validation des numéros multi-pays
                </span>
              </li>

              <li class="flex items-start space-x-3 group">
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-success-500/10 flex items-center justify-center group-hover:bg-success-500/20 transition-colors">
                  <svg class="w-4 h-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <span class="text-lg text-dark-600 group-hover:text-dark-900 transition-colors">
                  Analytics en temps réel
                </span>
              </li>
            </ul>

            <!-- CTA Button -->
            <div class="pt-4">
              <Button variant="primary" size="lg" class="group">
                Démarrer l'essai gratuit
                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </Button>
              <p class="text-sm text-dark-500 mt-3">
                Pas de carte bancaire requise • Configuration en 5 minutes
              </p>
            </div>
          </div>

        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-dark-100 py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center text-sm text-dark-500">
          <p>© 2025 S-Remind. Tous droits réservés.</p>
        </div>
      </div>
    </footer>
  </div>
</template>


