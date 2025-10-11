<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  cases: Object,
  stats: Object,
  filters: Object,
});

// Filtres locaux avec valeurs par défaut SÛRES
const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || 'all');
const perPage = ref(Number(props.filters?.per_page) || 25); // ← IMPORTANT: Number() + fallback

// Fonction debounce simple sans dépendance
let searchTimeout = null;
const performSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get('/cases', {
      search: search.value,
      status: status.value,
      per_page: perPage.value,
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  }, 500);
};

// Changer le filtre de statut
const changeStatus = (newStatus) => {
  status.value = newStatus;
  router.get('/cases', {
    search: search.value,
    status: newStatus,
    per_page: perPage.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

// Changer per_page - CORRECTION ICI
const changePerPage = () => {
  router.get('/cases', {
    search: search.value,
    status: status.value,
    per_page: perPage.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

// Exporter CSV
const exportCases = () => {
  window.location.href = '/cases/export';
};
</script>

<template>
  <AppLayout>
    <Head title="Cases" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Cases
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              Gérez tous vos cases CommCare en un seul endroit
            </p>
          </div>
          <button
            @click="exportCases"
            class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg shadow hover:shadow-md transition-shadow flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Exporter
          </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Cases</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats?.total?.toLocaleString() || 0 }}
                </p>
              </div>
              <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Actifs</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats?.active?.toLocaleString() || 0 }}
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
                <p class="text-sm text-gray-600 dark:text-gray-400">Inactifs</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ ((stats?.total || 0) - (stats?.active || 0)).toLocaleString() }}
                </p>
              </div>
              <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-lg">
                <svg class="w-8 h-8 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Avec téléphone</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats?.with_phone?.toLocaleString() || 0 }}
                </p>
              </div>
              <div class="p-3 bg-cyan-100 dark:bg-cyan-900 rounded-lg">
                <svg class="w-8 h-8 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
          <div class="p-6">
            <div class="flex flex-col md:flex-row gap-4">
              <!-- Search -->
              <div class="flex-1">
                <input
                  v-model="search"
                  @input="performSearch"
                  type="text"
                  placeholder="Rechercher par nom, ID ou téléphone..."
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                />
              </div>

              <!-- Status Filter -->
              <div class="flex gap-2">
                <button
                  @click="changeStatus('all')"
                  :class="[
                    'px-4 py-2 rounded-lg transition-colors',
                    status === 'all'
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                  ]"
                >
                  Tous
                </button>
                <button
                  @click="changeStatus('active')"
                  :class="[
                    'px-4 py-2 rounded-lg transition-colors',
                    status === 'active'
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                  ]"
                >
                  Actifs
                </button>
                <button
                  @click="changeStatus('eligible')"
                  :class="[
                    'px-4 py-2 rounded-lg transition-colors',
                    status === 'eligible'
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                  ]"
                >
                  Éligibles
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Case
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Téléphone
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Structure
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    District
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Statut
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Dernière synchro
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="case_ in cases?.data || []" :key="case_.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4">
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ case_.case_name }}
                      </div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ case_.case_id }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                    {{ case_.phone || 'Pas de téléphone' }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                    {{ case_.structure || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                    {{ case_.district || 'N/A' }}
                  </td>
                  <td class="px-6 py-4">
                    <span
                      :class="[
                        'px-2 py-1 text-xs rounded-full',
                        case_.status === 'active'
                          ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                          : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'
                      ]"
                    >
                      {{ case_.status === 'active' ? 'Actif' : 'Fermé' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                    {{ case_.last_sync }}
                  </td>
                  <td class="px-6 py-4 text-right text-sm">
                    <Link
                      :href="`/cases/${case_.id}`"
                      class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                      Voir détails
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-4">
              <span class="text-sm text-gray-700 dark:text-gray-300">
                Affichage
              </span>
              <select
                v-model.number="perPage"
                @change="changePerPage"
                class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500"
              >
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
              <span class="text-sm text-gray-700 dark:text-gray-300">
                sur {{ cases?.total || 0 }} entrées
              </span>
            </div>

            <div class="flex items-center gap-2">
              <!-- Previous -->
              <Link
                v-if="cases?.prev_page_url"
                :href="cases.prev_page_url"
                preserve-state
                preserve-scroll
                class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-white transition-colors"
              >
                Précédent
              </Link>
              <span v-else class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-400 cursor-not-allowed">
                Précédent
              </span>

              <!-- Page Numbers -->
              <template v-for="(link, index) in cases?.links || []" :key="index">
                <Link
                  v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'"
                  :href="link.url || '#'"
                  preserve-state
                  preserve-scroll
                  :class="[
                    'px-3 py-1 border rounded-lg transition-colors',
                    link.active
                      ? 'bg-blue-600 text-white border-blue-600'
                      : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-white'
                  ]"
                  v-html="link.label"
                />
              </template>

              <!-- Next -->
              <Link
                v-if="cases?.next_page_url"
                :href="cases.next_page_url"
                preserve-state
                preserve-scroll
                class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-white transition-colors"
              >
                Suivant
              </Link>
              <span v-else class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-400 cursor-not-allowed">
                Suivant
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
