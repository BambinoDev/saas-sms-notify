<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  sms: Object,
});

const pageTitle = computed(() => `SMS - ${props.sms?.recipient_name || 'Détails'}`);

// Format date
const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
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

// Status icon
const statusIcon = (status) => {
  const icons = {
    pending: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    sent: 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8',
    delivered: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    failed: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  };
  return icons[status] || icons.pending;
};

// Retry SMS
const retrySms = () => {
  if (!confirm('Renvoyer ce SMS ?')) return;
  router.post(`/sms/${props.sms.id}/retry`);
};

// Send now
const sendNow = () => {
  if (!confirm('Envoyer ce SMS immédiatement ?')) return;
  router.post(`/sms/${props.sms.id}/send-now`);
};
</script>

<template>
  <AppLayout>
    <Head :title="pageTitle" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
          <Link href="/sms" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Retour à la file SMS
          </Link>
        </div>

        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Détails SMS
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              ID: #{{ sms?.id }}
            </p>
          </div>
          <span
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium flex items-center gap-2',
              statusColor(sms?.status)
            ]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="statusIcon(sms?.status)" />
            </svg>
            {{ sms?.status }}
          </span>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left Column - SMS Details -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Message Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                Message
              </h2>
              <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <p class="text-gray-900 dark:text-white whitespace-pre-wrap">
                  {{ sms?.message || 'N/A' }}
                </p>
              </div>
              <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                {{ sms?.message?.length || 0 }} caractères
              </div>
            </div>

            <!-- Timeline Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                Timeline
              </h2>
              <div class="space-y-4">
                <!-- Created -->
                <div class="flex items-start gap-3">
                  <div class="flex-shrink-0 w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      SMS créé
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      {{ formatDate(sms?.created_at) }}
                    </p>
                  </div>
                </div>

                <!-- Scheduled -->
                <div class="flex items-start gap-3">
                  <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      Programmé pour
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      {{ formatDate(sms?.scheduled_at) }}
                    </p>
                  </div>
                </div>

                <!-- Sent -->
                <div v-if="sms?.sent_at" class="flex items-start gap-3">
                  <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      SMS envoyé
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      {{ formatDate(sms?.sent_at) }}
                    </p>
                  </div>
                </div>

                <!-- Delivered -->
                <div v-if="sms?.delivered_at" class="flex items-start gap-3">
                  <div class="flex-shrink-0 w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      SMS délivré
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      {{ formatDate(sms?.delivered_at) }}
                    </p>
                  </div>
                </div>

                <!-- Error -->
                <div v-if="sms?.error_message" class="flex items-start gap-3">
                  <div class="flex-shrink-0 w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      Erreur
                    </p>
                    <p class="text-sm text-red-600 dark:text-red-400">
                      {{ sms.error_message }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Recipient & Meta -->
          <div class="space-y-6">
            <!-- Recipient Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                Destinataire
              </h2>
              <dl class="space-y-3">
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nom</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ sms?.recipient_name || 'N/A' }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Téléphone</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">
                    {{ sms?.phone_number || 'N/A' }}
                  </dd>
                </div>
                <div v-if="sms?.case">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Structure</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ sms.case.structure || 'N/A' }}
                  </dd>
                </div>
                <div v-if="sms?.case">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Case</dt>
                  <dd class="mt-1">
                    <Link
                      :href="`/cases/${sms.case.id}`"
                      class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    >
                      {{ sms.case.case_name }}
                    </Link>
                  </dd>
                </div>
              </dl>
            </div>

            <!-- Meta Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                Informations
              </h2>
              <dl class="space-y-3">
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Template</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ sms?.template_name || 'N/A' }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Coût</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ sms?.cost ? `${sms.cost} FCFA` : 'N/A' }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ID</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">
                    #{{ sms?.id }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Créé le</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ formatDate(sms?.created_at) }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mis à jour</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ formatDate(sms?.updated_at) }}
                  </dd>
                </div>
              </dl>
            </div>

            <!-- Actions Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                Actions
              </h2>
              <div class="space-y-3">
                <button
                  v-if="sms?.status === 'failed'"
                  @click="retrySms"
                  class="w-full px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors flex items-center justify-center gap-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  Renvoyer SMS
                </button>
                <button
                  v-if="sms?.status === 'pending'"
                  @click="sendNow"
                  class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                  </svg>
                  Envoyer maintenant
                </button>
                <Link
                  href="/sms"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center justify-center gap-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                  </svg>
                  Retour à la liste
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
