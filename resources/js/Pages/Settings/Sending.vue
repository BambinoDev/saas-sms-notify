<template>
  <SettingsLayout title="Paramètres d'envoi">
    <div class="py-6 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Paramètres d'envoi des SMS</h1>
          <p class="mt-1 text-sm text-gray-600">
            Configuration des horaires et préférences d'envoi
          </p>
        </div>

        <!-- Message de succès -->
        <div v-if="form.recentlySuccessful" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium text-green-900">
              Paramètres enregistrés avec succès !
            </p>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Card Fuseau horaire -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Fuseau horaire
              </h2>
              <p class="text-sm text-gray-600 mt-1">
                Définissez le fuseau horaire de votre organisation
              </p>
            </div>

            <div class="p-6">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Fuseau horaire *
                </label>
                <select
                  v-model="form.timezone"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="Africa/Abidjan">Africa/Abidjan (Côte d'Ivoire, GMT+0)</option>
                  <option value="Africa/Accra">Africa/Accra (Ghana, GMT+0)</option>
                  <option value="Africa/Dakar">Africa/Dakar (Sénégal, GMT+0)</option>
                  <option value="Africa/Lagos">Africa/Lagos (Nigeria, GMT+1)</option>
                  <option value="Africa/Nairobi">Africa/Nairobi (Kenya, GMT+3)</option>
                  <option value="Africa/Johannesburg">Africa/Johannesburg (Afrique du Sud, GMT+2)</option>
                  <option value="Africa/Cairo">Africa/Cairo (Égypte, GMT+2)</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">
                  ⏰ Heure locale actuelle : {{ currentTime }}
                </p>
              </div>

              <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start gap-3">
                  <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <div>
                    <p class="text-sm font-semibold text-blue-900">
                      Pourquoi c'est important ?
                    </p>
                    <p class="text-sm text-blue-800 mt-1">
                      Tous les horaires des SMS seront calculés selon ce fuseau horaire. 
                      Par exemple, un SMS programmé à "08:00" sera envoyé à 08h00 heure locale.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Heure par défaut -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Heure d'envoi par défaut
              </h2>
              <p class="text-sm text-gray-600 mt-1">
                Heure suggérée lors de la création de nouvelles règles
              </p>
            </div>

            <div class="p-6">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Heure par défaut *
                </label>
                <input
                  type="time"
                  v-model="form.default_send_time"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                />
                <p class="text-xs text-gray-500 mt-1">
                  💡 Recommandation : Entre 08:00 et 10:00 pour un meilleur taux de lecture
                </p>
              </div>

              <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm text-green-900">
                  <strong>Exemple :</strong> Si vous choisissez 08:00, toutes les nouvelles règles que vous créerez 
                  proposeront automatiquement 08:00 comme heure d'envoi (vous pourrez la modifier pour chaque règle).
                </p>
              </div>
            </div>
          </div>

          <!-- Card Fenêtre d'envoi -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Fenêtre d'envoi autorisée
              </h2>
              <p class="text-sm text-gray-600 mt-1">
                Plage horaire pendant laquelle les SMS peuvent être envoyés
              </p>
            </div>

            <div class="p-6 space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Début de la fenêtre *
                  </label>
                  <input
                    type="time"
                    v-model="form.send_window_start"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Fin de la fenêtre *
                  </label>
                  <input
                    type="time"
                    v-model="form.send_window_end"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                  />
                </div>
              </div>

              <div class="p-4 bg-orange-50 border border-orange-200 rounded-lg">
                <div class="flex items-start gap-3">
                  <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                  <div>
                    <p class="text-sm font-semibold text-orange-900">
                      Protection anti-spam
                    </p>
                    <p class="text-sm text-orange-800 mt-1">
                      Aucun SMS ne sera envoyé en dehors de cette plage horaire. 
                      Si un SMS est programmé à 22:00 et que votre fenêtre se termine à 21:00, 
                      il sera automatiquement envoyé le lendemain matin.
                    </p>
                  </div>
                </div>
              </div>

              <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-sm font-medium text-gray-900 mb-2">
                  📊 Aperçu de votre fenêtre d'envoi :
                </p>
                <div class="flex items-center gap-2">
                  <div class="flex-1">
                    <div class="h-8 bg-red-100 rounded-l-lg flex items-center justify-center text-xs text-red-700 font-medium">
                      Bloqué
                    </div>
                  </div>
                  <div class="flex-[2]">
                    <div class="h-8 bg-green-500 flex items-center justify-center text-xs text-white font-bold">
                      ✓ Envoi autorisé ({{ form.send_window_start }} - {{ form.send_window_end }})
                    </div>
                  </div>
                  <div class="flex-1">
                    <div class="h-8 bg-red-100 rounded-r-lg flex items-center justify-center text-xs text-red-700 font-medium">
                      Bloqué
                    </div>
                  </div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                  <span>00:00</span>
                  <span>12:00</span>
                  <span>23:59</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Langue -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                </svg>
                Langue par défaut
              </h2>
              <p class="text-sm text-gray-600 mt-1">
                Langue principale des messages SMS
              </p>
            </div>

            <div class="p-6">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Langue *
                </label>
                <select
                  v-model="form.default_language"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                >
                  <option value="fr">🇫🇷 Français</option>
                  <option value="en">🇬🇧 English</option>
                  <option value="es">🇪🇸 Español</option>
                  <option value="ar">🇸🇦 العربية</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">
                  Cette langue sera utilisée pour les templates système
                </p>
              </div>
            </div>
          </div>

          <!-- Résumé -->
          <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg border-2 border-blue-200 shadow-sm">
            <div class="p-6">
              <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                📋 Résumé de votre configuration
              </h3>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-3 bg-white rounded-lg border border-blue-200">
                  <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Fuseau horaire</p>
                  <p class="text-sm font-bold text-gray-900">{{ form.timezone }}</p>
                </div>

                <div class="p-3 bg-white rounded-lg border border-green-200">
                  <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Heure par défaut</p>
                  <p class="text-sm font-bold text-gray-900">{{ form.default_send_time }}</p>
                </div>

                <div class="p-3 bg-white rounded-lg border border-orange-200">
                  <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Fenêtre d'envoi</p>
                  <p class="text-sm font-bold text-gray-900">
                    {{ form.send_window_start }} - {{ form.send_window_end }}
                  </p>
                </div>

                <div class="p-3 bg-white rounded-lg border border-purple-200">
                  <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Langue</p>
                  <p class="text-sm font-bold text-gray-900">{{ languageLabel }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-between">
            <Link
              href="/settings"
              class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium flex Peditems-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Retour
            </Link>

            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="form.processing">Enregistrement...</span>
              <span v-else>Enregistrer les paramètres</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </SettingsLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

const props = defineProps({
  organization: Object,
  settings: Object,
});

const form = useForm({
  timezone: props.settings?.timezone || 'Africa/Abidjan',
  default_send_time: props.settings?.default_send_time || '08:00',
  send_window_start: props.settings?.send_window_start || '06:00',
  send_window_end: props.settings?.send_window_end || '21:00',
  default_language: props.settings?.default_language || 'fr',
});

const currentTime = computed(() => {
  const now = new Date();
  return now.toLocaleTimeString('fr-FR', { 
    timeZone: form.timezone,
    hour: '2-digit',
    minute: '2-digit'
  });
});

const languageLabel = computed(() => {
  const labels = {
    fr: '🇫🇷 Français',
    en: '🇬🇧 English',
    es: '🇪🇸 Español',
    ar: '🇸🇦 العربية'
  };
  return labels[form.default_language] || 'Français';
});

const submit = () => {
  form.post('/settings/sending/update');
};
</script>