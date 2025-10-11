<script setup>
import { Head, router } from '@inertiajs/vue3';
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue';
import Button from '@/Components/ui/Button.vue';
import { ref, onMounted } from 'vue';

const syncing = ref(false);
const syncProgress = ref(0);
const syncStatus = ref('idle'); // idle, syncing, success, error
const syncStats = ref({
  total_cases: 0,
  synced_cases: 0,
  eligible_cases: 0,
});

const startSync = async () => {
  syncing.value = true;
  syncStatus.value = 'syncing';
  
  // Simulation de la progression (à remplacer par vraie API)
  const interval = setInterval(() => {
    syncProgress.value += 10;
    
    if (syncProgress.value >= 100) {
      clearInterval(interval);
      syncStatus.value = 'success';
      syncing.value = false;
      
      // Données simulées
      syncStats.value = {
        total_cases: 450,
        synced_cases: 450,
        eligible_cases: 387,
      };
    }
  }, 500);
};

const goToDashboard = () => {
  router.visit('/');
};

const previousStep = () => {
  router.visit('/onboarding/mapping');
};

// Auto-start sync après 1 seconde
onMounted(() => {
  setTimeout(() => {
    startSync();
  }, 1000);
});
</script>

<template>
  <Head title="Configuration Terminée - S-Remind" />

  <OnboardingLayout :current-step="6">
    
    <div class="space-y-8 animate-fade-in">
      
      <!-- Success Icon (shown after sync) -->
      <div v-if="syncStatus === 'success'" class="text-center animate-bounce-in">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-success-400 to-success-600 rounded-full mb-4">
          <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h2 class="text-3xl font-bold text-dark-900">
          Configuration terminée ! 🎉
        </h2>
        <p class="text-dark-600 mt-2">
          Votre compte S-Remind est prêt à l'emploi
        </p>
      </div>

      <!-- Loading State -->
      <div v-else class="text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-primary-100 rounded-full mb-4 animate-pulse">
          <svg class="w-12 h-12 text-primary-600 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <h2 class="text-3xl font-bold text-dark-900">
          Synchronisation en cours...
        </h2>
        <p class="text-dark-600 mt-2">
          Nous importons vos données depuis CommCare
        </p>
      </div>

      <!-- Progress Bar -->
      <div v-if="syncStatus === 'syncing'" class="max-w-md mx-auto">
        <div class="h-3 bg-dark-100 rounded-full overflow-hidden">
          <div 
            class="h-full bg-gradient-to-r from-primary-500 to-accent-500 transition-all duration-500 ease-out"
            :style="{ width: `${syncProgress}%` }"
          ></div>
        </div>
        <p class="text-center text-sm text-dark-600 mt-2">
          {{ syncProgress }}% complété
        </p>
      </div>

      <!-- Sync Stats (after success) -->
      <div v-if="syncStatus === 'success'" class="grid grid-cols-3 gap-4 max-w-2xl mx-auto">
        <div class="bg-primary-50 border border-primary-200 rounded-lg p-4 text-center">
          <p class="text-3xl font-bold text-primary-700">{{ syncStats.total_cases }}</p>
          <p class="text-sm text-dark-600 mt-1">Cases synchronisés</p>
        </div>
        
        <div class="bg-success-50 border border-success-200 rounded-lg p-4 text-center">
          <p class="text-3xl font-bold text-success-700">{{ syncStats.eligible_cases }}</p>
          <p class="text-sm text-dark-600 mt-1">Numéros valides</p>
        </div>
        
        <div class="bg-accent-50 border border-accent-200 rounded-lg p-4 text-center">
          <p class="text-3xl font-bold text-accent-700">
            {{ Math.round((syncStats.eligible_cases / syncStats.total_cases) * 100) }}%
          </p>
          <p class="text-sm text-dark-600 mt-1">Taux de validation</p>
        </div>
      </div>

      <!-- Configuration Summary -->
      <div class="bg-white border border-dark-200 rounded-xl p-6 max-w-2xl mx-auto">
        <h3 class="text-lg font-semibold text-dark-900 mb-4 flex items-center">
          <svg class="w-5 h-5 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Récapitulatif de votre configuration
        </h3>

        <div class="space-y-3">
          <div class="flex items-center justify-between py-2 border-b border-dark-100">
            <span class="text-sm text-dark-600">Secteur d'activité</span>
            <span class="text-sm font-medium text-dark-900">🏥 Santé</span>
          </div>

          <div class="flex items-center justify-between py-2 border-b border-dark-100">
            <span class="text-sm text-dark-600">Fuseau horaire</span>
            <span class="text-sm font-medium text-dark-900">🇨🇮 Africa/Abidjan (GMT+0)</span>
          </div>

          <div class="flex items-center justify-between py-2 border-b border-dark-100">
            <span class="text-sm text-dark-600">Domaine CommCare</span>
            <span class="text-sm font-medium text-dark-900 font-mono">votre-domaine.commcarehq.org</span>
          </div>

          <div class="flex items-center justify-between py-2 border-b border-dark-100">
            <span class="text-sm text-dark-600">Pays de validation</span>
            <span class="text-sm font-medium text-dark-900">🇨🇮 Côte d'Ivoire (+225)</span>
          </div>

          <div class="flex items-center justify-between py-2">
            <span class="text-sm text-dark-600">Préfixes autorisés</span>
            <span class="text-sm font-medium text-dark-900 font-mono">01, 05, 07</span>
          </div>
        </div>
      </div>

      <!-- Next Steps -->
      <div v-if="syncStatus === 'success'" class="bg-gradient-to-br from-primary-50 to-accent-50 border border-primary-200 rounded-xl p-6 max-w-2xl mx-auto">
        <h3 class="text-lg font-semibold text-dark-900 mb-4">Prochaines étapes 🚀</h3>
        
        <ul class="space-y-3">
          <li class="flex items-start">
            <span class="flex-shrink-0 w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-3 mt-0.5">1</span>
            <div>
              <p class="font-medium text-dark-900">Créer vos templates SMS</p>
              <p class="text-sm text-dark-600 mt-0.5">Personnalisez les messages pour chaque type de rendez-vous</p>
            </div>
          </li>

          <li class="flex items-start">
            <span class="flex-shrink-0 w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-3 mt-0.5">2</span>
            <div>
              <p class="font-medium text-dark-900">Configurer les règles d'envoi</p>
              <p class="text-sm text-dark-600 mt-0.5">Définissez quand envoyer les rappels (J-2, J-7, etc.)</p>
            </div>
          </li>

          <li class="flex items-start">
            <span class="flex-shrink-0 w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-3 mt-0.5">3</span>
            <div>
              <p class="font-medium text-dark-900">Activer l'envoi automatique</p>
              <p class="text-sm text-dark-600 mt-0.5">Lancez vos premiers envois SMS automatisés</p>
            </div>
          </li>
        </ul>
      </div>

      <!-- Navigation Buttons -->
      <div class="flex items-center justify-between pt-6 border-t border-dark-100 max-w-2xl mx-auto">
        <Button
          v-if="syncStatus !== 'success'"
          type="button"
          variant="ghost"
          @click="previousStep"
          :disabled="syncing"
          class="group"
        >
          <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Retour
        </Button>

        <div v-else></div>

        <Button
          v-if="syncStatus === 'success'"
          variant="primary"
          size="lg"
          @click="goToDashboard"
          class="group ml-auto"
        >
          Accéder au Dashboard
          <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </Button>

        <span v-else class="text-sm text-dark-500 italic">
          Veuillez patienter pendant la synchronisation...
        </span>
      </div>

    </div>

  </OnboardingLayout>
</template>

<style scoped>
@keyframes bounce-in {
  0% {
    transform: scale(0.3);
    opacity: 0;
  }
  50% {
    transform: scale(1.05);
  }
  70% {
    transform: scale(0.9);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-bounce-in {
  animation: bounce-in 0.6s ease-out;
}
</style>
