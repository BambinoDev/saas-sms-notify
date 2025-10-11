<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  organization: Object,
  stats: Object,
});

const form = useForm({
  name: props.organization.name,
  primary_color: props.organization.primary_color,
  secondary_color: props.organization.secondary_color,
  logo_url: props.organization.logo_url || '',
});

const submit = () => {
  form.put('/organization/settings/general', {
    preserveScroll: true,
  });
};
</script>

<template>
  <AppLayout>
    <Head title="Paramètres de l'organisation" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Paramètres de l'organisation
          </h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">
            Gérer les informations et le branding de votre organisation
          </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Sidebar Navigation -->
          <div class="lg:col-span-1">
            <nav class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 space-y-1">
              <a
                href="/organization/settings/general"
                class="flex items-center gap-3 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg font-medium"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Général
              </a>
              <a
                href="/organization/settings/members"
                class="flex items-center gap-3 px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Membres
              </a>
              <a
                href="/organization/settings/billing"
                class="flex items-center gap-3 px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Facturation
              </a>
              <a
                href="/organization/settings/api"
                class="flex items-center gap-3 px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                API
              </a>
            </nav>
          </div>

          <!-- Main Content -->
          <div class="lg:col-span-2">
            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
              <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
                Informations générales
              </h2>

              <div class="space-y-6">
                <!-- Name -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nom de l'organisation <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    placeholder="Nom de votre organisation"
                  />
                  <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                    {{ form.errors.name }}
                  </p>
                </div>

                <!-- Logo URL -->
                <div>
                  <label for="logo_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    URL du logo
                  </label>
                  <input
                    id="logo_url"
                    v-model="form.logo_url"
                    type="url"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    placeholder="https://exemple.com/logo.png"
                  />
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    URL complète de l'image de votre logo
                  </p>
                  <p v-if="form.errors.logo_url" class="mt-1 text-sm text-red-600 dark:text-red-400">
                    {{ form.errors.logo_url }}
                  </p>
                  
                  <!-- Logo preview -->
                  <div v-if="form.logo_url" class="mt-3">
                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">Aperçu :</p>
                    <img :src="form.logo_url" alt="Logo preview" class="w-32 h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700" />
                  </div>
                </div>

                <!-- Colors -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                    Couleurs du branding
                  </label>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label for="primary_color" class="block text-sm text-gray-600 dark:text-gray-400 mb-2">
                        Couleur principale <span class="text-red-500">*</span>
                      </label>
                      <div class="flex items-center gap-3">
                        <input
                          id="primary_color"
                          v-model="form.primary_color"
                          type="color"
                          required
                          class="w-16 h-12 border border-gray-300 dark:border-gray-600 rounded cursor-pointer"
                        />
                        <input
                          v-model="form.primary_color"
                          type="text"
                          required
                          pattern="^#[0-9A-F]{6}$"
                          class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white font-mono uppercase"
                          placeholder="#3B82F6"
                        />
                      </div>
                      <p v-if="form.errors.primary_color" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ form.errors.primary_color }}
                      </p>
                    </div>

                    <div>
                      <label for="secondary_color" class="block text-sm text-gray-600 dark:text-gray-400 mb-2">
                        Couleur secondaire <span class="text-red-500">*</span>
                      </label>
                      <div class="flex items-center gap-3">
                        <input
                          id="secondary_color"
                          v-model="form.secondary_color"
                          type="color"
                          required
                          class="w-16 h-12 border border-gray-300 dark:border-gray-600 rounded cursor-pointer"
                        />
                        <input
                          v-model="form.secondary_color"
                          type="text"
                          required
                          pattern="^#[0-9A-F]{6}$"
                          class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white font-mono uppercase"
                          placeholder="#10B981"
                        />
                      </div>
                      <p v-if="form.errors.secondary_color" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ form.errors.secondary_color }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Preview -->
                <div class="p-6 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                  <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Aperçu du branding</p>
                  <div class="flex items-center gap-4">
                    <div
                      v-if="form.logo_url"
                      class="w-20 h-20 rounded-lg overflow-hidden"
                    >
                      <img :src="form.logo_url" :alt="form.name" class="w-full h-full object-cover" />
                    </div>
                    <div
                      v-else
                      class="w-20 h-20 rounded-lg flex items-center justify-center text-3xl font-bold text-white"
                      :style="{ backgroundColor: form.primary_color }"
                    >
                      {{ form.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ form.name }}</p>
                      <div class="flex items-center gap-2 mt-2">
                        <div class="w-10 h-10 rounded" :style="{ backgroundColor: form.primary_color }"></div>
                        <div class="w-10 h-10 rounded" :style="{ backgroundColor: form.secondary_color }"></div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Stats Info -->
                <div v-if="stats" class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                  <p class="text-sm font-medium text-blue-900 dark:text-blue-200 mb-2">Statistiques de l'organisation</p>
                  <div class="grid grid-cols-3 gap-4">
                    <div>
                      <p class="text-xs text-blue-700 dark:text-blue-300">Cases</p>
                      <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ stats.total_cases?.toLocaleString() || 0 }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-blue-700 dark:text-blue-300">SMS envoyés</p>
                      <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ stats.sms_sent?.toLocaleString() || 0 }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-blue-700 dark:text-blue-300">Règles actives</p>
                      <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ stats.active_rules?.toLocaleString() || 0 }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="px-6 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="form.processing">Enregistrement...</span>
                  <span v-else>Enregistrer les modifications</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

