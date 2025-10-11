<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  organization: Object,
  stats: Object,
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

const roleLabel = (role) => {
  const labels = {
    owner: 'Propriétaire',
    admin: 'Administrateur',
    manager: 'Gestionnaire',
    user: 'Utilisateur',
  };
  return labels[role] || role;
};

const roleBadge = (role) => {
  const badges = {
    owner: 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200',
    admin: 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
    manager: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
    user: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
  };
  return badges[role] || badges.user;
};

const smsUsagePercentage = () => {
  if (!props.organization.subscription) return 0;
  const { sms_used, sms_limit } = props.organization.subscription;
  return Math.round((sms_used / sms_limit) * 100);
};
</script>

<template>
  <AdminLayout>
    <Head :title="organization.name" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <Link href="/admin/organizations" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </Link>
              
              <div
                v-if="organization.logo_url"
                class="w-16 h-16 rounded-lg overflow-hidden"
              >
                <img :src="organization.logo_url" :alt="organization.name" class="w-full h-full object-cover" />
              </div>
              <div
                v-else
                class="w-16 h-16 rounded-lg flex items-center justify-center text-2xl font-bold text-white"
                :style="{ backgroundColor: organization.primary_color }"
              >
                {{ organization.name.charAt(0).toUpperCase() }}
              </div>
              
              <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                  {{ organization.name }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400">{{ organization.slug }}</p>
              </div>
            </div>
            
            <Link
              :href="`/admin/organizations/${organization.id}/edit`"
              class="px-4 py-2 bg-red-600 text-white rounded-lg shadow hover:bg-red-700 transition-colors"
            >
              Éditer
            </Link>
          </div>
          
          <div class="mt-4 flex items-center gap-4">
            <span :class="['px-3 py-1 text-sm rounded-full font-medium', statusColor(organization.status)]">
              {{ statusLabel(organization.status) }}
            </span>
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-500 dark:text-gray-400">Couleurs:</span>
              <div class="flex items-center gap-1">
                <div class="w-6 h-6 rounded" :style="{ backgroundColor: organization.primary_color }"></div>
                <div class="w-6 h-6 rounded" :style="{ backgroundColor: organization.secondary_color }"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Cases</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                  {{ stats.total_cases?.toLocaleString() || 0 }}
                </p>
              </div>
              <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">SMS Envoyés</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                  {{ stats.sms_sent?.toLocaleString() || 0 }}
                </p>
              </div>
              <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                <svg class="w-6 h-6 text-green-600 dark:text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">SMS En attente</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                  {{ stats.sms_pending?.toLocaleString() || 0 }}
                </p>
              </div>
              <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Règles Actives</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                  {{ stats.active_rules?.toLocaleString() || 0 }}
                </p>
              </div>
              <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Subscription Card -->
          <div v-if="organization.subscription" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Subscription</h2>
            
            <div class="space-y-4">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Plan</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white capitalize">
                  {{ organization.subscription.plan }}
                </p>
              </div>

              <div>
                <div class="flex items-center justify-between mb-1">
                  <p class="text-sm text-gray-600 dark:text-gray-400">Usage SMS</p>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ smsUsagePercentage() }}%
                  </p>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                  <div 
                    class="bg-blue-600 h-2 rounded-full transition-all"
                    :style="{ width: `${smsUsagePercentage()}%` }"
                  ></div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ organization.subscription.sms_used?.toLocaleString() || 0 }} / 
                  {{ organization.subscription.sms_limit?.toLocaleString() || 0 }} SMS
                </p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Limite Utilisateurs</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ organization.subscription.users_limit }}
                  </p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Limite Structures</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ organization.subscription.structures_limit }}
                  </p>
                </div>
              </div>

              <div v-if="organization.subscription.price > 0">
                <p class="text-sm text-gray-600 dark:text-gray-400">Prix</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ organization.subscription.price }} € / mois
                </p>
              </div>
            </div>
          </div>

          <!-- Members Card -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold text-gray-900 dark:text-white">Membres</h2>
              <Link
                :href="`/admin/organizations/${organization.id}/members`"
                class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
              >
                Gérer →
              </Link>
            </div>

            <div class="space-y-3">
              <div 
                v-for="member in organization.users"
                :key="member.id"
                class="flex items-center justify-between py-2"
              >
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-700 dark:text-gray-300 font-medium">
                    {{ member.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ member.name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ member.email }}</p>
                  </div>
                </div>
                <span :class="['px-2 py-1 text-xs rounded-full font-medium', roleBadge(member.pivot.role)]">
                  {{ roleLabel(member.pivot.role) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

