<script setup>
import { ref } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue'

const props = defineProps({
    organization: {
        type: Object,
        required: true
    }
})

const form = useForm({
    organization_type: props.organization.organization_type || '',
    sector: props.organization.sector || '',
    timezone: props.organization.timezone || 'Africa/Abidjan',
    team_size: props.organization.team_size || '',
    project_description: props.organization.project_description || ''
})

const organizationTypes = [
    { value: 'hospital', label: 'Hôpital' },
    { value: 'health_center', label: 'Centre de santé' },
    { value: 'school', label: 'École / Université' },
    { value: 'cooperative', label: 'Coopérative agricole' },
    { value: 'ngo', label: 'ONG internationale' },
    { value: 'local_ngo', label: 'ONG locale' },
    { value: 'ministry', label: 'Ministère / Gouvernement' },
    { value: 'other', label: 'Autre' }
]

const sectors = [
    { value: 'health', label: 'Santé' },
    { value: 'education', label: 'Éducation' },
    { value: 'agriculture', label: 'Agriculture' },
    { value: 'ngo', label: 'Humanitaire / ONG' },
    { value: 'government', label: 'Gouvernement' },
    { value: 'other', label: 'Autre' }
]

const timezones = [
    { value: 'Africa/Abidjan', label: 'Africa/Abidjan (GMT+0)' },
    { value: 'Africa/Lagos', label: 'Africa/Lagos (GMT+1)' },
    { value: 'Africa/Cairo', label: 'Africa/Cairo (GMT+2)' },
    { value: 'Africa/Nairobi', label: 'Africa/Nairobi (GMT+3)' },
    { value: 'Europe/Paris', label: 'Europe/Paris (GMT+1)' }
]

const teamSizes = [
    { value: '1-10', label: '1-10 personnes' },
    { value: '10-50', label: '10-50 personnes' },
    { value: '50-200', label: '50-200 personnes' },
    { value: '200+', label: '200+ personnes' }
]

const submit = () => {
    form.post(route('onboarding.company.store'))
}
</script>

<template>
    <Head title="Onboarding - Informations Organisation" />

    <OnboardingLayout :current-step="2" :total-steps="5">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Informations sur votre organisation
                </h1>
                <p class="text-gray-600">
                    Aidez-nous à mieux comprendre votre contexte pour personnaliser votre expérience.
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6 bg-white p-8 rounded-xl shadow-sm border border-gray-200">
                <!-- Organization Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Type d'organisation <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.organization_type"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="{ 'border-red-500': form.errors.organization_type }"
                        required
                    >
                        <option value="" disabled>Sélectionnez un type</option>
                        <option v-for="type in organizationTypes" :key="type.value" :value="type.value">
                            {{ type.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.organization_type" class="mt-1 text-sm text-red-600">
                        {{ form.errors.organization_type }}
                    </p>
                </div>

                <!-- Sector -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Secteur d'activité <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.sector"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="{ 'border-red-500': form.errors.sector }"
                        required
                    >
                        <option value="" disabled>Sélectionnez un secteur</option>
                        <option v-for="sect in sectors" :key="sect.value" :value="sect.value">
                            {{ sect.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.sector" class="mt-1 text-sm text-red-600">
                        {{ form.errors.sector }}
                    </p>
                </div>

                <!-- Timezone -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Fuseau horaire <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.timezone"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="{ 'border-red-500': form.errors.timezone }"
                        required
                    >
                        <option v-for="tz in timezones" :key="tz.value" :value="tz.value">
                            {{ tz.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.timezone" class="mt-1 text-sm text-red-600">
                        {{ form.errors.timezone }}
                    </p>
                </div>

                <!-- Team Size -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Taille de l'équipe <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.team_size"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="{ 'border-red-500': form.errors.team_size }"
                        required
                    >
                        <option value="" disabled>Sélectionnez une taille</option>
                        <option v-for="size in teamSizes" :key="size.value" :value="size.value">
                            {{ size.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.team_size" class="mt-1 text-sm text-red-600">
                        {{ form.errors.team_size }}
                    </p>
                </div>

                <!-- Project Description -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Description de votre projet CommCare
                    </label>
                    <textarea
                        v-model="form.project_description"
                        rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        :class="{ 'border-red-500': form.errors.project_description }"
                        placeholder="Ex: Système de rappel de consultations prénatales pour les femmes enceintes dans 50 centres de santé..."
                        maxlength="500"
                    ></textarea>
                    <div class="flex justify-between items-center mt-1">
                        <p v-if="form.errors.project_description" class="text-sm text-red-600">
                            {{ form.errors.project_description }}
                        </p>
                        <p class="text-sm text-gray-500 ml-auto">
                            {{ form.project_description?.length || 0 }} / 500
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <Link
                        :href="route('onboarding.welcome')"
                        class="px-6 py-3 text-gray-700 font-medium hover:text-gray-900 transition-colors"
                    >
                        ← Retour
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Sauvegarde...</span>
                        <span v-else>Continuer →</span>
                    </button>
                </div>
            </form>
        </div>
    </OnboardingLayout>
</template>
