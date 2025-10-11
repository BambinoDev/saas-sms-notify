<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  case: Object,
});

const caseData = computed(() => props.case);
const pageTitle = computed(() => `Case - ${caseData.value?.case_name || 'Détails'}`);
</script>

<template>
  <AppLayout>
    <Head :title="pageTitle" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
          <Link href="/cases" class="text-blue-600 hover:text-blue-700 dark:text-blue-400">
            ← Retour aux cases
          </Link>
        </div>

        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              {{ caseData.case_name }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              ID: {{ caseData.case_id }}
            </p>
          </div>
          <span
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium',
              caseData.closed
                ? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'
                : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
            ]"
          >
            {{ caseData.closed ? 'Fermé' : 'Actif' }}
          </span>
        </div>

        <!-- Information Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Personal Info -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
              Informations Personnelles
            </h2>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nom</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ caseData.case_name }}</dd>
              </div>
              <div v-if="caseData.owner_name">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Propriétaire</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ caseData.owner_name }}</dd>
              </div>
              <div v-if="caseData.date_of_birth">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date de naissance</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ caseData.date_of_birth }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Téléphone</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                  {{ caseData.contact_phone_number || 'Non renseigné' }}
                </dd>
              </div>
            </dl>
          </div>

          <!-- Medical Info -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
              Informations Médicales
            </h2>
            <dl class="space-y-3">
              <div v-if="caseData.anc_number">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Numéro CPN</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ caseData.anc_number }}</dd>
              </div>
              <div v-if="caseData.edd">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date prévue d'accouchement</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ caseData.edd }}</dd>
              </div>
              <div v-if="caseData.next_visit_date">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Prochaine visite</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ caseData.next_visit_date }}</dd>
              </div>
            </dl>
          </div>

          <!-- Location Info -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
              Localisation
            </h2>
            <dl class="space-y-3">
              <div v-if="caseData.structure_sanitaire">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Structure sanitaire</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ caseData.structure_sanitaire }}</dd>
              </div>
              <div v-if="caseData.district_sanitaire">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">District</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ caseData.district_sanitaire }}</dd>
              </div>
              <div v-if="caseData.sous_prefecture">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Sous-préfecture</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ caseData.sous_prefecture }}</dd>
              </div>
              <div v-if="caseData.commune">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Commune</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ caseData.commune }}</dd>
              </div>
            </dl>
          </div>

          <!-- System Info -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
              Informations Système
            </h2>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Créé le</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                  {{ new Date(caseData.created_at).toLocaleString('fr-FR') }}
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dernière mise à jour</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                  {{ new Date(caseData.updated_at).toLocaleString('fr-FR') }}
                </dd>
              </div>
              <div v-if="caseData.date_closed">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fermé le</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                  {{ new Date(caseData.date_closed).toLocaleString('fr-FR') }}
                </dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- SMS History -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
              Historique SMS ({{ caseData.sms_history?.length || 0 }})
            </h2>
          </div>
          <div class="p-6">
            <div v-if="!caseData.sms_history || caseData.sms_history.length === 0" class="text-center py-8">
              <p class="text-gray-500 dark:text-gray-400">
                Aucun SMS envoyé pour ce case
              </p>
            </div>
            <div v-else class="space-y-4">
              <div
                v-for="sms in caseData.sms_history"
                :key="sms.id"
                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
              >
                <div class="flex items-start justify-between mb-2">
                  <span
                    :class="[
                      'px-2 py-1 text-xs rounded-full',
                      sms.status === 'sent'
                        ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200'
                        : sms.status === 'delivered'
                        ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                        : sms.status === 'failed'
                        ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
                        : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'
                    ]"
                  >
                    {{ sms.status }}
                  </span>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ new Date(sms.scheduled_at).toLocaleString('fr-FR') }}
                  </span>
                </div>
                <p class="text-sm text-gray-900 dark:text-white mb-2">
                  {{ sms.message }}
                </p>
                <div v-if="sms.sent_at" class="text-xs text-gray-500 dark:text-gray-400">
                  Envoyé: {{ new Date(sms.sent_at).toLocaleString('fr-FR') }}
                  <span v-if="sms.cost"> • Coût: {{ sms.cost }} FCFA</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

