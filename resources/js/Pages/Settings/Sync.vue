<template>
  <SettingsLayout title="Synchronisation">
    <div class="py-6 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Synchronisation CommCare</h1>
          <p class="mt-1 text-sm text-gray-600">
            Configuration et statistiques de synchronisation avec CommCare
          </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <!-- Total dossiers -->
          <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
              <div class="p-3 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
            </div>
            <p class="text-sm font-medium text-gray-600">Total dossiers</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">
              {{ formatNumber(statsData.total_cases) }}
            </p>
            <p class="text-xs text-gray-500 mt-2">
              Tous les dossiers synchronisés
            </p>
          </div>

          <!-- Éligibles SMS -->
          <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
              <div class="p-3 bg-green-100 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <p class="text-sm font-medium text-gray-600">Éligibles SMS</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">
              {{ formatNumber(statsData.eligible_cases) }}
            </p>
            <p class="text-xs text-gray-500 mt-2">
              Avec numéro de téléphone valide
            </p>
          </div>

          <!-- SMS en attente -->
          <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
              <div class="p-3 bg-yellow-100 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <p class="text-sm font-medium text-gray-600">SMS en attente</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">
              {{ formatNumber(statsData.pending_sms) }}
            </p>
            <p class="text-xs text-gray-500 mt-2">
              En file d'attente d'envoi
            </p>
          </div>
        </div>

        <!-- Synchronisation Card -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
          <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              État de la synchronisation
            </h2>
          </div>

          <div class="p-6 space-y-4">
            <!-- Dernière synchronisation -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
              <div>
                <p class="text-sm font-medium text-gray-900">Dernière synchronisation</p>
                <p class="text-sm text-gray-600 mt-1">
                  {{ lastSyncFormatted }}
                </p>
              </div>
              <div 
                class="px-3 py-1 rounded-full text-xs font-medium"
                :class="syncStatusClass"
              >
                {{ syncStatusText }}
              </div>
            </div>

            <!-- Fréquence -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
              <div>
                <p class="text-sm font-medium text-gray-900">Fréquence de synchronisation</p>
                <p class="text-sm text-gray-600 mt-1">
                  Automatique tous les jours à 01h00
                </p>
              </div>
            </div>

            <!-- Prochaine synchronisation -->
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-900">Prochaine synchronisation automatique</p>
                <p class="text-sm text-gray-600 mt-1">
                  {{ nextSyncFormatted }}
                </p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="p-6 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <div class="flex-1">
              <p class="text-sm text-gray-600">
                La synchronisation manuelle récupère les dernières données depuis CommCare
              </p>
            </div>
            <button
              @click="syncNow"
              :disabled="syncing"
              class="ml-4 px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <svg 
                v-if="syncing"
                class="animate-spin w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <svg 
                v-else
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span v-if="syncing">Synchronisation en cours...</span>
              <span v-else>Synchroniser maintenant</span>
            </button>
          </div>
        </div>

        <!-- Configuration CommCare (à venir) -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
          <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Configuration CommCare
            </h2>
          </div>

          <div class="p-6">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Domaine CommCare
                </label>
                <input
                  type="text"
                  :value="organization.commcare_domain || 'Non configuré'"
                  disabled
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  API Key
                </label>
                <input
                  type="password"
                  value="••••••••••••••••"
                  disabled
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
                />
              </div>

              <div class="pt-4">
                <button
                  type="button"
                  disabled
                  class="px-4 py-2 bg-gray-100 text-gray-400 rounded-lg font-medium cursor-not-allowed"
                >
                  Modifier la configuration (à venir)
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </SettingsLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

const props = defineProps({
  organization: Object,
  stats: Object,
  lastSync: String,
});

// État
const syncing = ref(false);

// Stats réelles depuis le serveur
const statsData = ref(props.stats || {
  total_cases: 0,
  eligible_cases: 0,
  pending_sms: 0,
});

// Dernière synchro
const lastSyncFormatted = computed(() => {
  return props.lastSync || 'Jamais';
});

// Prochaine synchro
const nextSyncFormatted = computed(() => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  tomorrow.setHours(1, 0, 0, 0);
  return tomorrow.toLocaleString('fr-FR');
});

// Status de la synchro
const syncStatusClass = computed(() => {
  return 'bg-green-100 text-green-800';
});

const syncStatusText = computed(() => {
  return 'Synchronisé';
});

// Format number avec séparateurs
const formatNumber = (num) => {
  return new Intl.NumberFormat('fr-FR').format(num);
};

// Synchroniser maintenant
const syncNow = async () => {
  syncing.value = true;

  try {
    // TODO: Appel API réel
    await new Promise(resolve => setTimeout(resolve, 2000)); // Simulation
    
    // Recharger la page pour avoir les nouvelles stats
    router.reload();
  } catch (error) {
    console.error('Erreur sync:', error);
    alert('Erreur lors de la synchronisation');
  } finally {
    syncing.value = false;
  }
};
</script>
