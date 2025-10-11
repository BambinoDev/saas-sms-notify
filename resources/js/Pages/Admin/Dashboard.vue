<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  stats: Object,
  recent_organizations: Array,
  organizations_by_status: Object,
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
    <Head title="Admin Dashboard" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Dashboard Administration
          </h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">
            Vue d'ensemble de la plateforme SAAS
          </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Organizations -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Organizations</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                  {{ stats.total_organizations }}
                </p>
                <p class="text-sm text-green-600 dark:text-green-400 mt-1">
                  {{ stats.active_organizations }} actives
                </p>
              </div>
              <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Users -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Utilisateurs</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                  {{ stats.total_users }}
                </p>
                <p class="text-sm text-red-600 dark:text-red-400 mt-1">
                  {{ stats.superadmins }} admins
                </p>
              </div>
              <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- SMS -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">SMS Totaux</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                  {{ stats.total_sms?.toLocaleString() }}
                </p>
                <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                  {{ stats.sms_sent?.toLocaleString() }} envoyés
                </p>
              </div>
              <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Revenue -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Revenue Mensuel</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                  {{ stats.monthly_revenue?.toLocaleString() || 0 }} €
                </p>
                <p class="text-sm text-green-600 dark:text-green-400 mt-1">
                  MRR
                </p>
              </div>
              <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Organizations -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Organisations Récentes
            </h2>
            <Link href="/admin/organizations" class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
              Voir toutes →
            </Link>
          </div>
          <div class="p-6">
            <div v-if="recent_organizations && recent_organizations.length > 0" class="space-y-4">
              <Link
                v-for="org in recent_organizations"
                :key="org.id"
                :href="`/admin/organizations/${org.id}`"
                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
              >
                <div class="flex items-center gap-4">
                  <div
                    class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold"
                    :style="{ backgroundColor: org.primary_color }"
                  >
                    {{ org.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">
                      {{ org.name }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      {{ org.slug }}
                    </p>
                  </div>
                </div>
                <div class="text-right">
                  <span :class="['text-xs px-2 py-1 rounded-full font-medium', statusColor(org.status)]">
                    {{ statusLabel(org.status) }}
                  </span>
                  <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                    {{ org.subscription?.plan || 'N/A' }}
                  </p>
                </div>
              </Link>
            </div>
            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
              Aucune organisation récente
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

