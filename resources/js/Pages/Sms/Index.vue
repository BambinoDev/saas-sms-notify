<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  smsQueue: Object,
  stats: Object,
  filters: Object,
});

// Filtres locaux
const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || 'all');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const perPage = ref(props.filters?.per_page || 25);

// Recherche avec debounce simple
let searchTimeout = null;
const performSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get('/sms', {
      search: search.value,
      status: status.value,
      date_from: dateFrom.value,
      date_to: dateTo.value,
      per_page: perPage.value,
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  }, 500);
};

// Changer statut
const changeStatus = (newStatus) => {
  status.value = newStatus;
  router.get('/sms', {
    search: search.value,
    status: newStatus,
    date_from: dateFrom.value,
    date_to: dateTo.value,
    per_page: perPage.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

// Appliquer filtres date
const applyDateFilters = () => {
  router.get('/sms', {
    search: search.value,
    status: status.value,
    date_from: dateFrom.value,
    date_to: dateTo.value,
    per_page: perPage.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

// Reset filtres
const resetFilters = () => {
  search.value = '';
  status.value = 'all';
  dateFrom.value = '';
  dateTo.value = '';
  router.get('/sms');
};

// Retry single SMS
const retrySms = (smsId) => {
  if (!confirm('Renvoyer ce SMS ?')) return;
  
  router.post(`/sms/${smsId}/retry`, {}, {
    onSuccess: () => {
      // Success handled by flash message
    },
  });
};

// Retry all failed
const retryAllFailed = () => {
  if (!confirm(`Remettre les ${props.stats?.failed || 0} SMS failed en attente ?`)) return;
  
  router.post('/sms/retry-all-failed', {}, {
    onSuccess: () => {
      // Success
    },
  });
};

// Export
const exportSms = () => {
  window.location.href = `/sms/export?status=${status.value}&date_from=${dateFrom.value}&date_to=${dateTo.value}`;
};

// Formatage date
const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleString('fr-FR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  });
};

// Badge couleur statut
const statusColor = (status) => {
  const colors = {
    pending: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
    sent: 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
    delivered: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
    failed: 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
  };
  return colors[status] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
};
</script>

<template>
  <AppLayout>
    <Head title="SMS Queue" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              SMS Queue
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              Monitor and manage all SMS messages
            </p>
          </div>
          <div class="flex gap-3">
            <button
              v-if="stats?.failed > 0"
              @click="retryAllFailed"
              class="px-4 py-2 bg-orange-600 text-white rounded-lg shadow hover:bg-orange-700 transition-colors flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Retry All Failed ({{ stats.failed }})
            </button>
            <button
              @click="exportSms"
              class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg shadow hover:shadow-md transition-shadow flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Exporter
            </button>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total SMS</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats?.total?.toLocaleString() || 0 }}
                </p>
              </div>
              <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Pending</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats?.pending?.toLocaleString() || 0 }}
                </p>
              </div>
              <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Sent</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats?.sent?.toLocaleString() || 0 }}
                </p>
              </div>
              <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Delivered</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats?.delivered?.toLocaleString() || 0 }}
                </p>
              </div>
              <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Failed</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats?.failed?.toLocaleString() || 0 }}
                </p>
              </div>
              <div class="p-3 bg-red-100 dark:bg-red-900 rounded-lg">
                <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6 p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search -->
            <div class="lg:col-span-2">
              <input
                v-model="search"
                @input="performSearch"
                type="text"
                placeholder="Search by recipient or phone..."
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              />
            </div>

            <!-- Status Filter -->
            <div>
              <select
                v-model="status"
                @change="changeStatus(status)"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              >
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="sent">Sent</option>
                <option value="delivered">Delivered</option>
                <option value="failed">Failed</option>
              </select>
            </div>

            <!-- Date From -->
            <div>
              <input
                v-model="dateFrom"
                type="date"
                placeholder="From"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              />
            </div>

            <!-- Date To -->
            <div>
              <input
                v-model="dateTo"
                type="date"
                placeholder="To"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              />
            </div>
          </div>

          <div class="mt-4 flex gap-3">
            <button
              @click="applyDateFilters"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              Apply Filters
            </button>
            <button
              @click="resetFilters"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              Reset
            </button>
          </div>
        </div>

        <!-- SMS Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Recipient</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Template</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Scheduled</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="sms in smsQueue?.data || []" :key="sms.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4">
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ sms.recipient_name }}
                      </div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ sms.phone_number }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ sms.template_name }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      Type: {{ sms.template_type }}
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                    {{ formatDate(sms.scheduled_at) }}
                  </td>
                  <td class="px-6 py-4">
                    <span :class="['px-2 py-1 text-xs rounded-full', statusColor(sms.status)]">
                      {{ sms.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right text-sm space-x-2">
                    <Link
                      :href="`/sms/${sms.id}`"
                      class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    >
                      Détails
                    </Link>
                    <button
                      v-if="sms.status === 'failed'"
                      @click.stop="retrySms(sms.id)"
                      class="text-orange-600 hover:text-orange-700 dark:text-orange-400"
                    >
                      Retry
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
              Showing {{ smsQueue?.from || 0 }} to {{ smsQueue?.to || 0 }} of {{ smsQueue?.total || 0 }}
            </div>

            <div class="flex items-center gap-2">
              <Link
                v-if="smsQueue?.prev_page_url"
                :href="smsQueue.prev_page_url"
                preserve-state
                preserve-scroll
                class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-white"
              >
                Previous
              </Link>
              <span v-else class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-400 cursor-not-allowed">
                Previous
              </span>

              <template v-for="(link, index) in smsQueue?.links || []" :key="index">
                <Link
                  v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'"
                  :href="link.url || '#'"
                  preserve-state
                  preserve-scroll
                  :class="[
                    'px-3 py-1 border rounded-lg',
                    link.active
                      ? 'bg-blue-600 text-white border-blue-600'
                      : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-white'
                  ]"
                  v-html="link.label"
                />
              </template>

              <Link
                v-if="smsQueue?.next_page_url"
                :href="smsQueue.next_page_url"
                preserve-state
                preserve-scroll
                class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-white"
              >
                Next
              </Link>
              <span v-else class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-400 cursor-not-allowed">
                Next
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
