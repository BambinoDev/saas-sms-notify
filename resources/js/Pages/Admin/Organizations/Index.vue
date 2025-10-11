<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  organizations: Object,
});

const statusColor = (status) => {
  const colors = {
    active: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
    trial: 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
    suspended: 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
    cancelled: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
  };
  return colors[status] || colors.active;
};

const statusLabel = (status) => {
  const labels = {
    active: 'Actif',
    trial: 'Essai',
    suspended: 'Suspendu',
    cancelled: 'Annulé',
  };
  return labels[status] || status;
};
</script>

<template>
  <AdminLayout>
    <Head title="Organizations" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Organizations
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              Gérer vos organisations
            </p>
          </div>
          <Link
            href="/admin/organizations/create"
            class="px-4 py-2 bg-red-600 text-white rounded-lg shadow hover:bg-red-700 transition-colors flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouvelle Organisation
          </Link>
        </div>

        <!-- Organizations List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <Link
            v-for="org in organizations.data"
            :key="org.id"
            :href="`/admin/organizations/${org.id}`"
            class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow p-6 block"
          >
            <!-- Logo & Name -->
            <div class="flex items-center gap-4 mb-4">
              <div
                v-if="org.logo_url"
                class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0"
              >
                <img :src="org.logo_url" :alt="org.name" class="w-full h-full object-cover" />
              </div>
              <div
                v-else
                class="w-16 h-16 rounded-lg flex items-center justify-center text-2xl font-bold text-white flex-shrink-0"
                :style="{ backgroundColor: org.primary_color }"
              >
                {{ org.name.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                  {{ org.name }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                  {{ org.slug }}
                </p>
              </div>
            </div>

            <!-- Status -->
            <div class="mb-4">
              <span :class="['px-2 py-1 text-xs rounded-full font-medium', statusColor(org.status)]">
                {{ statusLabel(org.status) }}
              </span>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Cases</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ org.cases_count?.toLocaleString() || 0 }}
                </p>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">SMS</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ org.sms_queue_count?.toLocaleString() || 0 }}
                </p>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Users</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ org.users?.length || 0 }}
                </p>
              </div>
            </div>

            <!-- Subscription -->
            <div v-if="org.subscription" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
              <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Plan</p>
              <div class="flex items-center justify-between">
                <p class="font-medium text-gray-900 dark:text-white capitalize">
                  {{ org.subscription.plan }}
                </p>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  {{ org.subscription.sms_used?.toLocaleString() || 0 }} / {{ org.subscription.sms_limit?.toLocaleString() || 0 }} SMS
                </span>
              </div>
            </div>
          </Link>
        </div>

        <!-- Empty state -->
        <div v-if="!organizations.data || organizations.data.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune organisation</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Commencez par créer une organisation
          </p>
          <div class="mt-6">
            <Link
              href="/admin/organizations/create"
              class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg shadow hover:bg-red-700 transition-colors"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Nouvelle Organisation
            </Link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="organizations.links && organizations.links.length > 3" class="mt-6">
          <div class="flex items-center justify-center gap-2">
            <Link
              v-for="(link, index) in organizations.links"
              :key="index"
              :href="link.url || '#'"
              :class="[
                'px-3 py-1 border rounded-lg transition-colors',
                link.active
                  ? 'bg-blue-600 text-white border-blue-600'
                  : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300',
                !link.url && 'opacity-50 cursor-not-allowed'
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

