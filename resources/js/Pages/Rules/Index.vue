<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Props from backend
const props = defineProps({
  rules: Object,
  templates: Array,
  stats: Object,
  filters: Object,
});

// State
const search = ref(props.filters?.search || '');
const typeFilter = ref(props.filters?.type || 'all');
const statusFilter = ref(props.filters?.status || 'all');
const showModal = ref(false);
const modalMode = ref('create');
const editingRule = ref(null);
const perPage = ref(25);

// Form data avec valeurs par défaut
const form = ref({
  id: null,
  name: '',
  type: 'reminder',
  days_before: 0,
  sending_time: '09:00',
  priority: 1,
  window_start: '09:00',
  window_end: '21:00',
  template: '',
  status: 'active',
});

// Character count
const characterCount = computed(() => form.value.template?.length || 0);
const maxCharacters = 160;

// Ouvrir modal création
const openCreateModal = () => {
  modalMode.value = 'create';
  form.value = {
    id: null,
    name: '',
    type: 'reminder',
    days_before: 0,
    sending_time: '09:00',
    priority: 1,
    window_start: '09:00',
    window_end: '21:00',
    template: '',
    status: 'active',
  };
  showModal.value = true;
};

// Ouvrir modal édition
const openEditModal = (rule) => {
  modalMode.value = 'edit';
  editingRule.value = rule;
  
  form.value = {
    id: rule.id,
    name: rule.name || '',
    type: rule.type || 'reminder',
    days_before: rule.days_before || 0,
    sending_time: rule.sending_time || '09:00',
    priority: rule.priority || 1,
    window_start: rule.window_start || '09:00',
    window_end: rule.window_end || '21:00',
    template: rule.template || '',
    status: rule.status || 'active',
  };
  
  showModal.value = true;
};

// Fermer modal
const closeModal = () => {
  showModal.value = false;
  editingRule.value = null;
};

// Sauvegarder rule - CORRECTION ICI
const saveRule = () => {
  // Normaliser les heures avant envoi (enlever les secondes si présentes: "09:00:00" -> "09:00")
  const dataToSend = {
    name: form.value.name,
    type: form.value.type,
    days_before: form.value.days_before,
    sending_time: form.value.sending_time?.substring(0, 5) || '09:00',
    priority: form.value.priority,
    template: form.value.template,
    status: form.value.status,
    // window_start et window_end NON ENVOYÉS (colonnes pas encore migrées)
  };

  console.log('Data to send:', dataToSend); // Debug

  if (modalMode.value === 'create') {
    // Création
    router.post('/rules', dataToSend, {
      onSuccess: () => {
        closeModal();
      },
      onError: (errors) => {
        console.error('Creation errors:', errors);
        alert('Erreur lors de la création:\n' + Object.entries(errors).map(([k, v]) => `${k}: ${v}`).join('\n'));
      },
    });
  } else {
    // Mise à jour
    router.put(`/rules/${form.value.id}`, dataToSend, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        closeModal();
      },
      onError: (errors) => {
        console.error('Update errors:', errors);
        alert('Erreur lors de la mise à jour:\n' + Object.entries(errors).map(([k, v]) => `${k}: ${v}`).join('\n'));
      },
    });
  }
};

// Supprimer rule
const deleteRule = (ruleId) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette règle ?')) {
    return;
  }

  router.delete(`/rules/${ruleId}`, {
    onSuccess: () => {
      // Success
    },
    onError: (errors) => {
      console.error('Delete errors:', errors);
      alert('Erreur lors de la suppression: ' + JSON.stringify(errors));
    },
  });
};

// Toggle rule status
const toggleRuleStatus = (rule) => {
  router.post(`/rules/${rule.id}/toggle`, {}, {
    preserveScroll: true,
  });
};

// Copier template depuis une règle existante
const copyTemplate = (ruleId) => {
  if (!ruleId) return;
  
  const rule = props.templates.find(t => t.id === parseInt(ruleId));
  if (rule && rule.message) {
    form.value.template = rule.message;
  }
};

// Get type badge
const getTypeBadge = (type) => {
  const badges = {
    reminder: 'bg-blue-100 text-blue-700',
    followup: 'bg-purple-100 text-purple-700',
    confirmation: 'bg-green-100 text-green-700',
  };
  return badges[type] || 'bg-gray-100 text-gray-700';
};

// Get days label
const getDaysLabel = (days) => {
  if (days > 0) return `J-${days}`;
  if (days < 0) return `J+${Math.abs(days)}`;
  return 'J-Day';
};

