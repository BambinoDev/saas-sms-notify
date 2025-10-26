<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ArrowPathIcon, CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  organization: Object,
  syncStatus: Object,
})

const isSyncing = ref(props.syncStatus.is_syncing)
const progress = ref(props.syncStatus.progress || 0)
const current = ref(props.syncStatus.current || 0)
const total = ref(props.syncStatus.total || 0)
const message = ref(props.syncStatus.message || 'Prêt à synchroniser')
const error = ref(null)
const syncCompleted = ref(false)

let pollingInterval = null


const startSync = async () => {
  try {
    error.value = null
    syncCompleted.value = false
    
    // Feedback immédiat
    isSyncing.value = true
    message.value = 'Démarrage de la synchronisation...'
    
    const response = await axios.post('/cases/sync')
    if (response.data.success) {
      startPolling()
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors du démarrage de la synchronisation'
    isSyncing.value = false
    console.error('Sync error:', err)
  }
}

const startPolling = () => {
  if (pollingInterval) return
  pollingInterval = setInterval(async () => {
    try {
      const response = await axios.get('/cases/sync/status')
      const status = response.data
      isSyncing.value = status.is_syncing
      progress.value = status.progress
      current.value = status.current
      total.value = status.total
      message.value = status.message
      
      
      if (!status.is_syncing) {
        stopPolling()
        if (status.progress === 100) {
          syncCompleted.value = true
          message.value = `Synchronisation terminée avec succès ! ${status.current} dossiers synchronisés.`
          
          // Recharger la page après 3 secondes pour afficher les nouvelles données
          setTimeout(() => {
            window.location.reload()
          }, 3000)
        }
      }
    } catch (err) {
      console.error('Polling error:', err)
      // Ne pas arrêter le polling en cas d'erreur, juste logger
    }
  }, 2000)
}

const stopPolling = () => {
  if (pollingInterval) {
    clearInterval(pollingInterval)
    pollingInterval = null
  }
}

onMounted(() => {
  // Toujours démarrer le polling pour vérifier l'état
  startPolling()
})

onUnmounted(() => stopPolling())
</script>

<template>
  <AppLayout>
    <Head title="Synchronisation CommCare" />

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Synchronisation CommCare</h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">
            Importez vos dossiers de type <strong>{{ organization.commcare_case_type }}</strong> depuis CommCare
          </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-8">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Configuration actuelle</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Type de dossier</p>
              <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ organization.commcare_case_type }}</p>
            </div>
            <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Propriétés</p>
              <p class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ organization.case_properties_count }}</p>
            </div>
            <div class="text-center p-4 bg-green-50 dark:bg-green-900/30 rounded-lg">
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Champ téléphone</p>
              <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ organization.phone_number_field }}</p>
            </div>
          </div>
          <div v-if="organization.last_sync_at" class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">Dernière synchronisation : {{ organization.last_sync_at }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 mb-8">
          <div v-if="!isSyncing" class="text-center">
            <ArrowPathIcon class="h-16 w-16 text-blue-600 dark:text-blue-400 mx-auto mb-4" />
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Prêt à synchroniser</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Cliquez pour importer vos dossiers depuis CommCare</p>
            <button @click="startSync" :disabled="isSyncing" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-lg font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
              <ArrowPathIcon class="h-6 w-6 mr-2" />
              Lancer la synchronisation
            </button>

            <div v-if="error" class="mt-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
              <div class="flex items-start">
                <XCircleIcon class="h-6 w-6 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                <div class="ml-3">
                  <h3 class="text-sm font-semibold text-red-900 dark:text-red-200">Erreur</h3>
                  <p class="text-sm text-red-700 dark:text-red-300 mt-1">{{ error }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Synchronisation en cours -->
          <div v-else-if="isSyncing" class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full mb-4">
              <ArrowPathIcon class="h-10 w-10 text-blue-600 dark:text-blue-400 animate-spin" />
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Synchronisation en cours...</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">{{ message }}</p>

            <div class="max-w-xl mx-auto">
              <div class="mb-2 flex justify-between text-sm text-gray-600 dark:text-gray-400">
                <span>{{ current }} / {{ total }} dossiers</span>
                <span>{{ progress }}%</span>
              </div>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                <div class="bg-blue-600 h-4 rounded-full transition-all duration-500" :style="{ width: progress + '%' }"></div>
              </div>
            </div>

            <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">Ne fermez pas cette page pendant la synchronisation</p>
          </div>

          <!-- Synchronisation terminée -->
          <div v-else-if="syncCompleted" class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full mb-4">
              <CheckCircleIcon class="h-10 w-10 text-green-600 dark:text-green-400" />
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">🎉 Première configuration terminée !</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">{{ message }}</p>
            
            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
              <div class="text-center">
                <p class="text-lg font-semibold text-green-800 dark:text-green-200 mb-2">
                  {{ total }} dossiers synchronisés avec succès
                </p>
                <p class="text-sm text-green-700 dark:text-green-300">
                  Dernière synchronisation : {{ new Date().toLocaleString('fr-FR') }}
                </p>
              </div>
            </div>

            <button @click="startSync" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-lg font-semibold rounded-lg hover:bg-blue-700 transition-colors">
              <ArrowPathIcon class="h-6 w-6 mr-2" />
              Relancer la synchronisation
            </button>
          </div>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
          <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-200 mb-2">ℹ️ Informations</h3>
          <ul class="text-sm text-blue-800 dark:text-blue-300 space-y-2">
            <li>• La synchronisation peut prendre plusieurs minutes selon le nombre de dossiers</li>
            <li>• Les dossiers existants seront mis à jour, les nouveaux seront créés</li>
            <li>• Seules les propriétés sélectionnées lors de l'onboarding seront importées</li>
            <li>• La synchronisation s'exécute en arrière-plan, vous pouvez naviguer ailleurs</li>
          </ul>
        </div>
      </div>
    </div>
  </AppLayout>
</template>


