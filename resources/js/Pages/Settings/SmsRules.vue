<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Règles d'envoi SMS</h2>
          <p class="text-sm text-gray-600">
            Gérez les règles de rappel automatique
          </p>
        </div>
        <button
          @click="openCreateModal"
          class="px-4 py-2 text-white rounded-md font-medium shadow-lg hover:shadow-xl transition-all duration-200 border-2"
          style="background-color: #1e40af !important; border-color: #1e3a8a !important;"
        >
          + Nouvelle règle
        </button>
      </div>

      <!-- Flash messages -->
      <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
        {{ $page.props.flash.success }}
      </div>

      <!-- Liste règles -->
      <div class="bg-white shadow overflow-hidden rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Timing</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Heure</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Template</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="rule in rules" :key="rule.id">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ rule.name }}</div>
                <div class="text-xs text-gray-500">{{ rule.type }}</div>
              </td>
              <td class="px-6 py-4 text-sm">
                {{ rule.days_before === 0 ? 'Jour-J' : `J-${rule.days_before}` }}
              </td>
              <td class="px-6 py-4 text-sm">{{ rule.sending_time }}</td>
              <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                {{ rule.template }}
              </td>
              <td class="px-6 py-4">
                <span
                  :class="[
                    'px-2 py-1 text-xs rounded-full font-medium',
                    rule.active 
                      ? 'bg-green-100 text-green-800' 
                      : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ rule.active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right text-sm space-x-3">
                <button
                  @click="toggleRule(rule)"
                  class="text-indigo-600 hover:text-indigo-900 font-medium"
                >
                  {{ rule.active ? 'Désactiver' : 'Activer' }}
                </button>
                <button
                  @click="openEditModal(rule)"
                  class="text-blue-600 hover:text-blue-900 font-medium"
                >
                  Modifier
                </button>
                <button
                  @click="deleteRule(rule)"
                  class="text-red-600 hover:text-red-900 font-medium"
                >
                  Supprimer
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Création/Édition -->
    <div v-if="showModal" class="fixed z-10 inset-0 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

        <!-- Modal -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
          <form @submit.prevent="saveRule">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <h3 class="text-lg font-medium text-gray-900 mb-4">
                {{ editingRule ? 'Modifier la règle' : 'Nouvelle règle SMS' }}
              </h3>

              <div class="space-y-4">
                <!-- Nom -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Nom de la règle</label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Ex: Rappel J-3"
                  >
                </div>

                <!-- Type (uniquement pour création) -->
                <div v-if="!editingRule">
                  <label class="block text-sm font-medium text-gray-700">Identifiant technique</label>
                  <input
                    v-model="form.type"
                    type="text"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Ex: j-3 (sans espaces, minuscules)"
                  >
                  <p class="text-xs text-gray-500 mt-1">Utilisé en interne, ne peut pas être modifié après création</p>
                </div>

                <!-- Jours avant RDV -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Jours avant le RDV</label>
                  <select
                    v-model.number="form.days_before"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  >
                    <option :value="0">Jour-J (le jour même)</option>
                    <option :value="1">J-1 (1 jour avant)</option>
                    <option :value="2">J-2 (2 jours avant)</option>
                    <option :value="3">J-3 (3 jours avant)</option>
                    <option :value="7">J-7 (1 semaine avant)</option>
                  </select>
                </div>

                <!-- Heure d'envoi -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Heure d'envoi</label>
                  <input
                    v-model="form.sending_time"
                    type="time"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    @change="console.log('Time changed:', form.sending_time)"
                  >
                </div>

                <!-- Template -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Message (template)</label>
                  <textarea
                    v-model="form.template"
                    rows="4"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Ex: Bonjour Mme {case_name}, ..."
                  ></textarea>
                  <p class="text-xs text-gray-500 mt-1">
                    Variables : {case_name}, {anc_number}, {structure}
                  </p>
                  <div class="text-xs text-gray-500 mt-1">
                    {{ form.template.length }} / 160 caractères
                  </div>
                </div>

                <!-- Aperçu -->
                <div class="p-4 bg-gray-50 rounded-md">
                  <p class="text-xs font-medium text-gray-500 mb-2">Aperçu :</p>
                  <p class="text-sm text-gray-900">{{ previewMessage }}</p>
                </div>

                <!-- Active -->
                <div class="flex items-center">
                  <input
                    v-model="form.active"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  >
                  <label class="ml-2 block text-sm text-gray-900">
                    Activer cette règle immédiatement
                  </label>
                </div>

                <!-- Fenêtre de rattrapage -->
                <div class="border-t pt-4">
                  <h4 class="text-md font-medium text-gray-900 mb-3">⏰ Fenêtre de rattrapage</h4>
                  
                  <div class="flex items-center mb-3">
                    <input
                      v-model="form.window_enabled"
                      type="checkbox"
                      class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    >
                    <label class="ml-2 block text-sm text-gray-900">
                      <strong>Activer la fenêtre de rattrapage</strong>
                    </label>
                  </div>
                  
                  <p class="text-xs text-gray-500 mb-3">
                    Si désactivée, les SMS seront disponibles pour envoi à toute heure de la journée
                  </p>
                  
                  <div class="grid grid-cols-2 gap-4" :class="{ 'opacity-50': !form.window_enabled }">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">
                        <span class="mr-1">🕕</span> Heure de début
                      </label>
                      <input
                        v-model="form.window_start"
                        type="time"
                        :disabled="!form.window_enabled"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                      >
                      <small class="text-xs text-gray-500">Début de la fenêtre d'envoi</small>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">
                        <span class="mr-1">🕕</span> Heure de fin
                      </label>
                      <input
                        v-model="form.window_end"
                        type="time"
                        :disabled="!form.window_enabled"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                      >
                      <small class="text-xs text-gray-500">Fin de la fenêtre d'envoi</small>
                    </div>
                  </div>
                  
                  <div class="mt-3 p-3 bg-blue-50 rounded-md">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                      </div>
                      <div class="ml-3">
                        <p class="text-sm text-blue-700">
                          <strong>Exemple :</strong> Fenêtre 06:00 - 12:00 signifie que les SMS en attente pour cette règle 
                          ne seront disponibles pour envoi que pendant cette plage horaire. En dehors de cette fenêtre, 
                          ils seront ignorés par l'API.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Boutons -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                type="submit"
                :disabled="saving"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-lg px-4 py-2 text-base font-medium text-white hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:bg-gray-400 disabled:shadow-none transition-all duration-200 border-2"
                style="background-color: #1e40af !important; border-color: #1e3a8a !important;"
              >
                {{ saving ? 'Enregistrement...' : (editingRule ? 'Modifier' : 'Créer') }}
              </button>
              <button
                type="button"
                @click="closeModal"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all duration-200"
              >
                Annuler
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  rules: Array,
});

