<template>
  <SettingsLayout title="Configuration SMS">
    <div class="py-6 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Configuration SMS</h1>
          <p class="mt-1 text-sm text-gray-600">
            Configuration de votre fournisseur SMS Africa's Talking
          </p>
        </div>

        <!-- Message de succès/erreur -->
        <div v-if="form.recentlySuccessful" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium text-green-900">
              Configuration enregistrée avec succès !
            </p>
          </div>
        </div>

        <div v-if="testResult" class="mb-6">
          <div 
            v-if="testResult.success"
            class="p-4 bg-green-50 border border-green-200 rounded-lg"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <p class="text-sm font-medium text-green-900">
                  {{ testResult.message }}
                </p>
                <p class="text-xs text-green-700 mt-1">
                  Solde disponible : {{ testResult.balance }}
                </p>
              </div>
            </div>
          </div>
          <div 
            v-else
            class="p-4 bg-red-50 border border-red-200 rounded-lg"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <p class="text-sm font-medium text-red-900">
                  Erreur de connexion
                </p>
                <p class="text-xs text-red-700 mt-1">
                  {{ testResult.message }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Formulaire Configuration -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Card Credentials -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Identifiants Africa's Talking
              </h2>
              <p class="text-sm text-gray-600 mt-1">
                Retrouvez vos identifiants sur votre 
                <a href="https://account.africastalking.com" target="_blank" class="text-blue-600 hover:text-blue-700 underline">
                  compte Africa's Talking
                </a>
              </p>
            </div>

            <div class="p-6 space-y-4">
              <!-- Environment -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Environnement *
                </label>
                <div class="flex gap-4">
                  <label class="flex items-center">
                    <input
                      type="radio"
                      v-model="form.environment"
                      value="sandbox"
                      class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                    />
                    <span class="ml-2 text-sm text-gray-700">
                      Sandbox (test)
                    </span>
                  </label>
                  <label class="flex items-center">
                    <input
                      type="radio"
                      v-model="form.environment"
                      value="production"
                      class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                    />
                    <span class="ml-2 text-sm text-gray-700">
                      Production (réel)
                    </span>
                  </label>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                  💡 Utilisez "Sandbox" pour tester sans frais
                </p>
              </div>

              <!-- Username -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Username *
                </label>
                <input
                  type="text"
                  v-model="form.username"
                  required
                  placeholder="sandbox ou votre username"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': form.errors.username }"
                />
                <p v-if="form.errors.username" class="mt-1 text-sm text-red-600">
                  {{ form.errors.username }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                  En mode Sandbox, utilisez "sandbox"
                </p>
              </div>

              <!-- API Key -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  API Key *
                </label>
                <div class="relative">
                  <input
                    :type="showApiKey ? 'text' : 'password'"
                    v-model="form.api_key"
                    required
                    placeholder="Votre clé API Africa's Talking"
                    class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': form.errors.api_key }"
                  />
                  <button
                    type="button"
                    @click="showApiKey = !showApiKey"
                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600"
                  >
                    <svg v-if="showApiKey" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </div>
                <p v-if="form.errors.api_key" class="mt-1 text-sm text-red-600">
                  {{ form.errors.api_key }}
                </p>
              </div>

              <!-- Sender ID -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Sender ID (nom affiché) *
                </label>
                <input
                  type="text"
                  v-model="form.sender_id"
                  required
                  placeholder="Ex: S-REMIND"
                  maxlength="11"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-500': form.errors.sender_id }"
                />
                <p v-if="form.errors.sender_id" class="mt-1 text-sm text-red-600">
                  {{ form.errors.sender_id }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                  Maximum 11 caractères. C'est le nom qui apparaîtra comme expéditeur du SMS.
                </p>
              </div>
            </div>
          </div>

          <!-- Card Statistiques (si configuré) -->
          <div 
            v-if="smsConfig"
            class="bg-white rounded-lg border border-gray-200 shadow-sm"
          >
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Statistiques d'utilisation
              </h2>
            </div>

            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Solde -->
                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                  <p class="text-sm font-medium text-blue-900">Solde disponible</p>
                  <p class="text-2xl font-bold text-blue-900 mt-1">
                    {{ formatCurrency(balance) }}
                  </p>
                  <p class="text-xs text-blue-700 mt-1">
                    Coût moyen par SMS : 18.5 FCFA
                  </p>
                </div>

                <!-- SMS ce mois -->
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                  <p class="text-sm font-medium text-green-900">SMS envoyés ce mois</p>
                  <p class="text-2xl font-bold text-green-900 mt-1">
                    {{ formatNumber(smsStats.sent_this_month) }}
                  </p>
                  <p class="text-xs text-green-700 mt-1">
                    Coût : {{ formatCurrency(smsStats.cost_this_month) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-between gap-4">
            <button
              type="button"
              @click="testConnection"
              :disabled="testing || !form.username || !form.api_key"
              class="px-6 py-2.5 bg-white border-2 border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <svg 
                v-if="testing"
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span v-if="testing">Test en cours...</span>
              <span v-else>Tester la connexion</span>
            </button>

            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="form.processing">Enregistrement...</span>
              <span v-else>Enregistrer la configuration</span>
            </button>
          </div>
        </form>

        <!-- Info box -->
        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-300 rounded-lg">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="text-sm font-semibold text-yellow-900">
                💡 Besoin d'aide ?
              </p>
              <p class="text-sm text-yellow-800 mt-1">
                Consultez la 
                <a href="https://developers.africastalking.com/docs" target="_blank" class="underline font-medium">
                  documentation Africa's Talking
                </a>
                pour créer votre compte et obtenir vos identifiants.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </SettingsLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

const props = defineProps({
  organization: Object,
  smsConfig: Object,
  balance: Number,
  smsStats: Object,
});

// État
const showApiKey = ref(false);
const testing = ref(false);
const testResult = ref(null);

// Form
const form = useForm({
  environment: props.smsConfig?.environment || 'sandbox',
  username: props.smsConfig?.username || '',
  api_key: props.smsConfig?.api_key || '',
  sender_id: props.smsConfig?.sender_id || 'S-REMIND',
});

// Format nombre
const formatNumber = (num) => {
  return new Intl.NumberFormat('fr-FR').format(num || 0);
};

// Format devise
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XOF',
    minimumFractionDigits: 0,
  }).format(amount || 0);
};

// Tester la connexion
const testConnection = async () => {
  testing.value = true;
  testResult.value = null;

  try {
    const response = await fetch('/settings/sms/test', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        environment: form.environment,
        username: form.username,
        api_key: form.api_key,
      }),
    });

    const data = await response.json();
    testResult.value = data;
  } catch (error) {
    testResult.value = {
      success: false,
      message: 'Erreur lors du test de connexion',
    };
  } finally {
    testing.value = false;
  }
};

// Submit
const submit = () => {
  form.post('/settings/sms/update', {
    onSuccess: () => {
      // Fermer l'affichage de l'API key
      showApiKey.value = false;
    },
  });
};
</script>