// Apply filters
const applyFilters = () => {
  router.get('/rules', {
    search: search.value,
    type: typeFilter.value,
    status: statusFilter.value,
    per_page: perPage.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Rules - S-Remind" />

  <AppLayout>
    
    <!-- Breadcrumb -->
    <template #breadcrumb>
      <nav class="flex items-center space-x-2 text-sm">
        <a href="/dashboard" class="text-dark-500 hover:text-dark-900">{{ t('nav.dashboard') }}</a>
        <svg class="w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-dark-900 font-medium">{{ t('nav.rules') }}</span>
      </nav>
    </template>

    <!-- Page Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-2xl font-bold text-dark-900">SMS Rules</h1>
          <p class="text-sm text-dark-500 mt-0.5">Configure when and how SMS reminders are sent</p>
        </div>
        <div class="flex items-center space-x-2">
          <button class="px-3 py-2 text-sm border border-dark-300 text-dark-700 hover:bg-dark-50 rounded-lg flex items-center space-x-1.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span>{{ t('common.export') }}</span>
          </button>
          <button @click="openCreateModal" class="px-3 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg flex items-center space-x-1.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>New Rule</span>
          </button>
        </div>
      </div>

      <!-- Stats Row -->
      <div class="grid grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-dark-200 p-4">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-xs text-dark-500 mb-1">Total Rules</p>
              <p class="text-2xl font-bold text-dark-900">{{ props.stats?.total || 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-primary-50 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-dark-200 p-4">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-xs text-dark-500 mb-1">Active</p>
              <p class="text-2xl font-bold text-success-600">{{ props.stats?.active || 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-success-50 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-dark-200 p-4">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-xs text-dark-500 mb-1">Inactive</p>
              <p class="text-2xl font-bold text-dark-400">{{ props.stats?.inactive || 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-dark-50 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-dark-200 p-4">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-xs text-dark-500 mb-1">Total Sent</p>
              <p class="text-2xl font-bold text-accent-600">{{ props.stats?.total_sent || 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-accent-50 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-dark-200 overflow-hidden">
      
      <!-- Header with Filters -->
      <div class="p-4 border-b border-dark-200">
        <div class="flex items-center justify-between gap-4">
          <h3 class="text-base font-semibold text-dark-900">All Rules</h3>
          
          <div class="flex items-center gap-3 flex-1 max-w-2xl">
            <!-- Search -->
            <div class="relative flex-1">
              <input
                v-model="search"
                type="text"
                placeholder="Search rules..."
                class="w-full pl-9 pr-4 py-1.5 text-sm border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              />
              <svg class="absolute left-3 top-2 w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            <!-- Type Filter -->
            <select
              v-model="typeFilter"
              class="px-3 py-1.5 text-sm border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="all">All Types</option>
              <option value="reminder">Reminder</option>
              <option value="followup">Follow-up</option>
              <option value="confirmation">Confirmation</option>
            </select>

            <!-- Status Filter -->
            <select
              v-model="statusFilter"
              class="px-3 py-1.5 text-sm border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="all">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-dark-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-dark-500 uppercase tracking-wider">Rule Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-dark-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-dark-500 uppercase tracking-wider">Timing</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-dark-500 uppercase tracking-wider">Template</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-dark-500 uppercase tracking-wider">Sent</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-dark-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-dark-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-dark-100">
            <tr 
              v-for="rule in props.rules?.data || []" 
              :key="rule.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-6 py-3">
                <div>
                  <p class="text-sm font-medium text-dark-900">{{ rule.name }}</p>
                  <p class="text-xs text-dark-500">Priority: {{ rule.priority }}</p>
                </div>
              </td>
              <td class="px-6 py-3">
                <span :class="['inline-block px-2 py-0.5 text-xs font-medium rounded capitalize', getTypeBadge(rule.type)]">
                  {{ rule.type }}
                </span>
              </td>
              <td class="px-6 py-3">
                <div>
                  <p class="text-sm font-medium text-dark-900">{{ getDaysLabel(rule.days_before) }}</p>
                  <p class="text-xs text-dark-500">at {{ rule.sending_time }}</p>
                </div>
              </td>
              <td class="px-6 py-3">
                <p class="text-sm text-dark-700 truncate max-w-xs">{{ rule.template || 'N/A' }}</p>
              </td>
              <td class="px-6 py-3">
                <span class="text-sm font-medium text-dark-900">{{ rule.sent_count || 0 }}</span>
              </td>
              <td class="px-6 py-3">
                <button
                  @click="toggleRuleStatus(rule)"
                  :class="[
                    'inline-flex items-center px-2 py-0.5 text-xs font-medium rounded capitalize transition-colors',
                    rule.status === 'active' 
                      ? 'bg-green-100 text-green-700 hover:bg-green-200' 
                      : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                  ]"
                >
                  <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="rule.status === 'active' ? 'bg-green-500' : 'bg-gray-400'"></span>
                  {{ rule.status }}
                </button>
              </td>
              <td class="px-6 py-3">
                <div class="flex items-center justify-center gap-2">
                  <button 
                    @click="openEditModal(rule)"
                    class="text-primary-600 hover:text-primary-700 text-sm font-medium"
                  >
                    {{ t('common.edit') }}
                  </button>
                  <button 
                    @click="deleteRule(rule.id)"
                    class="text-red-600 hover:text-red-700 text-sm font-medium"
                  >
                    {{ t('common.delete') }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer with Pagination -->
      <div class="px-6 py-3 border-t border-dark-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="text-sm text-dark-600">
            Showing {{ props.rules?.from || 0 }} to {{ props.rules?.to || 0 }} of {{ props.rules?.total || 0 }}
          </span>
        </div>

        <div class="flex items-center gap-1">
          <button 
            v-for="(link, index) in props.rules?.links || []" 
            :key="index"
            @click="link.url && router.get(link.url)"
            :class="[
              'px-3 py-1.5 text-sm rounded transition-colors',
              link.active ? 'bg-primary-600 text-white font-medium' : 'border border-dark-300 text-dark-700 hover:bg-dark-50',
              !link.url && 'opacity-50 cursor-not-allowed'
            ]"
            :disabled="!link.url"
            v-html="link.label"
          ></button>
        </div>
      </div>

    </div>

    <!-- Edit/Create Modal -->
    <Teleport to="body">
      <div
        v-if="showModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <!-- Background overlay -->
          <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            @click="closeModal"
          ></div>

          <!-- Modal panel -->
          <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ modalMode === 'create' ? 'Create Rule' : 'Edit Rule' }}
                </h3>
                <button
                  @click="closeModal"
                  class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <form @submit.prevent="saveRule" class="space-y-4">
                <!-- Rule Name & Type -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Rule Name
                    </label>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Type
                    </label>
                    <select
                      v-model="form.type"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    >
                      <option value="reminder">Reminder</option>
                      <option value="confirmation">Confirmation</option>
                      <option value="notification">Notification</option>
                    </select>
                  </div>
                </div>

                <!-- Days Before, Sending Time, Priority -->
                <div class="grid grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Days Before/After
                    </label>
                    <input
                      v-model.number="form.days_before"
                      type="number"
                      required
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                      Positive = before, Negative = after
                    </p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Sending Time
                    </label>
                    <input
                      v-model="form.sending_time"
                      type="time"
                      required
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Priority
                    </label>
                    <input
                      v-model.number="form.priority"
                      type="number"
                      min="1"
                      required
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    />
                  </div>
                </div>

                <!-- Template Message -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Template Message
                  </label>
                  <textarea
                    v-model="form.template"
                    rows="4"
                    required
                    :maxlength="maxCharacters"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    placeholder="Bonjour Mme {case_name}, n'oubliez pas..."
                  ></textarea>
                  <div class="mt-1 flex items-center justify-between text-xs">
                    <span class="text-gray-500 dark:text-gray-400">
                      Variables: {case_name}, {anc_number}, {visit_date}, {district}
                    </span>
                    <span class="text-gray-500 dark:text-gray-400">
                      {{ characterCount }} / {{ maxCharacters }}
                    </span>
                  </div>
                </div>

                <!-- Quick Load Template (optionnel) -->
                <div v-if="templates && templates.length > 0">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Quick Load Template
                  </label>
                  <select
                    @change="copyTemplate($event.target.value)"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  >
                    <option value="">-- Copy from existing rule --</option>
                    <option
                      v-for="template in templates"
                      :key="template.id"
                      :value="template.id"
                    >
                      {{ template.name }}
                    </option>
                  </select>
                </div>

                <!-- Status -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Status
                  </label>
                  <select
                    v-model="form.status"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                  >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 mt-6">
                  <button
                    type="button"
                    @click="closeModal"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                  >
                    Annuler
                  </button>
                  <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                  >
                    {{ modalMode === 'create' ? 'Create Rule' : 'Update Rule' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </AppLayout>
</template>
