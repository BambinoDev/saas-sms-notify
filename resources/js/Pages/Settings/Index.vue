<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Paramètres</h2>
          <p class="mt-1 text-sm text-gray-600">
            Configuration du système de rappel SMS
          </p>
        </div>
        <span v-if="hasChanges" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
          Non sauvegardé
        </span>
      </div>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
        {{ $page.props.flash.error }}
      </div>

      <!-- Synchronisation CommCare -->
      <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          Synchronisation CommCare
        </h3>
        
        <!-- Contenu principal -->
        <div class="mb-4">
          <p class="text-sm text-gray-600">
            Dernière synchronisation :
            <span class="font-medium text-gray-900">
              {{ settings.last_sync ? formatDate(settings.last_sync) : 'Jamais' }}
            </span>
          </p>
          <p class="text-xs text-gray-500 mt-1">
            Synchronisation automatique tous les jours à 01h00
          </p>
          
          <!-- Statistiques Sync -->
          <div class="mt-4 grid grid-cols-3 gap-4 text-sm">
            <div>
              <span class="text-gray-500">Total femmes :</span>
              <span class="font-semibold text-gray-900 ml-2">{{ stats.total_women.toLocaleString() }}</span>
            </div>
            <div>
              <span class="text-gray-500">Éligibles SMS :</span>
              <span class="font-semibold text-green-600 ml-2">{{ stats.eligible_women.toLocaleString() }}</span>
            </div>
            <div>
              <span class="text-gray-500">SMS en attente :</span>
              <span class="font-semibold text-blue-600 ml-2">{{ stats.pending_sms.toLocaleString() }}</span>
            </div>
          </div>
        </div>
        
        <!-- Bouton de synchronisation centré et proéminent -->
        <div class="flex justify-center">
          <button
            @click="syncNow"
            :disabled="syncing"
            type="button"
            class="inline-flex items-center px-8 py-4 border border-transparent text-lg font-bold rounded-xl shadow-xl text-white focus:outline-none focus:ring-4 focus:ring-offset-2 transition-all duration-300 transform hover:scale-105"
            :class="syncing 
              ? 'bg-gray-400 cursor-not-allowed transform-none' 
              : 'bg-blue-800 hover:bg-blue-900 focus:ring-blue-600'"
          >
            <svg v-if="syncing" class="animate-spin -ml-1 mr-3 h-6 w-6 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="font-bold text-xl">
              {{ syncing ? 'Synchronisation en cours...' : '🔄 Synchroniser maintenant' }}
            </span>
          </button>
        </div>

        <!-- Statut de synchronisation -->
        <div v-if="settings.sync_status === 'running'" class="mt-4 flex items-center justify-center text-blue-600">
          <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span class="font-medium">Synchronisation en cours...</span>
        </div>
      </div>

      <!-- Templates SMS -->
      <form @submit.prevent="saveSettings" class="space-y-6">
        <!-- Template J-2 -->
        <div class="bg-white shadow rounded-lg p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              Template SMS J-2 (Rappel 2 jours avant)
            </h3>
            <button
              type="button"
              @click="resetTemplateJMinus2"
              class="text-sm text-indigo-600 hover:text-indigo-800 font-medium hover:underline transition-colors"
            >
              Restaurer par défaut
            </button>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Message
            </label>
            <textarea
              v-model="form.template_j_minus_2"
              rows="4"
              :class="[
                'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                form.template_j_minus_2.length > 160 ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
              ]"
              placeholder="Bonjour Mme {case_name}..."
            ></textarea>
            
            <!-- Compteur de caractères -->
            <div class="flex justify-between text-xs mt-1">
              <div>
                <span :class="form.template_j_minus_2.length > 160 ? 'text-red-600 font-semibold' : 'text-gray-500'">
                  {{ form.template_j_minus_2.length }} / 160 caractères
                </span>
                <span v-if="form.template_j_minus_2.length > 160" class="text-red-600 font-semibold ml-2">
                  ⚠️ Dépassement SMS
                </span>
              </div>
              <span 
                :class="form.template_j_minus_2.length > 160 ? 'text-red-600 font-semibold' : 'text-gray-500'"
              >
                {{ Math.ceil(form.template_j_minus_2.length / 160) }} SMS
              </span>
            </div>
            
            <!-- Variables détectées -->
            <div class="mt-2 text-xs">
              <span class="text-gray-500">Variables détectées : </span>
              <span v-if="form.template_j_minus_2.includes('{case_name}')" class="text-green-600">✓ {case_name}</span>
              <span v-if="form.template_j_minus_2.includes('{anc_number}')" class="text-green-600">✓ {anc_number}</span>
              <span v-if="form.template_j_minus_2.includes('{structure}')" class="text-green-600">✓ {structure}</span>
            </div>
            
            <div v-if="errors.template_j_minus_2" class="mt-2 text-sm text-red-600">
              {{ errors.template_j_minus_2 }}
            </div>
          </div>
          <!-- Preview -->
          <div class="mt-4 p-4 bg-gray-50 rounded-md">
            <p class="text-xs font-medium text-gray-500 mb-2">Aperçu :</p>
            <p class="text-sm text-gray-900">{{ previewJMinus2 }}</p>
          </div>
        </div>

        <!-- Template Jour-J -->
        <div class="bg-white shadow rounded-lg p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              Template SMS Jour-J (Rappel le jour même)
            </h3>
            <button
              type="button"
              @click="resetTemplateJourJ"
              class="text-sm text-indigo-600 hover:text-indigo-800 font-medium hover:underline transition-colors"
            >
              Restaurer par défaut
            </button>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Message
            </label>
            <textarea
              v-model="form.template_jour_j"
              rows="4"
              :class="[
                'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                form.template_jour_j.length > 160 ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
              ]"
              placeholder="Bonjour Mme {case_name}..."
            ></textarea>
            
            <!-- Compteur de caractères -->
            <div class="flex justify-between text-xs mt-1">
              <div>
                <span :class="form.template_jour_j.length > 160 ? 'text-red-600 font-semibold' : 'text-gray-500'">
                  {{ form.template_jour_j.length }} / 160 caractères
                </span>
                <span v-if="form.template_jour_j.length > 160" class="text-red-600 font-semibold ml-2">
                  ⚠️ Dépassement SMS
                </span>
              </div>
              <span 
                :class="form.template_jour_j.length > 160 ? 'text-red-600 font-semibold' : 'text-gray-500'"
              >
                {{ Math.ceil(form.template_jour_j.length / 160) }} SMS
              </span>
            </div>
            
            <!-- Variables détectées -->
            <div class="mt-2 text-xs">
              <span class="text-gray-500">Variables détectées : </span>
              <span v-if="form.template_jour_j.includes('{case_name}')" class="text-green-600">✓ {case_name}</span>
              <span v-if="form.template_jour_j.includes('{anc_number}')" class="text-green-600">✓ {anc_number}</span>
              <span v-if="form.template_jour_j.includes('{structure}')" class="text-green-600">✓ {structure}</span>
            </div>
            
            <div v-if="errors.template_jour_j" class="mt-2 text-sm text-red-600">
              {{ errors.template_jour_j }}
            </div>
          </div>
          <!-- Preview -->
          <div class="mt-4 p-4 bg-gray-50 rounded-md">
            <p class="text-xs font-medium text-gray-500 mb-2">Aperçu :</p>
            <p class="text-sm text-gray-900">{{ previewJourJ }}</p>
          </div>
        </div>

        <!-- Heure d'envoi -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Heure d'envoi des SMS
          </h3>
          <div class="max-w-xs">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Heure (format 24h)
            </label>
            <input
              v-model="form.sending_time"
              type="time"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            <p class="mt-2 text-sm text-gray-500">
              Les SMS seront envoyés à cette heure tous les jours
            </p>
            <div v-if="errors.sending_time" class="mt-2 text-sm text-red-600">
              {{ errors.sending_time }}
            </div>
          </div>
        </div>

        <!-- Boutons actions -->
        <div class="flex justify-end gap-3">
          <button
            type="button"
            @click="resetForm"
            class="px-4 py-2 border-2 border-gray-400 rounded-md shadow text-sm font-medium text-gray-800 bg-white hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
          >
            Annuler les modifications
          </button>
          <button
            type="submit"
            :disabled="saving || !hasChanges"
            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
            :class="(saving || !hasChanges)
              ? 'bg-gray-400 cursor-not-allowed' 
              : 'bg-green-600 hover:bg-green-700 focus:ring-green-500'"
          >
            <svg v-if="saving" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-if="hasChanges && !saving" class="mr-2 text-lg">●</span>
            {{ saving ? 'Enregistrement...' : 'Enregistrer les modifications' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  settings: Object,
  stats: Object,
  errors: Object,
});

