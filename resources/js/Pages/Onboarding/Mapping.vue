<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import axios from 'axios'
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue'
import { CheckCircleIcon, XCircleIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    organization: {
        type: Object,
        required: true
    }
})

// État du chargement des propriétés
const loadingProperties = ref(false)
const propertiesLoaded = ref(false)
const propertiesError = ref('')
const availableProperties = ref([])
const caseExample = ref(null)

// Form pour saisir le case_type
const caseTypeInput = ref(props.organization.commcare_case_type || '')

// Form pour sauvegarder le mapping (Inertia)
const form = useForm({
    case_type: props.organization.commcare_case_type || '',
    selected_properties: props.organization.case_properties_mapping || [],
    phone_number_field: props.organization.phone_number_field || '',
    eligibility_field: props.organization.eligibility_field || '',
    eligibility_condition: props.organization.eligibility_condition || 'equals',
    eligibility_value: props.organization.eligibility_value || ''
})

// Computed : Au moins une propriété sélectionnée
const hasSelectedProperties = computed(() => {
    return form.selected_properties.length > 0
})

// Computed : Champ téléphone sélectionné
const hasPhoneField = computed(() => {
    return form.phone_number_field !== ''
})

// Computed : Formulaire valide
const isFormValid = computed(() => {
    return form.case_type && hasSelectedProperties.value && hasPhoneField.value
})

// Charger les propriétés CommCare
const loadProperties = async () => {
    if (!caseTypeInput.value) {
        propertiesError.value = 'Veuillez saisir un case type'
        return
    }

    loadingProperties.value = true
    propertiesError.value = ''
    propertiesLoaded.value = false
    availableProperties.value = []

    try {
        const response = await axios.post(route('onboarding.mapping.properties'), {
            case_type: caseTypeInput.value
        })

        if (response.data.success) {
            availableProperties.value = response.data.properties
            caseExample.value = response.data.case_example
            propertiesLoaded.value = true
            form.case_type = caseTypeInput.value

            // Pré-sélectionner les propriétés si déjà configurées
            if (props.organization.case_properties_mapping?.length > 0) {
                form.selected_properties = props.organization.case_properties_mapping.filter(
                    prop => availableProperties.value.includes(prop)
                )
            }
        } else {
            propertiesError.value = response.data.message
        }
    } catch (error) {
        propertiesError.value = error.response?.data?.message || 'Erreur lors du chargement des propriétés'
        console.error('Load properties error:', error)
    } finally {
        loadingProperties.value = false
    }
}

// Toggle selection d'une propriété
const toggleProperty = (property) => {
    const index = form.selected_properties.indexOf(property)
    if (index === -1) {
        form.selected_properties.push(property)
    } else {
        form.selected_properties.splice(index, 1)
        // Si c'était le champ téléphone, le réinitialiser
        if (form.phone_number_field === property) {
            form.phone_number_field = ''
        }
    }
}

// Sélectionner comme champ téléphone
const selectAsPhoneField = (property) => {
    // Sélectionner la propriété si pas déjà sélectionnée
    if (!form.selected_properties.includes(property)) {
        form.selected_properties.push(property)
    }
    form.phone_number_field = property
}

// Sélectionner/Désélectionner toutes les propriétés
const toggleAllProperties = () => {
    if (form.selected_properties.length === availableProperties.value.length) {
        // Tout désélectionner
        form.selected_properties = []
        form.phone_number_field = ''
    } else {
        // Tout sélectionner
        form.selected_properties = [...availableProperties.value]
    }
}

// Soumission du formulaire
const submit = () => {
    if (!isFormValid.value) {
        alert('Veuillez sélectionner au moins une propriété et définir le champ téléphone.')
        return
    }
    form.post(route('onboarding.mapping.store'))
}
</script>

