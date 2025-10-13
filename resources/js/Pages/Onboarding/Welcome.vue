<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { CheckCircleIcon, RocketLaunchIcon } from '@heroicons/vue/24/outline'

defineProps({
    organization: {
        type: Object,
        required: true
    }
})

const steps = [
    { number: 1, name: 'Welcome', description: 'Introduction' },
    { number: 2, name: 'Company', description: 'Informations organisation' },
    { number: 3, name: 'CommCare', description: 'Configuration API' },
    { number: 4, name: 'Mapping', description: 'Sélection des champs' },
    { number: 5, name: 'Completion', description: 'Confirmation' }
]
</script>

<template>
    <Head title="Onboarding - Bienvenue" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div class="max-w-4xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-12 text-center">
                <RocketLaunchIcon class="h-16 w-16 text-white mx-auto mb-4" />
                <h1 class="text-4xl font-bold text-white mb-2">
                    Bienvenue sur S-Remind
                </h1>
                <p class="text-blue-100 text-lg">
                    Configurez votre système d'envoi de SMS CommCare en 5 minutes
                </p>
            </div>

            <!-- Content -->
            <div class="px-8 py-10">
                <!-- Organisation info -->
                <div class="mb-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm text-gray-600">Organisation</p>
                    <p class="text-xl font-semibold text-gray-900">{{ organization.name }}</p>
                </div>

                <!-- Steps preview -->
                <div class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Étapes de configuration</h2>
                    <div class="space-y-4">
                        <div
                            v-for="step in steps"
                            :key="step.number"
                            class="flex items-start space-x-4 p-4 rounded-lg transition-colors hover:bg-gray-50"
                        >
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-semibold">{{ step.number }}</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">{{ step.name }}</h3>
                                <p class="text-sm text-gray-600">{{ step.description }}</p>
                            </div>
                            <CheckCircleIcon v-if="step.number === 1" class="h-6 w-6 text-green-500" />
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="mb-10 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4">
                        <div class="text-3xl font-bold text-blue-600 mb-1">~5 min</div>
                        <div class="text-sm text-gray-600">Temps de configuration</div>
                    </div>
                    <div class="text-center p-4">
                        <div class="text-3xl font-bold text-blue-600 mb-1">100%</div>
                        <div class="text-sm text-gray-600">Personnalisable</div>
                    </div>
                    <div class="text-center p-4">
                        <div class="text-3xl font-bold text-blue-600 mb-1">∞</div>
                        <div class="text-sm text-gray-600">Types de projets</div>
                    </div>
                </div>

                <!-- CTA -->
                <div class="text-center">
                    <Link
                        :href="route('onboarding.company')"
                        class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-lg font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                    >
                        Commencer la configuration
                        <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