const showModal = ref(false);
const editingRule = ref(null);
const saving = ref(false);

const form = useForm({
  name: '',
  type: '',
  days_before: 2,
  sending_time: '08:00',
  template: '',
  active: true,
  priority: 0,
  window_enabled: true,
  window_start: '06:00',
  window_end: '12:00',
});

const openCreateModal = () => {
  editingRule.value = null;
  form.reset();
  form.days_before = 2;
  form.sending_time = '08:00';
  form.active = true;
  form.window_enabled = true;
  form.window_start = '06:00';
  form.window_end = '12:00';
  showModal.value = true;
};

const openEditModal = (rule) => {
  editingRule.value = rule;
  form.name = rule.name;
  form.days_before = rule.days_before;
  form.sending_time = rule.sending_time;
  form.template = rule.template;
  form.active = rule.active;
  form.priority = rule.priority;
  form.window_enabled = rule.window_enabled ?? true;
  form.window_start = rule.window_start ?? '06:00';
  form.window_end = rule.window_end ?? '12:00';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingRule.value = null;
  form.reset();
};

const saveRule = () => {
  saving.value = true;

  // Log pour debug
  console.log('Form data being sent:', {
    name: form.name,
    days_before: form.days_before,
    sending_time: form.sending_time,
    template: form.template,
    active: form.active,
    priority: form.priority,
    window_enabled: form.window_enabled,
    window_start: form.window_start,
    window_end: form.window_end,
  });

  if (editingRule.value) {
    const url = `/settings/sms-rules/${editingRule.value.id}/update`;
    
    // S'assurer que tous les champs sont présents
    const data = {
      name: form.name,
      days_before: form.days_before,
      sending_time: form.sending_time,
      template: form.template,
      active: form.active,
      priority: form.priority,
    };
    
    console.log('Sending update with data:', data);
    
    // Utiliser router.post avec data explicite
    router.post(url, data, {
      preserveScroll: true,
      onSuccess: () => {
        console.log('Update success');
        closeModal();
        saving.value = false;
      },
      onError: (errors) => {
        console.error('Update errors:', errors);
        saving.value = false;
      },
    });
  } else {
    // Création
    console.log('Creating rule:', form.data());
    
    router.post('/settings/sms-rules', {
      name: form.name,
      type: form.type,
      days_before: form.days_before,
      sending_time: form.sending_time,
      template: form.template,
      active: form.active,
      priority: form.priority,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        console.log('Create success');
        closeModal();
        saving.value = false;
      },
      onError: (errors) => {
        console.error('Create errors:', errors);
        saving.value = false;
      },
    });
  }
};

const toggleRule = (rule) => {
  router.post(`/settings/sms-rules/${rule.id}/toggle`, {}, {
    preserveScroll: true,
  });
};

const deleteRule = (rule) => {
  if (confirm(`Supprimer la règle "${rule.name}" ?\n\nCette action est irréversible.`)) {
    router.delete(`/settings/sms-rules/${rule.id}`, {
      preserveScroll: true,
    });
  }
};

const previewMessage = computed(() => {
  return form.template
    .replace('{case_name}', 'Marie Kouassi')
    .replace('{anc_number}', '3')
    .replace('{structure}', 'Centre de Santé de Yopougon');
});
</script>