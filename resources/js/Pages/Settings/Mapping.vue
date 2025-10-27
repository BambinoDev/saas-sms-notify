<template>
  <SettingsLayout title="Configuration des champs">
    <div class="py-6 px-4 sm:px-6 lg:px-8">
      <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Mapping des champs CommCare</h1>
          <p class="mt-1 text-sm text-gray-600">
            Associez vos champs CommCare aux variables utilisables dans les templates SMS
          </p>
        </div>

        <!-- Message de succès -->
        <div v-if="form.recentlySuccessful" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium text-green-900">
              Mappings enregistrés avec succès !
            </p>
          </div>
        </div>

        <!-- Info box -->
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="text-sm font-semibold text-blue-900">
                💡 Comment ça marche ?
              </p>
              <p class="text-sm text-blue-800 mt-1">
                Les <strong>champs CommCare</strong> sont les propriétés de vos dossiers (case_name, next_visit_date, etc.).
                Les <strong>variables système</strong> sont les placeholders utilisables dans vos templates SMS (ex: {case_name}).
              </p>
            </div>
          </div>
        </div>

        <!-- Formulaire -->
        <form @submit.prevent="submit">
          <!-- Table des mappings -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
            <div class="p-6 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <div>
                  <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                    Mappings des champs
                  </h2>
                  <p class="text-sm text-gray-600 mt-1">
                    {{ mappings.length }} champ(s) mappé(s)
                  </p>
                </div>
                <button
                  type="button"
                  @click="addMapping"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition flex items-center gap-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Ajouter un champ
                </button>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Champ CommCare
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                      →
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Variable système
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Label (affichage)
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr 
                    v-for="(mapping, index) in mappings"
                    :key="index"
                    class="hover:bg-gray-50"
                  >
                    <!-- Champ CommCare -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <input
                        v-model="mapping.commcare_field"
                        type="text"
                        placeholder="case_name"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      />
                    </td>

                    <!-- Arrow -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <svg class="w-5 h-5 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                      </svg>
                    </td>

                    <!-- Variable système -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center gap-2">
                        <span class="text-gray-500">{</span>
                        <input
                          v-model="mapping.system_variable"
                          type="text"
                          placeholder="case_name"
                          required
                          class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <span class="text-gray-500">}</span>
                      </div>
                    </td>

                    <!-- Label -->
                    <td class="px-6 py-4 whitespace-nowrap">
                      <input
                        v-model="mapping.label"
                        type="text"
                        placeholder="Nom du bénéficiaire"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      />
                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                      <button
                        type="button"
                        @click="removeMapping(index)"
                        class="text-red-600 hover:text-red-700 p-1"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </td>
                  </tr>

                  <!-- Empty state -->
                  <tr v-if="mappings.length === 0">
                    <td colspan="5" class="px-6 py-12 text-center">
                      <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      <p class="text-sm font-medium text-gray-900">Aucun mapping configuré</p>
                      <p class="text-sm text-gray-500 mt-1">
                        Cliquez sur "Ajouter un champ" pour commencer
                      </p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Preview -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Aperçu des variables
              </h2>
              <p class="text-sm text-gray-600 mt-1">
                Voici comment utiliser vos variables dans les templates
              </p>
            </div>

            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                  v-for="(mapping, index) in mappings"
                  :key="index"
                  class="p-3 bg-gray-50 rounded-lg border border-gray-200"
                >
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-gray-500 uppercase">
                      {{ mapping.label || 'Sans label' }}
                    </span>
                    <code class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded text-xs font-mono">
                      { {{ mapping.system_variable || '?' }} }
                    </code>
                  </div>
                  <p class="text-sm text-gray-600">
                    Champ source : <code class="text-blue-600 font-mono">{{ mapping.commcare_field || 'non défini' }}</code>
                  </p>
                </div>

                <!-- Empty state -->
                <div 
                  v-if="mappings.length === 0"
                  class="col-span-2 p-8 text-center text-gray-500 text-sm"
                >
                  Ajoutez des mappings pour voir l'aperçu
                </div>
              </div>

              <!-- Exemple d'utilisation -->
              <div v-if="mappings.length > 0" class="mt-6 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <p class="text-sm font-semibold text-purple-900 mb-2">
                  📝 Exemple d'utilisation dans un template :
                </p>
                <code class="block p-3 bg-white border border-purple-200 rounded text-sm text-gray-800 font-mono">
                  Bonjour {{ getExampleText() }}, 
                  votre RDV est prévu le {{ getExampleDate() }}.
                </code>
              </div>
            </div>
          </div>

          <!-- Mappings prédéfinis -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Mappings recommandés
              </h2>
              <p class="text-sm text-gray-600 mt-1">
                Ajoutez rapidement les mappings les plus courants
              </p>
            </div>

            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <button
                  v-for="preset in presetMappings"
                  :key="preset.commcare_field"
                  type="button"
                  @click="addPresetMapping(preset)"
                  :disabled="mappings.some(m => m.commcare_field === preset.commcare_field)"
                  class="p-3 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-left disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:border-gray-200 disabled:hover:bg-white"
                >
                  <div class="flex items-center gap-2 mb-1">
                    <svg 
                      v-if="mappings.some(m => m.commcare_field === preset.commcare_field)"
                      class="w-4 h-4 text-green-600"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-sm font-medium text-gray-900">{{ preset.label }}</span>
                  </div>
                    <code class="text-xs text-gray-600 font-mono">
                      {{ preset.commcare_field }} → { {{ preset.system_variable }} }
                    </code>
                </button>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-between">
            <Link
              href="/settings/sync"
              class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium"
            >
              ← Retour à la synchronisation
            </Link>

            <button
              type="submit"
              :disabled="form.processing || mappings.length === 0"
              class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="form.processing">Enregistrement...</span>
              <span v-else>Enregistrer les mappings</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </SettingsLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

