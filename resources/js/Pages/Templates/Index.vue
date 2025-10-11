<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Props from backend
const props = defineProps({
  templates: Object,
  stats: Object,
  filters: Object,
  available_variables: Object,
});

// State
const search = ref(props.filters?.search || '');
const typeFilter = ref(props.filters?.type || 'all');
const statusFilter = ref(props.filters?.status || 'all');
const createModalOpen = ref(false);
const editModalOpen = ref(false);
const deleteModalOpen = ref(false);
const selectedTemplate = ref(null);
const perPage = ref(25);

// Form using Inertia form helper
const form = useForm({
  name: '',
  type: 'reminder',
  language: 'fr',
  status: 'active',
  template: '',
  days_before: 2,
  sending_time: '09:00',
  priority: 1,
  active: true,
});

// Methods
const openCreateModal = () => {
  form.reset();
  form.name = '';
  form.type = 'reminder';
  form.language = 'fr';
  form.status = 'active';
  form.template = '';
  form.days_before = 2;
  form.sending_time = '09:00';
  form.priority = 1;
  form.active = true;
  createModalOpen.value = true;
};

const openEditModal = (template) => {
  selectedTemplate.value = template;
  // IMPORTANT: Créer une copie complète avec toutes les valeurs et fallbacks
  form.name = template.name || '';
  form.type = template.type || 'reminder';
  form.template = template.message || '';
  form.language = template.language || 'fr';
  form.status = template.status || 'active';
  form.days_before = template.days_before || 2;
  form.sending_time = template.sending_time || '09:00';
  form.priority = template.priority || 1;
  form.active = template.status === 'active';
  editModalOpen.value = true;
};

const openDeleteModal = (template) => {
  selectedTemplate.value = template;
  deleteModalOpen.value = true;
};

const closeModals = () => {
  createModalOpen.value = false;
  editModalOpen.value = false;
  deleteModalOpen.value = false;
  selectedTemplate.value = null;
};

const createTemplate = () => {
  form.post('/templates', {
    preserveScroll: true,
    onSuccess: () => {
      closeModals();
    }
  });
};

const updateTemplate = () => {
  form.put(`/templates/${selectedTemplate.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      closeModals();
    }
  });
};

const deleteTemplate = () => {
  router.delete(`/templates/${selectedTemplate.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      closeModals();
    }
  });
};

const duplicateTemplate = (template) => {
  router.post(`/templates/${template.id}/duplicate`, {}, {
    preserveScroll: true,
  });
};

const insertVariable = (variable) => {
  form.template += `{${variable}}`;
};

const getTypeBadge = (type) => {
  const badges = {
    reminder: 'bg-blue-100 text-blue-700',
    confirmation: 'bg-green-100 text-green-700',
    followup: 'bg-purple-100 text-purple-700',
    welcome: 'bg-yellow-100 text-yellow-700',
    notification: 'bg-orange-100 text-orange-700',
  };
  return badges[type] || 'bg-gray-100 text-gray-700';
};

