<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import { route } from 'ziggy-js'

const props = defineProps({
    availableVariables: Object,
})

const form = useForm({
    name: '',
    content: '',
    description: '',
    is_active: true,
})

const submit = () => {
    form.post('/templates')
}

const previewContent = () => {
    let content = form.content
    
    // Remplacer les variables par des exemples
    const examples = {
        case_name: 'Marie Kouadio',
        case_id: 'CASE_001',
        contact_phone_number: '+225070000000',
        next_visit_date: '15/10/2025',
        structure_sanitaire: 'CHU de Cocody',
        district_sanitaire: 'Cocody',
        region_sanitaire: 'Abidjan',
    }
    
    Object.entries(examples).forEach(([key, value]) => {
        content = content.replace(new RegExp(`{${key}}`, 'g'), value)
    })
    
    return content
}
</script>

<template>
    <AppLayout>
        <Head title="Nouveau Template" />

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center mb-8">
                    <Link
                        :href="'/templates'"
                        class="mr-4 p-2 text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    >
                        <ArrowLeftIcon class="h-5 w-5" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Nouveau Template
                        </h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Créez un nouveau template de message SMS
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Formulaire -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                            Informations du template
                        </h2>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Nom -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Nom du template <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="Ex: Rappel RDV J-1"
                                />
                                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Contenu -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Contenu du message <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    v-model="form.content"
                                    required
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="Bonjour {case_name}, votre RDV est le {next_visit_date}..."
                                ></textarea>
                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Utilisez les variables disponibles dans la liste de droite
                                </div>
                                <div v-if="form.errors.content" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.content }}
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Description (optionnelle)
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="Description de l'usage de ce template..."
                                ></textarea>
                                <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <!-- Statut actif -->
                            <div class="flex items-center">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Template actif
                                </label>
                            </div>

                            <!-- Boutons -->
                            <div class="flex space-x-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
                                >
                                    {{ form.processing ? 'Création...' : 'Créer le template' }}
                                </button>
                                <Link
                                    :href="'/templates'"
                                    class="px-6 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors"
                                >
                                    Annuler
                                </Link>
                            </div>
                        </form>
                    </div>

                    <!-- Aide et prévisualisation -->
                    <div class="space-y-6">
                        <!-- Variables disponibles -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                Variables disponibles
                            </h3>
                            <div class="space-y-2">
                                <div
                                    v-for="(description, variable) in availableVariables"
                                    :key="variable"
                                    class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded"
                                >
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ description }}
                                    </span>
                                    <code class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded">
                                        {{'{' + variable + '}'}}
                                    </code>
                                </div>
                            </div>
                        </div>

                        <!-- Prévisualisation -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                Prévisualisation
                            </h3>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                    {{ previewContent() || 'Le message apparaîtra ici...' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
