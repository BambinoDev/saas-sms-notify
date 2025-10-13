<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import axios from 'axios'
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue'
import { CheckCircleIcon, XCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    organization: {
        type: Object,
        required: true
    }
})

// Form pour tester la connexion (AJAX)
const testForm = ref({
    email: props.organization.commcare_email || '',
    api_key: '',
    project_space: props.organization.commcare_project_space || ''
})

// State du test de connexion
const testStatus = ref(null) // null | 'testing' | 'success' | 'error'
const testMessage = ref('')
const testData = ref(null)

// Form pour sauvegarder la configuration (Inertia)
const form = useForm({
    email: props.organization.commcare_email || '',
    domain: props.organization.commcare_project_space || '',
    api_key: '',
    app_id: props.organization.commcare_app_id || '',
    project_name: props.organization.commcare_project_name || ''
})

// Validation : Formulaire complet
const isFormComplete = computed(() => {
    return form.email && form.domain && form.api_key && form.app_id && form.project_name
})

// Validation : Test réussi
const isTestSuccessful = computed(() => {
    return testStatus.value === 'success'
})

// Test de connexion CommCare (AJAX)
const testConnection = async () => {
    // Reset status
    testStatus.value = 'testing'
    testMessage.value = ''
    testData.value = null

    try {
        const response = await axios.post(route('onboarding.commcare.test'), {
            email: testForm.value.email,
            api_key: testForm.value.api_key,
            project_space: testForm.value.project_space
        })

        if (response.data.success) {
            testStatus.value = 'success'
            testMessage.value = response.data.message
            testData.value = response.data.data

            // Pré-remplir le formulaire avec les données testées
            form.email = testForm.value.email
            form.domain = testForm.value.project_space
            form.api_key = testForm.value.api_key

            // Extraire les infos de l'application si disponible
            if (response.data.data?.objects?.length > 0) {
                const firstApp = response.data.data.objects[0]
                if (!form.app_id) {
                    form.app_id = firstApp.id || ''
                }
                if (!form.project_name) {
                    form.project_name = firstApp.name || ''
                }
            }
        } else {
            testStatus.value = 'error'
            testMessage.value = response.data.message
        }
    } catch (error) {
        testStatus.value = 'error'
        if (error.response?.data?.message) {
            testMessage.value = error.response.data.message
        } else {
            testMessage.value = 'Erreur de connexion. Vérifiez votre connexion internet.'
        }
        console.error('Test CommCare error:', error)
    }
}

// Soumission du formulaire
const submit = () => {
    if (!isTestSuccessful.value) {
        alert('Veuillez d\'abord tester la connexion avec succès.')
        return
    }
    form.post(route('onboarding.commcare.store'))
}
</script>

<template>
    <Head title="Onboarding - Configuration CommCare" />

    <OnboardingLayout :current-step="3" :total-steps="5">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Configuration CommCare
                </h1>
                <p class="text-gray-600">
                    Connectez votre compte CommCare pour synchroniser vos données.
                </p>
            </div>

            <!-- Test Connection Section -->
            <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Étape 1 : Tester la connexion
                </h2>

                <div class="space-y-4">
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Email CommCare <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="testForm.email"
                            type="email"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="votre.email@exemple.com"
                            required
                        />
                    </div>

                    <!-- Project Space (Domain) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Project Space (Domain) <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="testForm.project_space"
                            type="text"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="mon-projet-commcare"
                            required
                        />
                        <p class="mt-1 text-sm text-gray-500">
                            Le nom de domaine de votre projet CommCare (ex: sci-civ-malaria)
                        </p>
                    </div>

                    <!-- API Key -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            API Key <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="testForm.api_key"
                            type="password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono"
                            placeholder="••••••••••••••••••••••••••••"
                            required
                        />
                        <p class="mt-1 text-sm text-gray-500">
                            Trouvez votre API Key dans les paramètres de votre compte CommCare
                        </p>
                    </div>

                    <!-- Test Button -->
                    <div class="pt-4">
                        <button
                            @click="testConnection"
                            :disabled="!testForm.email || !testForm.api_key || !testForm.project_space || testStatus === 'testing'"
                            class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                        >
                            <svg
                                v-if="testStatus === 'testing'"
                                class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span v-if="testStatus === 'testing'">Test en cours...</span>
                            <span v-else>Tester la connexion</span>
                        </button>
                    </div>

                    <!-- Test Result -->
                    <div v-if="testStatus && testStatus !== 'testing'" class="mt-4">
                        <!-- Success -->
                        <div v-if="testStatus === 'success'" class="p-4 bg-green-50 border border-green-200 rounded-lg flex items-start">
                            <CheckCircleIcon class="h-6 w-6 text-green-600 flex-shrink-0 mt-0.5" />
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-semibold text-green-900">Connexion réussie !</h3>
                                <p class="text-sm text-green-700 mt-1">{{ testMessage }}</p>
                                <div v-if="testData?.objects" class="mt-2 text-sm text-green-800">
                                    <p><strong>Applications trouvées :</strong> {{ testData.objects.length }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Error -->
                        <div v-else-if="testStatus === 'error'" class="p-4 bg-red-50 border border-red-200 rounded-lg flex items-start">
                            <XCircleIcon class="h-6 w-6 text-red-600 flex-shrink-0 mt-0.5" />
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-semibold text-red-900">Échec de connexion</h3>
                                <p class="text-sm text-red-700 mt-1">{{ testMessage }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration Form -->
            <div
                v-if="isTestSuccessful"
                class="bg-white p-8 rounded-xl shadow-sm border border-gray-200"
            >
                <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Étape 2 : Compléter la configuration
                </h2>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- App ID -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Application ID
                        </label>
                        <input
                            v-model="form.app_id"
                            type="text"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.app_id }"
                            placeholder="abc123def456 (optionnel)"
                        />
                        <p v-if="form.errors.app_id" class="mt-1 text-sm text-red-600">
                            {{ form.errors.app_id }}
                        </p>
                    </div>

                    <!-- Project Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Nom du projet <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.project_name"
                            type="text"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.project_name }"
                            placeholder="Mon Projet CommCare"
                            required
                        />
                        <p v-if="form.errors.project_name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.project_name }}
                        </p>
                    </div>

                    <!-- Warning -->
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg flex items-start">
                        <ExclamationTriangleIcon class="h-6 w-6 text-amber-600 flex-shrink-0 mt-0.5" />
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-amber-900">Sécurité</h3>
                            <p class="text-sm text-amber-700 mt-1">
                                Votre API Key sera encryptée en base de données et ne sera jamais affichée en clair.
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <Link
                            :href="route('onboarding.company')"
                            class="px-6 py-3 text-gray-700 font-medium hover:text-gray-900 transition-colors"
                        >
                            ← Retour
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || !form.project_name"
                            class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">Sauvegarde...</span>
                            <span v-else>Continuer →</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Locked message if test not successful -->
            <div
                v-else-if="testStatus !== 'success'"
                class="bg-gray-50 p-8 rounded-xl border-2 border-dashed border-gray-300 text-center"
            >
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <p class="text-gray-600 font-medium">
                    Veuillez d'abord tester la connexion avec succès
                </p>
            </div>
        </div>
    </OnboardingLayout>
</template>