// Apply filters
const applyFilters = () => {
  router.get('/templates', {
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
  <Head title="Templates - S-Remind" />

  <AppLayout>
    
    <!-- Breadcrumb -->
    <template #breadcrumb>
      <nav class="flex items-center space-x-2 text-sm">
        <a href="/dashboard" class="text-dark-500 hover:text-dark-900">{{ t('nav.dashboard') }}</a>
        <svg class="w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-dark-900 font-medium">{{ t('nav.templates') }}</span>
      </nav>
    </template>

    <!-- Page Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-2xl font-bold text-dark-900">SMS Templates</h1>
          <p class="text-sm text-dark-500 mt-0.5">Create and manage your SMS message templates</p>
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
            <span>New Template</span>
          </button>
        </div>
      </div>

      <!-- Stats Row -->
      <div class="grid grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-dark-200 p-4">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-xs text-dark-500 mb-1">Total Templates</p>
              <p class="text-2xl font-bold text-dark-900">{{ props.stats.total }}</p>
            </div>
            <div class="w-10 h-10 bg-primary-50 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-dark-200 p-4">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-xs text-dark-500 mb-1">Active</p>
              <p class="text-2xl font-bold text-success-600">{{ props.stats.active }}</p>
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
              <p class="text-2xl font-bold text-dark-400">{{ props.stats.inactive }}</p>
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
              <p class="text-xs text-dark-500 mb-1">Total Usage</p>
              <p class="text-2xl font-bold text-accent-600">{{ props.stats.total_usage }}</p>
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
          <h3 class="text-base font-semibold text-dark-900">All Templates</h3>
          
          <div class="flex items-center gap-3 flex-1 max-w-2xl">
            <!-- Search -->
            <div class="relative flex-1">
              <input
                v-model="search"
                type="text"
                placeholder="Search templates..."
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
              <option value="confirmation">Confirmation</option>
              <option value="followup">Follow-up</option>
              <option value="welcome">Welcome</option>
              <option value="notification">Notification</option>
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
              <th class="px-6 py-3 text-left text-xs font-medium text-dark-500 uppercase tracking-wider">Template</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-dark-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-dark-500 uppercase tracking-wider">Message Preview</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-dark-500 uppercase tracking-wider">Usage</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-dark-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-dark-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-dark-100">
            <tr 
              v-for="template in props.templates.data" 
              :key="template.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-6 py-3">
                <div>
                  <p class="text-sm font-medium text-dark-900">{{ template.name }}</p>
                  <p class="text-xs text-dark-500">{{ template.language.toUpperCase() }}</p>
                </div>
              </td>
              <td class="px-6 py-3">
                <span :class="['inline-block px-2 py-0.5 text-xs font-medium rounded capitalize', getTypeBadge(template.type)]">
                  {{ template.type }}
                </span>
              </td>
              <td class="px-6 py-3">
                <p class="text-sm text-dark-700 truncate max-w-xs">{{ template.message }}</p>
              </td>
              <td class="px-6 py-3">
                <span class="text-sm font-medium text-dark-900">{{ template.usage_count }}</span>
              </td>
              <td class="px-6 py-3">
                <span 
                  :class="[
                    'inline-block px-2 py-0.5 text-xs font-medium rounded capitalize',
                    template.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
                  ]"
                >
                  {{ template.status }}
                </span>
              </td>
              <td class="px-6 py-3">
                <div class="flex items-center justify-center gap-2">
                  <button 
                    @click="openEditModal(template)"
                    class="text-primary-600 hover:text-primary-700 text-sm font-medium"
                  >
                    {{ t('common.edit') }}
                  </button>
                  <button 
                    @click="duplicateTemplate(template)"
                    class="text-accent-600 hover:text-accent-700 text-sm font-medium"
                  >
                    Duplicate
                  </button>
                  <button 
                    @click="openDeleteModal(template)"
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
            Showing {{ props.templates.from }} to {{ props.templates.to }} of {{ props.templates.total }}
          </span>
        </div>

        <div class="flex items-center gap-1">
          <button 
            v-for="(link, index) in props.templates.links" 
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

    <!-- Create/Edit Modal -->
    <div
      v-if="createModalOpen || editModalOpen"
      class="fixed inset-0 z-50 overflow-y-auto"
      @click="closeModals"
    >
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-dark-900 bg-opacity-50 transition-opacity"></div>
        
        <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6" @click.stop>
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-dark-900">
              {{ createModalOpen ? 'Create New Template' : 'Edit Template' }}
            </h3>
            <button @click="closeModals" class="text-dark-400 hover:text-dark-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="createModalOpen ? createTemplate() : updateTemplate()" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-dark-700 mb-2">Template Name</label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  placeholder="e.g., Reminder J-2"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-dark-700 mb-2">Type</label>
                <select
                  v-model="form.type"
                  class="w-full px-3 py-2 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                >
                  <option value="reminder">Reminder</option>
                  <option value="confirmation">Confirmation</option>
                  <option value="followup">Follow-up</option>
                  <option value="welcome">Welcome</option>
                  <option value="notification">Notification</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-dark-700 mb-2">Language</label>
                <select
                  v-model="form.language"
                  class="w-full px-3 py-2 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                >
                  <option value="fr">Français</option>
                  <option value="en">English</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-dark-700 mb-2">Status</label>
                <select
                  v-model="form.status"
                  class="w-full px-3 py-2 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-dark-700 mb-2">Message</label>
              <textarea
                v-model="form.template"
                rows="5"
                required
                class="w-full px-3 py-2 border border-dark-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="Type your message here... Use {case_name} for variables"
              ></textarea>
              <p class="text-xs text-dark-500 mt-2">
                Character count: {{ form.template.length }} / 160
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-dark-700 mb-2">Insert Variables</label>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="(label, variable) in props.available_variables"
                  :key="variable"
                  type="button"
                  @click="insertVariable(variable)"
                  class="px-2 py-1 text-xs bg-dark-100 hover:bg-dark-200 text-dark-700 rounded transition-colors"
                  :title="label"
                >
                  {{'{'}}{{ variable }}{{'}'}}
                </button>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
              <button
                type="button"
                @click="closeModals"
                class="px-4 py-2 text-sm border border-dark-300 text-dark-700 hover:bg-dark-50 rounded-lg transition-colors"
              >
                {{ t('common.cancel') }}
              </button>
              <button
                type="submit"
                class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors"
              >
                {{ createModalOpen ? 'Create Template' : 'Update Template' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div
      v-if="deleteModalOpen"
      class="fixed inset-0 z-50 overflow-y-auto"
      @click="closeModals"
    >
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-dark-900 bg-opacity-50 transition-opacity"></div>
        
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" @click.stop>
          <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>

          <h3 class="text-lg font-semibold text-dark-900 text-center mb-2">Delete Template</h3>
          <p class="text-sm text-dark-600 text-center mb-6">
            Are you sure you want to delete "<strong>{{ selectedTemplate?.name }}</strong>"? This action cannot be undone.
          </p>

          <div class="flex items-center gap-3">
            <button
              @click="closeModals"
              class="flex-1 px-4 py-2 text-sm border border-dark-300 text-dark-700 hover:bg-dark-50 rounded-lg transition-colors"
            >
              {{ t('common.cancel') }}
            </button>
            <button
              @click="deleteTemplate"
              class="flex-1 px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

  </AppLayout>
</template>
