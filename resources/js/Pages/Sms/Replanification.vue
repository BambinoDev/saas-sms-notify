<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Replanification des SMS</h2>
          <p class="mt-1 text-sm text-gray-600">
            Modifier l'heure d'envoi des SMS en attente ou échoués
          </p>
        </div>
        
        <!-- Bouton replanification -->
        <button
          v-if="selectedIds.length > 0"
          @click="showReplanifyModal = true"
          class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Replanifier ({{ selectedIds.length }})
        </button>
      </div>

      <!-- Stats rapides -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
        <div class="bg-white overflow-hidden shadow rounded-lg p-5">
          <div class="text-sm font-medium text-gray-500">Total SMS</div>
          <div class="mt-1 text-3xl font-semibold text-gray-900">
            {{ stats.total.toLocaleString() }}
          </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg p-5">
          <div class="text-sm font-medium text-gray-500">En attente</div>
          <div class="mt-1 text-3xl font-semibold text-yellow-600">
            {{ stats.pending.toLocaleString() }}
          </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg p-5">
          <div class="text-sm font-medium text-gray-500">Échoués</div>
          <div class="mt-1 text-3xl font-semibold text-red-600">
            {{ stats.failed.toLocaleString() }}
          </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg p-5">
          <div class="text-sm font-medium text-gray-500">Envoyés</div>
          <div class="mt-1 text-3xl font-semibold text-green-600">
            {{ stats.sent.toLocaleString() }}
          </div>
        </div>
      </div>

      <!-- Filtres -->
      <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-5">
          <div>
            <label class="block text-sm font-medium text-gray-700">Statut</label>
            <select
              v-model="form.status"
              @change="applyFilters"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
              <option value="all">Tous</option>
              <option value="pending">En attente</option>
              <option value="failed">Échoués</option>
              <option value="sent">Envoyés</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Type</label>
            <select
              v-model="form.type"
              @change="applyFilters"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
              <option value="all">Tous</option>
              <option v-for="type in smsTypes" :key="type" :value="type">
                {{ type }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Date début</label>
            <input
              v-model="form.date_from"
              @change="applyFilters"
              type="date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Date fin</label>
            <input
              v-model="form.date_to"
              @change="applyFilters"
              type="date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Recherche</label>
            <input
              v-model="form.search"
              @input="debounceSearch"
              type="text"
              placeholder="Nom ou case_id..."
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
          </div>
        </div>
      </div>

      <!-- Table SMS avec checkboxes -->
      <div class="bg-white shadow overflow-hidden rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <!-- Checkbox tout sélectionner -->
              <th class="px-6 py-3 text-left">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  @change="toggleSelectAll"
                  class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                >
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Femme</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Planifié</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="sms in sms.data" :key="sms.id">
              <!-- Checkbox sélection -->
              <td class="px-6 py-4 whitespace-nowrap">
                <input
                  v-if="['pending', 'failed'].includes(sms.status)"
                  type="checkbox"
                  :value="sms.id"
                  v-model="selectedIds"
                  class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                >
              </td>
              
              <!-- Colonnes -->
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ sms.woman?.case_name }}</div>
                <div class="text-sm text-gray-500">{{ sms.woman?.case_id }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ sms.recipient_phone }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                  {{ sms.sms_type }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                    getStatusClass(sms.status)
                  ]"
                >
                  {{ getStatusLabel(sms.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(sms.scheduled_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <Link :href="`/sms/${sms.id}`" class="text-indigo-600 hover:text-indigo-900">
                  Détails
                </Link>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Affichage de {{ sms.from }} à {{ sms.to }} sur {{ sms.total }} résultats
            </div>
            <div class="flex gap-2">
              <Link
                v-if="sms.prev_page_url"
                :href="sms.prev_page_url"
                class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
              >
                Précédent
              </Link>
              <Link
                v-if="sms.next_page_url"
                :href="sms.next_page_url"
                class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
              >
                Suivant
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de replanification -->
    <div v-if="showReplanifyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Replanifier {{ selectedIds.length }} SMS</h3>
          
          <form @submit.prevent="replanifySms">
            <!-- Type de replanification -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Type de replanification</label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input
                    v-model="replanifyForm.type"
                    type="radio"
                    value="offset"
                    class="mr-2"
                  >
                  Décalage temporel
                </label>
                <label class="flex items-center">
                  <input
                    v-model="replanifyForm.type"
                    type="radio"
                    value="absolute_time"
                    class="mr-2"
                  >
                  Heure précise
                </label>
              </div>
            </div>

            <!-- Décalage temporel -->
            <div v-if="replanifyForm.type === 'offset'" class="mb-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Heures</label>
                  <input
                    v-model.number="replanifyForm.offset_hours"
                    type="number"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="0"
                  >
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Jours</label>
                  <input
                    v-model.number="replanifyForm.offset_days"
                    type="number"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="0"
                  >
                </div>
              </div>
            </div>

            <!-- Heure précise -->
            <div v-if="replanifyForm.type === 'absolute_time'" class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Nouvelle date et heure</label>
              <input
                v-model="replanifyForm.new_datetime"
                type="datetime-local"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
            </div>

            <!-- Raison -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Raison (optionnel)</label>
              <textarea
                v-model="replanifyForm.reason"
                rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Raison de la replanification..."
              ></textarea>
            </div>

            <!-- Boutons -->
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="showReplanifyModal = false"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                Annuler
              </button>
              <button
                type="submit"
                :disabled="isReplanifying"
                class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
              >
                {{ isReplanifying ? 'Replanification...' : 'Replanifier' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  sms: Object,
  stats: Object,
  smsTypes: Array,
  filters: Object,
});

const form = ref({
  status: props.filters.status,
  type: props.filters.type,
  search: props.filters.search,
  date_from: props.filters.date_from,
  date_to: props.filters.date_to,
});

const selectedIds = ref([]);
const showReplanifyModal = ref(false);
const isReplanifying = ref(false);

const replanifyForm = ref({
  type: 'offset',
  offset_hours: 0,
  offset_days: 0,
  new_datetime: '',
  reason: '',
});

const allSelected = computed(() => {
  const eligibleSms = props.sms.data.filter(s => ['pending', 'failed'].includes(s.status));
  return eligibleSms.length > 0 && selectedIds.value.length === eligibleSms.length;
});

const toggleSelectAll = () => {
  const eligibleSms = props.sms.data.filter(s => ['pending', 'failed'].includes(s.status));
  if (allSelected.value) {
    selectedIds.value = [];
  } else {
    selectedIds.value = eligibleSms.map(s => s.id);
  }
};

const replanifySms = () => {
  if (selectedIds.value.length === 0) return;

  isReplanifying.value = true;

  router.post('/sms/replanification', {
    sms_ids: selectedIds.value,
    replanify_type: replanifyForm.value.type,
    offset_hours: replanifyForm.value.offset_hours,
    offset_days: replanifyForm.value.offset_days,
    new_datetime: replanifyForm.value.new_datetime,
    reason: replanifyForm.value.reason,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedIds.value = [];
      showReplanifyModal.value = false;
      isReplanifying.value = false;
    },
    onError: () => {
      isReplanifying.value = false;
    },
  });
};

let searchTimeout = null;

const applyFilters = () => {
  selectedIds.value = []; // Reset sélection
  router.get('/sms/replanification', form.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('fr-FR');
};

const getStatusLabel = (status) => {
  const labels = {
    pending: 'En attente',
    sent: 'Envoyé',
    delivered: 'Livré',
    failed: 'Échoué',
  };
  return labels[status] || status;
};

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    sent: 'bg-blue-100 text-blue-800',
    delivered: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

