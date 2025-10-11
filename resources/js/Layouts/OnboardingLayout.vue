<script setup>
import { computed } from 'vue';

const props = defineProps({
  currentStep: {
    type: Number,
    required: true,
  },
  totalSteps: {
    type: Number,
    default: 6,
  },
});

const steps = [
  { number: 1, name: 'Bienvenue', description: 'Commencez votre configuration' },
  { number: 2, name: 'Entreprise', description: 'Informations organisation' },
  { number: 3, name: 'CommCare', description: 'Configuration API' },
  { number: 4, name: 'Téléphonie', description: 'Validation numéros' },
  { number: 5, name: 'Mapping', description: 'Champs personnalisés' },
  { number: 6, name: 'Finalisation', description: 'Synchronisation initiale' },
];

const getStepStatus = (stepNumber) => {
  if (stepNumber < props.currentStep) return 'completed';
  if (stepNumber === props.currentStep) return 'current';
  return 'upcoming';
};
</script>

<template>
  <div class="min-h-screen flex bg-dark-50">
    
    <!-- Sidebar Steps -->
    <aside class="hidden lg:flex lg:w-80 bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 relative overflow-hidden">
      
      <!-- Pattern Background -->
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
        <span class="text-xl font-bold text-white">S-Remind</span>
      </div>

      <!-- Steps -->
      <div class="relative z-10 flex flex-col justify-center px-8 py-24">
        <div class="space-y-1">
          <div 
            v-for="step in steps" 
            :key="step.number"
            class="relative"
          >
            <!-- Step Item -->
            <div class="flex items-start space-x-4 py-4">
              <!-- Number Badge -->
              <div 
                :class="[
                  'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all',
                  getStepStatus(step.number) === 'completed' ? 'bg-success-500 text-white' :
                  getStepStatus(step.number) === 'current' ? 'bg-white text-primary-700 ring-4 ring-white/30' :
                  'bg-white/20 text-white/60 border border-white/30'
                ]"
              >
                <svg v-if="getStepStatus(step.number) === 'completed'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
                <span v-else>{{ step.number }}</span>
              </div>

              <!-- Step Info -->
              <div class="flex-1 min-w-0 pt-1">
                <p 
                  :class="[
                    'text-sm font-semibold transition-colors',
                    getStepStatus(step.number) === 'current' ? 'text-white' : 'text-white/70'
                  ]"
                >
                  {{ step.name }}
                </p>
                <p class="text-xs text-white/50 mt-0.5">
                  {{ step.description }}
                </p>
              </div>
            </div>

            <!-- Connector Line -->
            <div 
              v-if="step.number < totalSteps"
              :class="[
                'absolute left-5 top-16 w-0.5 h-8 transition-colors',
                getStepStatus(step.number) === 'completed' ? 'bg-success-500' : 'bg-white/20'
              ]"
            ></div>
          </div>
        </div>

        <!-- Progress Text -->
        <div class="mt-12 pt-8 border-t border-white/20">
          <p class="text-sm text-white/70">
            Étape <span class="font-bold text-white">{{ currentStep }}</span> sur {{ totalSteps }}
          </p>
          <div class="mt-3 h-2 bg-white/20 rounded-full overflow-hidden">
            <div 
              class="h-full bg-success-500 transition-all duration-500"
              :style="{ width: `${(currentStep / totalSteps) * 100}%` }"
            ></div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
      
      <!-- Top Navigation -->
      <header class="bg-white border-b border-dark-100 px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-dark-900">Configuration initiale</h2>
            <p class="text-sm text-dark-600 mt-0.5">Configurez votre compte S-Remind</p>
          </div>
          <div class="flex items-center space-x-3">
            <button class="px-4 py-2 text-sm text-dark-600 hover:text-dark-900 transition-colors">
              Sauvegarder et quitter
            </button>
          </div>
        </div>
      </header>

      <!-- Content Area -->
      <div class="flex-1 overflow-y-auto p-6 lg:p-12">
        <div class="max-w-3xl mx-auto">
          <slot />
        </div>
      </div>

    </main>

  </div>
</template>
