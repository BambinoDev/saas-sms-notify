<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    templates: Array,
})

const deleteTemplate = (template) => {
    if (confirm(`Supprimer le template "${template.name}" ?`)) {
        // TODO: Implement delete
        console.log('Delete template', template.id)
    }
}
</script>

<template>
    <AppLayout>
        <Head title="Templates SMS" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Templates SMS
                        </h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Gérez vos templates de messages avec variables dynamiques
                        </p>
                    </div>
                    <Link
                        :href="route('templates.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <PlusIcon class="h-5 w-5 mr-2" />
                        Nouveau template
                    </Link>
                </div>

                <!-- Templates Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        v-for="template in templates"
                        :key="template.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                    {{ template.name }}
                                </h3>
                                <span
                                    class="inline-flex px-2 py-1 text-xs rounded-full"
                                    :class="template.is_active 
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                                >
                                    {{ template.is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                            <div class="flex space-x-2">
                                <Link
                                    :href="route('templates.edit', template.id)"
                                    class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                >
                                    <PencilIcon class="h-5 w-5" />
                                </Link>
                                <button
                                    @click="deleteTemplate(template)"
                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                >
                                    <TrashIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-4">
                            <p class="text-sm text-gray-700 dark:text-gray-300 font-mono whitespace-pre-wrap">
                                {{ template.content }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                            <span>Utilisé {{ template.rules_count }} fois</span>
                            <span>{{ template.usage_count }} envois</span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="templates.length === 0" class="text-center py-12">
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Aucun template créé pour le moment
                    </p>
                    <Link
                        :href="route('templates.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <PlusIcon class="h-5 w-5 mr-2" />
                        Créer votre premier template
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>