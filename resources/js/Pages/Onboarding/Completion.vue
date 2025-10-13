<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { CheckCircleIcon, RocketLaunchIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    organization: {
        type: Object,
        required: true
    }
})

const configSummary = [
    {
        icon: '🏢',
        label: 'Organisation',
        value: props.organization.name,
        detail: props.organization.organization_type
    },
    {
        icon: '🔗',
        label: 'CommCare',
        value: props.organization.commcare_project_name,
        detail: props.organization.commcare_project_space
    },
    {
        icon: '📋',
        label: 'Case Type',
        value: props.organization.commcare_case_type,
        detail: `${props.organization.case_properties_mapping?.length || 0} propriétés`
    },
    {
        icon: '📱',
        label: 'Champ téléphone',
        value: props.organization.phone_number_field,
        detail: 'Prêt pour l\'envoi de SMS'
    }
]
</script>

<template>
    <Head title="Onboarding - Configuration Terminée" />

    <div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50 flex items-center justify-center p-4">
        <div class="max-w-3xl w-full bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Success Header -->
            <div class="bg-gradient-to-r from-green-500 to-blue-500 px-8 py-16 text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-full mb-6">
                    <CheckCircleIcon class="h-16 w-16 text-green-500" />
                </div>
                <h1 class="text-4xl font-bold text-white mb-3">
                    Félicitations ! 🎉
                </h1>
                <p class="text-xl text-white opacity-90">
                    Votre configuration est terminée
                </p>
            </div>

            <!-- Content -->
            <div class="px-8 py-10">
                <!-- Success Message -->
                <div class="text-center mb-10">
                    <p class="text-lg text-gray-700 mb-2">
                        <strong>{{ organization.name }}</strong> est maintenant prêt à envoyer des SMS !
                    </p>
                    <p class="text-gray-600">
                        Vous pouvez maintenant créer vos règles d'envoi et commencer à communiquer avec vos bénéficiaires.
                    </p>
                </div>

                <!-- Configuration Summary -->
                <div class="mb-10">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 text-center">
                        Récapitulatif de votre configuration
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            v-for="item in configSummary"
                            :key="item.label"
                            class="p-6 bg-gray-50 rounded-xl border border-gray-200"
                        >
                            <div class="flex items-start">
                                <div class="text-3xl mr-4">{{ item.icon }}</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">
                                        {{ item.label }}
                                    </p>
                                    <p class="text-lg font-bold text-gray-900 mt-1 truncate">
                                        {{ item.value }}
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ item.detail }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="mb-10">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 text-center">
                        Prochaines étapes
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-start p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold mr-4">
                                1
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Créer vos règles SMS</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Définissez quand et comment envoyer vos SMS automatiquement
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold mr-4">
                                2
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Créer vos templates</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Personnalisez vos messages avec des variables dynamiques
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold mr-4">
                                3
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Lancer la synchronisation</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Synchronisez vos données CommCare et commencez à envoyer
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA -->
                <div class="text-center">
                    <Link
                        :href="route('dashboard')"
                        class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-lg font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                    >
                        <RocketLaunchIcon class="h-6 w-6 mr-2" />
                        Accéder au Dashboard
                    </Link>
                </div>

                <!-- Support -->
                <div class="mt-10 pt-8 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-600">
                        Besoin d'aide ? Consultez notre
                        <a href="#" class="text-blue-600 hover:text-blue-700 font-medium">documentation</a>
                        ou contactez le
                        <a href="#" class="text-blue-600 hover:text-blue-700 font-medium">support</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
