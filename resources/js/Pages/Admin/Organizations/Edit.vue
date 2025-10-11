<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  organization: Object,
});

const form = useForm({
  name: props.organization.name,
  primary_color: props.organization.primary_color,
  secondary_color: props.organization.secondary_color,
  logo_url: props.organization.logo_url || '',
});

const submit = () => {
  form.put(`/admin/organizations/${props.organization.id}`, {
    preserveScroll: true,
  });
};
</script>

<template>
  <AdminLayout>
    <Head title="Éditer Organisation" />

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-center gap-4 mb-4">
            <Link :href="`/admin/organizations/${organization.id}`" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </Link>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Éditer Organisation
            </h1>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="primary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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
                <label for="secondary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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

            <!-- Preview -->
            <div class="p-6 border border-gray-200 dark:border-gray-700 rounded-lg">
              <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Aperçu du branding</p>
              <div class="flex items-center gap-4">
                <div
                  v-if="form.logo_url"
                  class="w-16 h-16 rounded-lg overflow-hidden"
                >
                  <img :src="form.logo_url" :alt="form.name" class="w-full h-full object-cover" />
                </div>
                <div
                  v-else
                  class="w-16 h-16 rounded-lg flex items-center justify-center text-2xl font-bold text-white"
                  :style="{ backgroundColor: form.primary_color }"
                >
                  {{ form.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="font-semibold text-gray-900 dark:text-white">{{ form.name }}</p>
                  <div class="flex items-center gap-2 mt-1">
                    <div class="w-8 h-8 rounded" :style="{ backgroundColor: form.primary_color }"></div>
                    <div class="w-8 h-8 rounded" :style="{ backgroundColor: form.secondary_color }"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <Link
              :href="`/admin/organizations/${organization.id}`"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              Annuler
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2 bg-red-600 text-white rounded-lg shadow hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="form.processing">Enregistrement...</span>
              <span v-else>Enregistrer</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

