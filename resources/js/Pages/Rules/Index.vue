<template>
  <AppLayout title="Règles SMS">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Règles SMS</h1>
            <p class="mt-2 text-sm text-gray-600">
              Configurez la génération automatique et manuelle de vos SMS
            </p>
          </div>
          <Link
            href="/rules/create"
            class="btn-primary"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouvelle règle
          </Link>
        </div>

        <!-- Info génération automatique -->
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-start gap-3">
          <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="flex-1">
            <h3 class="text-sm font-semibold text-blue-900">Génération automatique active</h3>
            <p class="text-sm text-blue-700 mt-1">
              Les règles actives génèrent automatiquement des SMS selon leur fréquence configurée.
              Vous pouvez également forcer une génération manuelle à tout moment.
            </p>
          </div>
        </div>

        <!-- Liste des règles -->
        <div class="space-y-4">
          <div
            v-for="rule in rules"
            :key="rule.id"
            class="bg-white shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow"
          >
            <div class="p-6">
              <!-- Header -->
              <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                  <div class="flex items-center gap-3">
                    <h3 class="text-lg font-semibold text-gray-900">{{ rule.name }}</h3>
                    
                    <!-- Toggle Active/Inactive -->
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input
                        type="checkbox"
                        :checked="rule.is_active"
                        @change="toggleActive(rule)"
                        class="sr-only peer"
                      />
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                  
                  <p v-if="rule.description" class="mt-1 text-sm text-gray-600">
                    {{ rule.description }}
                  </p>

                  <!-- Détails -->
                  <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-600">
                    <div class="flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                      </svg>
                      <strong>{{ rule.template_name }}</strong>
                    </div>
                    <div class="flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      Génération : <strong>{{ formatFrequency(rule) }}</strong>
                    </div>
                    <div class="flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                      </svg>
                      {{ formatCondition(rule) }}
                    </div>
                  </div>
                </div>

                <!-- Badge Status -->
                <div>
                  <span
                    v-if="rule.is_active && !rule.is_paused"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                  >
                    <span class="w-2 h-2 mr-1.5 bg-green-400 rounded-full animate-pulse"></span>
                    Active
                  </span>
                  <span
                    v-else-if="rule.is_paused"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                  >
                    <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    En pause
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                  >
                    Inactive
                  </span>
                </div>
              </div>

              <!-- Raison pause -->
              <div
                v-if="rule.is_paused && rule.pause_reason"
                class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md"
              >
                <p class="text-sm text-yellow-800">
                  <strong>Raison :</strong> {{ rule.pause_reason }}
                </p>
                <p class="text-xs text-yellow-600 mt-1">
                  Mise en pause {{ formatDate(rule.paused_at) }}
                </p>
              </div>

              <!-- Prochaine génération automatique -->
              <div
                v-if="rule.is_active && !rule.is_paused && rule.next_generation_time"
                class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md"
              >
                <p class="text-sm text-blue-800">
                  <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <strong>Prochaine génération automatique :</strong> {{ formatDate(rule.next_generation_time) }}
                </p>
              </div>

              <!-- Stats -->
              <div class="grid grid-cols-4 gap-4 mb-4">
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                  <div class="text-2xl font-bold text-gray-900">{{ rule.total_generated }}</div>
                  <div class="text-xs text-gray-500 uppercase">Générés</div>
                </div>
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                  <div class="text-2xl font-bold text-blue-600">{{ rule.total_sent }}</div>
                  <div class="text-xs text-gray-500 uppercase">Envoyés</div>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg">
                  <div class="text-2xl font-bold text-green-600">{{ rule.total_delivered }}</div>
                  <div class="text-xs text-gray-500 uppercase">Délivrés</div>
                </div>
                <div class="text-center p-3 bg-red-50 rounded-lg">
                  <div class="text-2xl font-bold text-red-600">{{ rule.total_failed }}</div>
                  <div class="text-xs text-gray-500 uppercase">Échecs</div>
                </div>
              </div>

              <!-- Taux succès + dernière génération -->
              <div class="mb-4 flex justify-between text-sm text-gray-600">
                <div v-if="rule.total_sent + rule.total_failed > 0">
                  <strong>Taux de succès :</strong>
                  <span :class="{
                    'text-green-600': rule.success_rate >= 90,
                    'text-yellow-600': rule.success_rate >= 70 && rule.success_rate < 90,
                    'text-red-600': rule.success_rate < 70
                  }">
                    {{ rule.success_rate }}%
                  </span>
                </div>
                <div v-if="rule.last_generated_at">
                  <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Dernière génération : {{ formatDate(rule.last_generated_at) }}
                </div>
              </div>

              <!-- Actions -->
              <div class="flex flex-wrap gap-2">
                <!-- Générer maintenant -->
                <button
                  @click="generateNow(rule)"
                  :disabled="!rule.is_active || rule.is_paused || generating[rule.id]"
                  class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="!generating[rule.id]" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg v-else class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ generating[rule.id] ? 'Génération...' : 'Générer maintenant' }}
                </button>

                <!-- Pause/Resume -->
                <button
                  v-if="!rule.is_paused"
                  @click="pauseRule(rule)"
                  :disabled="!rule.is_active"
                  class="btn-warning disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                  Pause
                </button>
                <button
                  v-else
                  @click="resumeRule(rule)"
                  class="btn-success"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Reprendre
                </button>

                <!-- Éditer -->
                <Link
                  :href="`/rules/${rule.id}/edit`"
                  class="btn-secondary"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Éditer
                </Link>

                <!-- Supprimer -->
                <button
                  @click="deleteRule(rule)"
                  class="btn-danger"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  Supprimer
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- État vide -->
        <div v-if="rules.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune règle</h3>
          <p class="mt-1 text-sm text-gray-500">
            Créez votre première règle pour automatiser l'envoi de SMS
          </p>
          <div class="mt-6">
            <Link
              href="/rules/create"
              class="btn-primary"
            >
              Créer une règle
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  rules: Array,
});