const props = defineProps({
  organization: Object,
  currentMappings: Object,
});

// Mappings
const mappings = ref(
  Object.entries(props.currentMappings || {}).map(([field, label]) => ({
    commcare_field: field,
    system_variable: field,
    label: label,
  }))
);

// Si aucun mapping, ajouter un exemple
if (mappings.value.length === 0) {
  mappings.value.push({
    commcare_field: 'case_name',
    system_variable: 'case_name',
    label: 'Nom du bénéficiaire',
  });
}

// Presets
const presetMappings = [
  { commcare_field: 'case_name', system_variable: 'case_name', label: 'Nom du bénéficiaire' },
  { commcare_field: 'contact_phone_number', system_variable: 'contact_phone_number', label: 'Numéro de téléphone' },
  { commcare_field: 'next_visit_date', system_variable: 'next_visit_date', label: 'Date du prochain RDV' },
  { commcare_field: 'structure_sanitaire', system_variable: 'structure_sanitaire', label: 'Structure sanitaire' },
  { commcare_field: 'district_sanitaire', system_variable: 'district_sanitaire', label: 'District sanitaire' },
  { commcare_field: 'region_sanitaire', system_variable: 'region_sanitaire', label: 'Région sanitaire' },
  { commcare_field: 'created_at', system_variable: 'created_at', label: 'Date de création' },
  { commcare_field: 'updated_at', system_variable: 'updated_at', label: 'Dernière modification' },
];

// Form
const form = useForm({
  mappings: mappings.value,
});

// Ajouter un mapping
const addMapping = () => {
  mappings.value.push({
    commcare_field: '',
    system_variable: '',
    label: '',
  });
};

// Supprimer un mapping
const removeMapping = (index) => {
  mappings.value.splice(index, 1);
};

// Ajouter un preset
const addPresetMapping = (preset) => {
  if (!mappings.value.some(m => m.commcare_field === preset.commcare_field)) {
    mappings.value.push({ ...preset });
  }
};

// Fonctions pour l'exemple
const getExampleText = () => {
  const firstMapping = mappings.value[0];
  if (firstMapping?.system_variable) {
    return `{${firstMapping.system_variable}}`;
  }
  return '{case_name}';
};

const getExampleDate = () => {
  const dateMapping = mappings.value.find(m => m.commcare_field.includes('date'));
  if (dateMapping?.system_variable) {
    return `{${dateMapping.system_variable}}`;
  }
  return '{next_visit_date}';
};

// Submit
const submit = () => {
  form.mappings = mappings.value.filter(m => 
    m.commcare_field && m.system_variable && m.label
  );

  form.post('/settings/mapping/update');
};
</script>