<template>
    <Head title="Onboarding - Mapping des Champs" />
    
    <OnboardingLayout :current-step="4" :total-steps="5">
        <div class="max-w-4xl mx-auto">
      <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Mapping des champs CommCare
                </h1>
                <p class="text-gray-600">
                    Sélectionnez les champs que vous souhaitez utiliser dans votre projet.
        </p>
      </div>

            <!-- Section 1 : Case Type Input -->
            <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
          </svg>
                    Étape 1 : Type de case CommCare
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Case Type <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-3">
                            <input
                                v-model="caseTypeInput"
                                type="text"
                                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Ex: woman, child, patient, student..."
                                @keyup.enter="loadProperties"
                            />
                            <button
                                @click="loadProperties"
                                :disabled="!caseTypeInput || loadingProperties"
                                class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
                            >
                                <span v-if="loadingProperties">Chargement...</span>
                                <span v-else>Charger les propriétés</span>
                            </button>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            Saisissez le type de case que vous souhaitez traiter (ex: woman, child, patient)
            </p>
          </div>

                    <!-- Error message -->
                    <div v-if="propertiesError" class="p-4 bg-red-50 border border-red-200 rounded-lg flex items-start">
                        <XCircleIcon class="h-6 w-6 text-red-600 flex-shrink-0 mt-0.5" />
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-red-900">Erreur</h3>
                            <p class="text-sm text-red-700 mt-1">{{ propertiesError }}</p>
                        </div>
                    </div>
        </div>
      </div>

            <!-- Section 2 : Properties Selection (only if loaded) -->
            <div v-if="propertiesLoaded" class="bg-white p-8 rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <CheckCircleIcon class="w-6 h-6 mr-2 text-green-600" />
                        Étape 2 : Sélection des propriétés
                    </h2>
                    <button
                        @click="toggleAllProperties"
                        class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                    >
                        {{ form.selected_properties.length === availableProperties.length ? 'Tout désélectionner' : 'Tout sélectionner' }}
                    </button>
                </div>

                <!-- Info box -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-start">
                    <InformationCircleIcon class="h-6 w-6 text-blue-600 flex-shrink-0 mt-0.5" />
                    <div class="ml-3 text-sm text-blue-800">
                        <p class="font-semibold">{{ availableProperties.length }} propriétés trouvées</p>
                        <p class="mt-1">Sélectionnez les champs que vous souhaitez synchroniser depuis CommCare.</p>
                    </div>
              </div>

                <!-- Properties Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div
                        v-for="property in availableProperties"
                        :key="property"
                        class="relative border rounded-lg p-4 transition-all cursor-pointer"
                        :class="form.selected_properties.includes(property)
                            ? 'border-blue-500 bg-blue-50'
                            : 'border-gray-200 hover:border-gray-300 bg-white'"
                        @click="toggleProperty(property)"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-start flex-1 min-w-0">
                <input
                                    type="checkbox"
                                    :checked="form.selected_properties.includes(property)"
                                    class="mt-1 mr-3 h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                    @click.stop="toggleProperty(property)"
                                />
                                <div class="flex-1 min-w-0">
                                    <p class="font-mono text-sm font-medium text-gray-900 truncate">
                                        {{ property }}
                                    </p>
                                    <button
                                        v-if="form.selected_properties.includes(property)"
                                        @click.stop="selectAsPhoneField(property)"
                                        class="mt-2 text-xs font-medium transition-colors"
                                        :class="form.phone_number_field === property
                                            ? 'text-green-600'
                                            : 'text-blue-600 hover:text-blue-700'"
                                    >
                                        <svg v-if="form.phone_number_field === property" class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        {{ form.phone_number_field === property ? '✓ Champ téléphone' : 'Définir comme champ téléphone' }}
                                    </button>
                                </div>
              </div>
            </div>
          </div>
        </div>

                <!-- Summary -->
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">
                            <strong class="text-gray-900">{{ form.selected_properties.length }}</strong> propriété(s) sélectionnée(s)
                        </span>
                        <span v-if="hasPhoneField" class="text-green-600 font-medium">
                            ✓ Champ téléphone : {{ form.phone_number_field }}
                        </span>
                        <span v-else class="text-amber-600 font-medium">
                            ⚠ Champ téléphone non défini
                        </span>
                    </div>
                </div>
            </div>

            <!-- Section 3 : Eligibility Conditions (Optional) -->
            <div v-if="propertiesLoaded" class="bg-white p-8 rounded-xl shadow-sm border border-gray-200 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    Étape 3 : Condition d'éligibilité (Optionnel)
                </h2>

                <p class="text-sm text-gray-600 mb-6">
                    Définissez une condition pour filtrer les cases qui recevront des SMS.
                </p>

                <div class="space-y-4">
                    <!-- Eligibility Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Champ à vérifier
                        </label>
                        <select
                            v-model="form.eligibility_field"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Aucun (tous les cases)</option>
                            <option v-for="prop in form.selected_properties" :key="prop" :value="prop">
                                {{ prop }}
                            </option>
                        </select>
          </div>

                    <!-- Eligibility Condition -->
                    <div v-if="form.eligibility_field">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Condition
                        </label>
                        <select
                            v-model="form.eligibility_condition"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="equals">Est égal à</option>
                            <option value="not_equals">Est différent de</option>
                            <option value="contains">Contient</option>
                            <option value="not_contains">Ne contient pas</option>
                        </select>
                    </div>

                    <!-- Eligibility Value -->
                    <div v-if="form.eligibility_field">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Valeur attendue
                        </label>
                <input
                            v-model="form.eligibility_value"
                  type="text"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Ex: yes, true, enrolled..."
                />
              </div>

                    <!-- Example -->
                    <div v-if="form.eligibility_field && form.eligibility_value" class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                        <p class="text-sm text-purple-900">
                            <strong>Exemple :</strong> Seuls les cases où
                            <code class="px-2 py-1 bg-purple-100 rounded">{{ form.eligibility_field }}</code>
                            {{ form.eligibility_condition === 'equals' ? 'est égal à' : form.eligibility_condition === 'not_equals' ? 'est différent de' : form.eligibility_condition === 'contains' ? 'contient' : 'ne contient pas' }}
                            <code class="px-2 py-1 bg-purple-100 rounded">{{ form.eligibility_value }}</code>
                            recevront des SMS.
                        </p>
                    </div>
              </div>
            </div>

            <!-- Actions -->
            <div v-if="propertiesLoaded" class="flex items-center justify-between">
                <Link
                    :href="route('onboarding.commcare')"
                    class="px-6 py-3 text-gray-700 font-medium hover:text-gray-900 transition-colors"
                >
                    ← Retour
                </Link>
            <button
                    @click="submit"
                    :disabled="form.processing || !isFormValid"
                    class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="form.processing">Sauvegarde...</span>
                    <span v-else>Terminer la configuration →</span>
            </button>
        </div>

            <!-- Placeholder if not loaded -->
            <div v-else class="bg-gray-50 p-12 rounded-xl border-2 border-dashed border-gray-300 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
                <p class="text-gray-600 font-medium text-lg mb-2">
                    Saisissez un case type pour commencer
                </p>
                <p class="text-gray-500 text-sm">
                    Nous récupérerons automatiquement toutes les propriétés disponibles
          </p>
        </div>
        </div>
  </OnboardingLayout>
</template>