const syncing = ref(false);
const saving = ref(false);

const form = useForm({
  template_j_minus_2: props.settings.template_j_minus_2,
  template_jour_j: props.settings.template_jour_j,
  sending_time: props.settings.sending_time,
});

// Indicateur de modification
const hasChanges = computed(() => {
  return form.template_j_minus_2 !== props.settings.template_j_minus_2
    || form.template_jour_j !== props.settings.template_jour_j
    || form.sending_time !== props.settings.sending_time;
});

const previewJMinus2 = computed(() => {
  return form.template_j_minus_2
    .replace('{case_name}', 'Marie Kouassi')
    .replace('{anc_number}', '3')
    .replace('{structure}', 'Centre de Santé de Yopougon');
});

const previewJourJ = computed(() => {
  return form.template_jour_j
    .replace('{case_name}', 'Marie Kouassi')
    .replace('{anc_number}', '3')
    .replace('{structure}', 'Centre de Santé de Yopougon');
});

const saveSettings = () => {
  saving.value = true;
  form.post('/settings', {
    preserveScroll: true,
    onFinish: () => {
      saving.value = false;
    },
  });
};

const resetForm = () => {
  form.reset();
};

const resetTemplateJMinus2 = () => {
  if (confirm('Restaurer le template J-2 par défaut ?')) {
    form.template_j_minus_2 = "Bonjour Mme {case_name}, venez après-demain au Centre de Santé pour votre {anc_number}ème Consultation Prénatale. Ne manquez pas ce Rendez-vous !";
  }
};

const resetTemplateJourJ = () => {
  if (confirm('Restaurer le template Jour-J par défaut ?')) {
    form.template_jour_j = "Bonjour Mme {case_name}, venez aujourd'hui au centre santé pour votre {anc_number}ème Consultation Prénatale. Nous vous attendons !";
  }
};

const syncNow = () => {
  if (confirm('Lancer une synchronisation manuelle avec CommCare ?')) {
    syncing.value = true;
    router.post('/settings/sync', {}, {
      preserveScroll: true,
      onFinish: () => {
        syncing.value = false;
      },
    });
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('fr-FR');
};
</script>