const generating = ref({});

// Générer maintenant
const generateNow = (rule) => {
  if (!confirm(`Voulez-vous générer les SMS pour "${rule.name}" maintenant ?\n\nCela créera des SMS pour tous les cases éligibles selon les conditions de la règle.`)) {
    return;
  }

  generating.value[rule.id] = true;

  router.post(`/rules/${rule.id}/generate`, {}, {
    preserveScroll: true,
    onFinish: () => {
      generating.value[rule.id] = false;
    },
  });
};

// Pause
const pauseRule = (rule) => {
  const reason = prompt('Raison de la pause (optionnel) :', '');
  if (reason === null) return;

  router.post(`/rules/${rule.id}/pause`, { reason }, {
    preserveScroll: true,
  });
};

// Resume
const resumeRule = (rule) => {
  if (!confirm(`Voulez-vous reprendre la règle "${rule.name}" ?\n\nLa génération automatique reprendra selon la fréquence configurée.`)) return;

  router.post(`/rules/${rule.id}/resume`, {}, {
    preserveScroll: true,
  });
};

// Toggle active
const toggleActive = (rule) => {
  router.put(`/rules/${rule.id}`, {
    is_active: !rule.is_active,
  }, {
    preserveScroll: true,
  });
};

// Delete
const deleteRule = (rule) => {
  if (!confirm(`Êtes-vous sûr de vouloir supprimer la règle "${rule.name}" ?\n\nCette action est irréversible.`)) {
    return;
  }

  router.delete(`/rules/${rule.id}`, {
    preserveScroll: true,
  });
};

// Format fréquence
const formatFrequency = (rule) => {
  const freqMap = {
    'daily': 'Quotidien',
    'weekly': 'Hebdomadaire',
    'monthly': 'Mensuel',
  };
  
  const freq = freqMap[rule.generation_frequency] || rule.generation_frequency;
  return `${freq} à ${rule.generation_time}`;
};

// Format condition
const formatCondition = (rule) => {
  const condMap = {
    'before': 'avant',
    'after': 'après',
    'equals': '=',
  };

  return `${rule.trigger_field} ${condMap[rule.trigger_condition]} ${rule.trigger_value} ${rule.trigger_unit}`;
};

// Format date
const formatDate = (date) => {
  if (!date) return '';
  
  const d = new Date(date);
  const now = new Date();
  const diff = Math.floor((now - d) / 1000);

  if (diff < 0) {
    // Date future
    const absDiff = Math.abs(diff);
    if (absDiff < 3600) return `Dans ${Math.floor(absDiff / 60)} min`;
    if (absDiff < 86400) return `Dans ${Math.floor(absDiff / 3600)}h`;
    if (absDiff < 604800) return `Dans ${Math.floor(absDiff / 86400)}j`;
  } else {
    // Date passée
    if (diff < 60) return 'À l\'instant';
    if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)}h`;
    if (diff < 604800) return `Il y a ${Math.floor(diff / 86400)}j`;
  }

  return d.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

<style scoped>
.btn-primary {
  @apply inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150;
}

.btn-secondary {
  @apply inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150;
}

.btn-warning {
  @apply inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150;
}

.btn-success {
  @apply inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150;
}

.btn-danger {
  @apply inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150;
}
</style>