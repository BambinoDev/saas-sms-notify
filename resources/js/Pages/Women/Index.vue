<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Gestion des Femmes</h2>
          <p class="mt-1 text-sm text-gray-600">
            Liste des femmes enceintes avec filtres avancés
          </p>
        </div>
      </div>

      <!-- Stats rapides -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <div class="bg-white overflow-hidden shadow rounded-lg p-5">
          <div class="text-sm font-medium text-gray-500">Total</div>
          <div class="mt-1 text-3xl font-semibold text-gray-900">
            {{ stats.total.toLocaleString() }}
          </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg p-5">
          <div class="text-sm font-medium text-gray-500">Éligibles SMS</div>
          <div class="mt-1 text-3xl font-semibold text-green-600">
            {{ stats.eligible.toLocaleString() }}
          </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg p-5">
          <div class="text-sm font-medium text-gray-500">Non éligibles</div>
          <div class="mt-1 text-3xl font-semibold text-red-600">
            {{ stats.not_eligible.toLocaleString() }}
          </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg p-5">
          <div class="text-sm font-medium text-gray-500">RDV aujourd'hui</div>
          <div class="mt-1 text-3xl font-semibold text-blue-600">
            {{ stats.rdv_today.toLocaleString() }}
          </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg p-5">
          <div class="text-sm font-medium text-gray-500">RDV cette semaine</div>
          <div class="mt-1 text-3xl font-semibold text-indigo-600">
            {{ stats.rdv_this_week.toLocaleString() }}
          </div>
        </div>
      </div>

      <!-- Filtres -->
      <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
          <div>
            <label class="block text-sm font-medium text-gray-700">Éligibilité</label>
            <select
              v-model="form.eligibility"
              @change="applyFilters"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
            >
              <option value="all">Toutes</option>
              <option value="eligible">Éligibles SMS</option>
              <option value="not_eligible">Non éligibles</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Région</label>
            <select
              v-model="form.region"
              @change="applyFilters"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
            >
              <option value="all">Toutes</option>
              <option v-for="region in regions" :key="region" :value="region">
                {{ region }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">District</label>
            <select
              v-model="form.district"
              @change="applyFilters"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
            >
              <option value="all">Tous</option>
              <option v-for="district in districts" :key="district" :value="district">
                {{ district }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Structure</label>
            <select
              v-model="form.structure"
              @change="applyFilters"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
            >
              <option value="all">Toutes</option>
              <option v-for="structure in structures" :key="structure" :value="structure">
                {{ structure }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">RDV</label>
            <select
              v-model="form.rdv"
              @change="applyFilters"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
            >
              <option value="all">Tous</option>
              <option value="today">Aujourd'hui</option>
              <option value="this_week">Cette semaine</option>
              <option value="this_month">Ce mois</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Par page</label>
            <select
              v-model="form.per_page"
              @change="applyFilters"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
            >
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>
        </div>

        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700">Recherche</label>
          <input
            v-model="form.search"
            @input="debounceSearch"
            type="text"
            placeholder="Nom, case_id, téléphone..."
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          >
        </div>
      </div>

      <!-- Liste des femmes -->
      <div class="bg-white shadow overflow-hidden rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Femme
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Téléphone
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Localisation
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Prochain RDV
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Éligibilité SMS
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                CPN
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="woman in women.data" :key="woman.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ woman.case_name }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ woman.case_id }}
                </div>
                <div v-if="woman.client_age" class="text-xs text-gray-400">
                  {{ woman.client_age }} ans
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ woman.contact_phone_number || 'N/A' }}
                </div>
                <div v-if="woman.husband_phone_number" class="text-xs text-gray-500">
                  Époux: {{ woman.husband_phone_number }}
                </div>
                <div v-if="woman.contact_phone_number_is_verified" class="text-xs text-green-600">
                  ✓ Vérifié
                </div>
                <div v-else class="text-xs text-red-600">
                  ✗ Non vérifié
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">
                  {{ woman.structure_sanitaire || 'N/A' }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ woman.district_sanitaire || 'N/A' }}
                </div>
                <div class="text-xs text-gray-400">
                  {{ woman.region_sanitaire || 'N/A' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ formatDate(woman.next_visit_date) }}
                </div>
                <div v-if="woman.two_days_before_next_visit_date" class="text-xs text-gray-500">
                  Rappel: {{ formatDate(woman.two_days_before_next_visit_date) }}
                </div>
              </td>
              <td class="px-6 py-4">
                <div v-if="getEligibilityStatus(woman).eligible" class="flex items-center">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    Éligible
                  </span>
                </div>
                <div v-else>
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 mb-1">
                    Non éligible
                  </span>
                  <div class="text-xs text-gray-600">
                    {{ getEligibilityStatus(woman).reason }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div>
                  {{ woman.anc_counter }} CPN
                </div>
                <div class="text-xs text-gray-500">
                  Prochaine: {{ woman.next_anc_number }}ème
                </div>
                <div v-if="woman.sms_reminder_counter > 0" class="text-xs text-blue-600">
                  {{ woman.sms_reminder_counter }} SMS envoyés
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Affichage de {{ women.from }} à {{ women.to }} sur {{ women.total }} résultats
            </div>
            <div class="flex gap-2">
              <Link
                v-if="women.prev_page_url"
                :href="women.prev_page_url"
                class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
              >
                Précédent
              </Link>
              <Link
                v-if="women.next_page_url"
                :href="women.next_page_url"
                class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
              >
                Suivant
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
  women: Object,
  stats: Object,
  regions: Array,
  districts: Array,
  structures: Array,
  filters: Object,
});

const form = ref({
  eligibility: props.filters.eligibility,
  region: props.filters.region,
  district: props.filters.district,
  structure: props.filters.structure,
  rdv: props.filters.rdv,
  search: props.filters.search,
  per_page: props.filters.per_page,
});

let searchTimeout = null;

const applyFilters = () => {
  router.get('/women', form.value, {
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

const getEligibilityStatus = (woman) => {
  const eligible = woman.consent_sms_yes 
    && woman.contact_phone_number_is_verified 
    && !woman.closed
    && (woman.contact_phone_number || woman.husband_phone_number);

  if (eligible) {
    return { eligible: true };
  }

  const reasons = [];
  if (!woman.consent_sms_yes) reasons.push('Pas de consentement');
  if (!woman.contact_phone_number_is_verified) reasons.push('Téléphone non vérifié');
  if (!woman.contact_phone_number && !woman.husband_phone_number) reasons.push('Aucun numéro');
  if (woman.closed) reasons.push('Dossier fermé');

  return {
    eligible: false,
    reason: reasons.join(', ')
  };
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('fr-FR');
};
</script>

