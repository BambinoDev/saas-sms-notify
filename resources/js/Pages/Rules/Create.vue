<template>
  <AppLayout title="Nouvelle règle SMS">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-6 flex items-center gap-2 text-sm text-gray-600">
          <Link href="/rules" class="hover:text-blue-600">Règles SMS</Link>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
          <span class="text-gray-900 font-medium">Nouvelle règle</span>
        </nav>

                <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Créer une règle SMS</h1>
          <p class="mt-2 text-sm text-gray-600">
            Définissez quand et comment envoyer automatiquement des SMS aux bénéficiaires
          </p>
        </div>

        <!-- Formulaire -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- ÉTAPE 1 : Informations de base -->
          <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-white">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-lg">
                  1
                </div>
                    <div>
                  <h2 class="text-lg font-semibold text-gray-900">Informations générales</h2>
                  <p class="text-sm text-gray-600">Nommez et décrivez votre règle</p>
                </div>
                    </div>
                </div>

            <div class="p-6 space-y-4">
                            <!-- Nom -->
                            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Nom de la règle *
                  <span class="text-gray-500 font-normal ml-2">
                    (ex: "Rappel RDV J-2", "Message bienvenue")
                  </span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                  placeholder="Donnez un nom court et explicite"
                  class="input-field"
                  :class="{ 'border-red-500': form.errors.name }"
                                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <!-- Description -->
                            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Description (optionnel)
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="2"
                  placeholder="Décrivez l'objectif de cette règle..."
                  class="input-field"
                                ></textarea>
                            </div>

                            <!-- Template -->
                            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Modèle de message *
                                </label>
                                <select
                                    v-model="form.sms_template_id"
                                    required
                  class="input-field"
                  :class="{ 'border-red-500': form.errors.sms_template_id }"
                                >
                  <option value="">-- Choisissez un modèle de message --</option>
                                    <option
                                        v-for="template in templates"
                                        :key="template.id"
                                        :value="template.id"
                                    >
                                        {{ template.name }}
                                    </option>
                                </select>
                <p v-if="form.errors.sms_template_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.sms_template_id }}
                </p>
              </div>
                                </div>
                            </div>

          <!-- ÉTAPE 2 : Déclenchement (LA PARTIE CRITIQUE) -->
          <div class="bg-white shadow-sm rounded-lg border-2 border-blue-300">
            <div class="p-6 border-b border-blue-200 bg-gradient-to-r from-blue-100 to-blue-50">
              <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-lg">
                  2
                </div>
                            <div>
                  <h2 class="text-lg font-semibold text-gray-900">Quand envoyer le SMS ?</h2>
                  <p class="text-sm text-gray-600">
                    Cette section détermine automatiquement quels dossiers recevront le SMS
                  </p>
                </div>
              </div>

              <!-- Prévisualisation de la règle -->
              <div class="mt-4 p-4 bg-white border-2 border-blue-400 rounded-lg shadow-sm">
                <div class="flex items-start gap-3">
                  <svg class="w-6 h-6 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <div class="flex-1">
                    <p class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-1">
                      Aperçu de votre règle en langage clair :
                    </p>
                    <p class="text-base font-semibold text-blue-900">
                      {{ rulePreview }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="p-6 space-y-6">
              <!-- Question 1 : Champ - VERSION MODERNE -->
              <div class="mb-8">
                <!-- Header -->
                <div class="mb-4 p-4 bg-gradient-to-r from-blue-600 to-blue-500 rounded-lg shadow-md">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white text-blue-600 rounded-full flex items-center justify-center font-bold text-lg">
                      A
                    </div>
                    <div class="flex-1">
                      <h3 class="text-lg font-bold text-white">
                        Choisissez le champ de date à surveiller
                      </h3>
                      <p class="text-sm text-blue-100 mt-1">
                        Sélectionnez le champ qui déclenchera l'envoi du SMS
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Barre de recherche + Options d'affichage -->
                <div class="mb-4 flex gap-3">
                  <!-- Recherche -->
                  <div class="flex-1 relative">
                    <input
                      v-model="searchQuery"
                      type="text"
                      placeholder="🔍 Rechercher un champ..."
                      class="w-full pl-10 pr-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    />
                    <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                  </div>

                  <!-- Toggle vue compacte -->
                  <button
                    @click="compactView = !compactView"
                    type="button"
                    class="px-4 py-2.5 border-2 border-gray-300 rounded-lg hover:bg-gray-50 transition flex items-center gap-2 text-sm font-medium text-gray-700"
                  >
                    <svg v-if="compactView" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    {{ compactView ? 'Vue détaillée' : 'Vue compacte' }}
                  </button>
                </div>

                <!-- Compteur de résultats -->
                <div v-if="searchQuery" class="mb-3 text-sm text-gray-600">
                  {{ totalFilteredFields }} champ(s) trouvé(s)
                </div>

                <!-- Liste des catégories -->
                <div class="space-y-4">
                  <div
                    v-for="(category, categoryKey) in filteredCategories"
                    :key="categoryKey"
                    class="border-2 border-gray-200 rounded-lg overflow-hidden"
                  >
                    <!-- Header catégorie (cliquable pour expand/collapse) -->
                    <button
                      @click="expandedCategories[categoryKey] = !expandedCategories[categoryKey]"
                      type="button"
                      class="w-full px-4 py-3 bg-gray-50 hover:bg-gray-100 flex items-center justify-between transition"
                    >
                      <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ category.icon }}</span>
                        <span class="font-semibold text-gray-900">{{ category.label }}</span>
                        <span class="text-sm text-gray-500">({{ category.fields.length }})</span>
                      </div>
                      <svg
                        class="w-5 h-5 text-gray-500 transition-transform"
                        :class="{ 'rotate-180': expandedCategories[categoryKey] }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>

                    <!-- Champs de la catégorie -->
                    <div
                      v-show="expandedCategories[categoryKey]"
                      class="p-3 space-y-2"
                    >
                      <!-- Vue COMPACTE -->
                      <div v-if="compactView" class="space-y-1.5">
                        <label
                          v-for="field in category.fields"
                          :key="field.value"
                          class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-all hover:bg-blue-50"
                          :class="{
                            'bg-blue-100 border-2 border-blue-500': form.trigger_field === field.value,
                            'border-2 border-transparent': form.trigger_field !== field.value
                          }"
                        >
                          <input
                            type="radio"
                            v-model="form.trigger_field"
                            :value="field.value"
                            class="w-5 h-5 text-blue-600"
                            @change="updatePreview"
                          />
                          <span class="text-xl">{{ field.icon }}</span>
                          <span class="flex-1 font-medium text-gray-900">{{ field.label }}</span>
                          <svg
                            v-if="form.trigger_field === field.value"
                            class="w-5 h-5 text-blue-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                          </svg>
                                </label>
                      </div>

                      <!-- Vue DÉTAILLÉE -->
                      <div v-else class="space-y-3">
                        <label
                          v-for="field in category.fields"
                          :key="field.value"
                          class="block cursor-pointer"
                        >
                          <div
                            class="relative p-4 border-3 rounded-xl transition-all"
                            :class="{
                              'border-blue-600 bg-blue-50 shadow-lg': form.trigger_field === field.value,
                              'border-gray-300 bg-white hover:border-blue-400 hover:bg-blue-50': form.trigger_field !== field.value
                            }"
                          >
                            <div class="flex items-start gap-4">
                              <input
                                type="radio"
                                    v-model="form.trigger_field"
                                :value="field.value"
                                class="mt-1 w-5 h-5 text-blue-600"
                                @change="updatePreview"
                              />
                              <div class="flex-shrink-0">
                                <div 
                                  class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl"
                                  :class="{
                                    'bg-blue-600': form.trigger_field === field.value,
                                    'bg-gray-200': form.trigger_field !== field.value
                                  }"
                                >
                                  <span v-if="form.trigger_field === field.value" class="filter brightness-0 invert">
                                    {{ field.icon }}
                                  </span>
                                  <span v-else>{{ field.icon }}</span>
                                </div>
                              </div>
                              <div class="flex-1">
                                <h4 
                                  class="text-base font-bold mb-1"
                                  :class="{
                                    'text-blue-900': form.trigger_field === field.value,
                                    'text-gray-900': form.trigger_field !== field.value
                                  }"
                                >
                                  {{ field.label }}
                                </h4>
                                <p class="text-sm text-gray-600">{{ field.description }}</p>
                                <p class="text-xs text-blue-600 mt-1">{{ field.example }}</p>
                              </div>
                              <svg
                                v-if="form.trigger_field === field.value"
                                class="w-6 h-6 text-blue-600 flex-shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                              >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                              </svg>
                            </div>
                          </div>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Message si aucun résultat -->
                <div
                  v-if="searchQuery && totalFilteredFields === 0"
                  class="text-center py-8 text-gray-500"
                >
                  <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <p class="font-medium">Aucun champ trouvé pour "{{ searchQuery }}"</p>
                  <button
                    @click="searchQuery = ''"
                    type="button"
                    class="mt-2 text-blue-600 hover:text-blue-700 text-sm font-medium"
                  >
                    Réinitialiser la recherche
                  </button>
                </div>

                <!-- Message d'aide -->
                <div class="mt-4 p-4 bg-yellow-50 border-2 border-yellow-300 rounded-lg">
                  <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                      <p class="text-sm font-semibold text-yellow-900">
                        💡 Astuce
                      </p>
                      <p class="text-sm text-yellow-800 mt-1">
                        Utilisez la <strong>barre de recherche</strong> pour trouver rapidement un champ.
                        Les champs sont organisés par catégorie pour faciliter votre choix.
                      </p>
                    </div>
                  </div>
                                </div>
                            </div>

              <!-- Question 2 : Condition -->
                            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-3">
                  <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs font-bold mr-2">
                    B
                  </span>
                  À quel moment par rapport à cette date ? *
                                </label>

                <div class="space-y-3">
                  <label
                    v-for="condition in conditionOptions"
                    :key="condition.value"
                    class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-blue-300 hover:bg-blue-50"
                    :class="{
                      'border-blue-600 bg-blue-50': form.trigger_condition === condition.value,
                      'border-gray-200': form.trigger_condition !== condition.value
                    }"
                  >
                    <input
                      type="radio"
                                    v-model="form.trigger_condition"
                      :value="condition.value"
                      class="mt-1"
                      @change="updatePreview"
                    />
                    <div class="flex-1">
                      <div class="flex items-center gap-2">
                        <span class="text-2xl">{{ condition.icon }}</span>
                        <span class="font-semibold text-gray-900">{{ condition.label }}</span>
                      </div>
                      <p class="text-sm text-gray-600 mt-1">{{ condition.description }}</p>
                      <div class="mt-2 p-2 bg-green-50 border border-green-200 rounded">
                        <p class="text-xs text-green-800">
                          <strong>✓ Cas d'usage :</strong> {{ condition.useCase }}
                        </p>
                      </div>
                    </div>
                  </label>
                                </div>
                            </div>

              <!-- Question 3 : Valeur + Unité -->
              <div class="border-t pt-6">
                <label class="block text-sm font-semibold text-gray-900 mb-3">
                  <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs font-bold mr-2">
                    C
                  </span>
                  Combien de temps {{ conditionPreposition }} ?
                </label>

                <div class="grid grid-cols-2 gap-4">
                            <!-- Valeur -->
                            <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Nombre *
                                </label>
                                <input
                      v-model.number="form.trigger_value"
                      type="number"
                      min="0"
                                    required
                      placeholder="Ex: 2"
                      class="input-field"
                      @input="updatePreview"
                    />
                    <p class="mt-1 text-xs text-gray-600">
                      {{ valueHint }}
                    </p>
                            </div>

                            <!-- Unité -->
                            <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Unité *
                                </label>
                                <select
                                    v-model="form.trigger_unit"
                                    required
                      class="input-field"
                      @change="updatePreview"
                                >
                      <option value="days">Jours</option>
                                    <option value="hours">Heures</option>
                                </select>
                  </div>
                </div>
              </div>

              <!-- Exemples concrets -->
              <div class="border-t pt-6">
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-4">
                  <p class="text-sm font-semibold text-purple-900 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Exemples de configurations populaires
                  </p>
                  <div class="space-y-2">
                    <div
                      v-for="example in examples"
                      :key="example.name"
                      class="flex items-start gap-2 text-xs"
                    >
                      <span class="text-green-600 font-bold mt-0.5">✓</span>
                      <div>
                        <strong class="text-purple-900">{{ example.name }} :</strong>
                        <span class="text-purple-700"> {{ example.config }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
                                </div>
                            </div>

          <!-- ÉTAPE 3 : Fréquence génération automatique -->
          <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-white">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg">
                  3
                </div>
                            <div>
                  <h2 class="text-lg font-semibold text-gray-900">Fréquence de vérification automatique</h2>
                  <p class="text-sm text-gray-600">
                    À quelle fréquence le système doit-il vérifier les dossiers éligibles ?
                  </p>
                                </div>
                                </div>
                            </div>

            <div class="p-6 space-y-4">
              <!-- Fréquence -->
                            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Fréquence de vérification *
                                </label>
                                <select
                                    v-model="form.generation_frequency"
                                    required
                  class="input-field"
                >
                  <option value="daily">📅 Quotidien - Tous les jours</option>
                  <option value="weekly">📆 Hebdomadaire - 1 fois par semaine</option>
                  <option value="monthly">🗓️ Mensuel - 1 fois par mois</option>
                                </select>
                <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
                  <strong>💡 Recommandation :</strong>
                  <span class="text-gray-700">
                    Pour les rappels de RDV, utilisez <strong>Quotidien</strong> pour ne manquer aucun dossier.
                  </span>
                                </div>
                            </div>

              <!-- Heure génération -->
                            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Heure de vérification *
                                </label>
                                <input
                                    v-model="form.generation_time"
                                    type="time"
                                    required
                  class="input-field"
                />
                <p class="mt-1 text-xs text-gray-600">
                  💡 <strong>Conseil :</strong> Choisissez une heure de nuit (ex: 01:00) pour ne pas surcharger le système en journée
                </p>
                            </div>

              <!-- Jour semaine (si weekly) -->
                            <div v-if="form.generation_frequency === 'weekly'">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Jour de la semaine *
                                </label>
                                <select
                  v-model.number="form.generation_day_of_week"
                                    required
                  class="input-field"
                >
                  <option :value="1">Lundi</option>
                  <option :value="2">Mardi</option>
                  <option :value="3">Mercredi</option>
                  <option :value="4">Jeudi</option>
                  <option :value="5">Vendredi</option>
                  <option :value="6">Samedi</option>
                  <option :value="0">Dimanche</option>
                                </select>
              </div>

              <!-- Jour mois (si monthly) -->
              <div v-if="form.generation_frequency === 'monthly'">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Jour du mois (1-31) *
                </label>
                <input
                  v-model.number="form.generation_day_of_month"
                  type="number"
                  min="1"
                  max="31"
                  required
                  placeholder="Ex: 1 pour le 1er du mois"
                  class="input-field"
                />
              </div>
            </div>
          </div>

          <!-- ÉTAPE 4 : Heure d'envoi SMS -->
          <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-white">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-600 text-white rounded-full flex items-center justify-center font-bold text-lg">
                  4
                </div>
                <div>
                  <h2 class="text-lg font-semibold text-gray-900">Heure d'envoi des SMS</h2>
                  <p class="text-sm text-gray-600">
                    À quelle heure envoyer les SMS aux bénéficiaires ?
                  </p>
                </div>
                                </div>
                            </div>

            <div class="p-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Heure d'envoi (optionnel)
                                </label>
                <input
                  v-model="form.send_time"
                  type="time"
                  class="input-field"
                />
                <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm">
                  <strong>💡 Recommandation :</strong>
                  <span class="text-gray-700">
                    Envoyez entre <strong>08:00 et 18:00</strong> (heures ouvrables).
                    Si vide, les SMS seront envoyés immédiatement après génération.
                  </span>
                </div>
              </div>
                                </div>
                            </div>

          <!-- ÉTAPE 5 : Options avancées -->
          <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="p-6 border-b border-gray-200">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-600 text-white rounded-full flex items-center justify-center font-bold text-lg">
                  5
                </div>
                            <div>
                  <h2 class="text-lg font-semibold text-gray-900">Options avancées</h2>
                  <p class="text-sm text-gray-600">Paramètres optionnels</p>
                                </div>
                                </div>
            </div>

            <div class="p-6 space-y-4">
              <!-- Priorité -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Priorité (0 = urgent, 10 = faible)
                </label>
                <input
                  v-model.number="form.priority"
                  type="number"
                  min="0"
                  max="10"
                  class="input-field"
                />
                <p class="mt-1 text-xs text-gray-600">
                  💡 Les règles prioritaires (0-2) seront exécutées en premier
                </p>
                            </div>

                            <!-- Limite quotidienne -->
                            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Limite quotidienne de SMS (optionnel)
                                </label>
                                <input
                  v-model.number="form.daily_limit"
                                    type="number"
                                    min="1"
                  placeholder="Illimité"
                  class="input-field"
                />
                <p class="mt-1 text-xs text-gray-600">
                  💡 Utile pour contrôler les coûts. Laissez vide pour ne pas limiter.
                </p>
                            </div>

              <!-- Activer -->
              <div class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                  id="is_active"
                  class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <label for="is_active" class="flex-1">
                  <span class="text-sm font-medium text-gray-900">
                    ✅ Activer cette règle immédiatement
                  </span>
                  <p class="text-xs text-gray-600 mt-1">
                    Si décoché, la règle sera créée mais inactive
                  </p>
                                </label>
              </div>
            </div>
                            </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-6 border-t">
            <Link
              href="/rules"
              class="btn-secondary"
            >
              Annuler
            </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
              class="btn-primary"
            >
              <span v-if="!form.processing">Créer la règle</span>
              <span v-else class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Création...
              </span>
                                </button>
                            </div>
                        </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  templates: Array,
  availableFields: Object, // ✅ Ajouter cette ligne
});

const form = useForm({
  name: '',
  description: '',
  sms_template_id: '',
  trigger_field: '',
  trigger_condition: '',
  trigger_value: '2', // ✅ String au lieu de number
  trigger_unit: 'days',
  generation_frequency: 'daily',
  generation_time: '01:00',
  generation_day_of_week: null,
  generation_day_of_month: null,
  send_time: '08:00',
  priority: 5,
  daily_limit: null,
  is_active: true,
});

// État pour la recherche et l'affichage
const searchQuery = ref('');
const compactView = ref(true); // Vue compacte par défaut
const expandedCategories = ref({
  dates: true, // Ouvrir "Dates" par défaut
  identifiers: false,
  location: false,
  medical: false,
  contact: false,
  other: false
});

// Transformer availableFields en options d'affichage
const fieldOptions = computed(() => {
  if (!props.availableFields || Object.keys(props.availableFields).length === 0) {
    // Fallback si aucun champ configuré
    return [
      {
        value: 'next_visit_date',
        label: 'Date du prochain rendez-vous',
        icon: '📅',
        description: 'Champ par défaut pour les rappels de RDV',
        example: 'Rappeler 2 jours avant un RDV',
        type: 'date'
      }
    ];
  }

  // Mapper les champs de l'organisation
  return Object.entries(props.availableFields).map(([fieldKey, fieldLabel]) => {
    // Déterminer l'icône selon le nom du champ
    let icon = '📋'; // Par défaut
    let description = `Surveiller le champ "${fieldLabel}"`;
    let example = `Envoyer un SMS basé sur ${fieldLabel}`;
    let type = 'text';

    // Détecter les champs de date
    if (fieldKey.includes('date') || fieldKey.includes('_at')) {
      icon = '📅';
      type = 'date';
      description = `Surveiller la date : ${fieldLabel}`;
      example = `Ex: Rappel 2 jours avant ${fieldLabel}`;
    }
    // Détecter les champs de création
    else if (fieldKey.includes('created') || fieldKey.includes('inscription')) {
      icon = '🆕';
      type = 'date';
      description = `Date de création ou inscription : ${fieldLabel}`;
      example = `Ex: Message 1 jour après ${fieldLabel}`;
    }
    // Détecter les champs de modification
    else if (fieldKey.includes('updated') || fieldKey.includes('modified')) {
      icon = '🔄';
      type = 'date';
      description = `Date de dernière modification : ${fieldLabel}`;
      example = `Ex: Relance après 30 jours sans ${fieldLabel}`;
    }
    // Détecter les champs de RDV / consultation
    else if (fieldKey.includes('rdv') || fieldKey.includes('visite') || fieldKey.includes('cpn') || fieldKey.includes('consultation')) {
      icon = '🏥';
      type = 'date';
      description = `Date de rendez-vous : ${fieldLabel}`;
      example = `Ex: Rappel avant ${fieldLabel}`;
    }
    // Détecter les champs de vaccination
    else if (fieldKey.includes('vaccin') || fieldKey.includes('immunisation')) {
      icon = '💉';
      type = 'date';
      description = `Date de vaccination : ${fieldLabel}`;
      example = `Ex: Rappel de vaccination ${fieldLabel}`;
    }
    // Détecter les champs d'accouchement
    else if (fieldKey.includes('accouchement') || fieldKey.includes('delivery')) {
      icon = '👶';
      type = 'date';
      description = `Date d'accouchement : ${fieldLabel}`;
      example = `Ex: Suivi post-accouchement ${fieldLabel}`;
    }

    return {
      value: fieldKey,
      label: fieldLabel,
      icon: icon,
      description: description,
      example: example,
      type: type
    };
  });
});

// Catégoriser les champs
const categorizedFields = computed(() => {
  const categories = {
    dates: { label: '📅 Dates et rendez-vous', icon: '📅', fields: [] },
    identifiers: { label: '🆔 Identifiants', icon: '🆔', fields: [] },
    location: { label: '📍 Localisation', icon: '📍', fields: [] },
    medical: { label: '🏥 Informations médicales', icon: '🏥', fields: [] },
    contact: { label: '📞 Contact', icon: '📞', fields: [] },
    other: { label: '📋 Autres', icon: '📋', fields: [] }
  };

  fieldOptions.value.forEach(field => {
    const key = field.value.toLowerCase();
    
    // Catégorisation automatique
    if (key.includes('date') || key.includes('_at') || key.includes('rdv') || 
        key.includes('visite') || key.includes('cpn') || key.includes('vaccin')) {
      categories.dates.fields.push(field);
    }
    else if (key.includes('id') || key.includes('code') || key.includes('number') || 
             key.includes('case_')) {
      categories.identifiers.fields.push(field);
    }
    else if (key.includes('district') || key.includes('region') || key.includes('commune') || 
             key.includes('structure') || key.includes('site') || key.includes('lieu')) {
      categories.location.fields.push(field);
    }
    else if (key.includes('age') || key.includes('poids') || key.includes('taille') || 
             key.includes('tension') || key.includes('medical') || key.includes('diagnostic')) {
      categories.medical.fields.push(field);
    }
    else if (key.includes('phone') || key.includes('telephone') || key.includes('contact') || 
             key.includes('email')) {
      categories.contact.fields.push(field);
    }
    else {
      categories.other.fields.push(field);
    }
  });

  // Retourner seulement les catégories non vides
  return Object.entries(categories)
    .filter(([_, cat]) => cat.fields.length > 0)
    .reduce((acc, [key, cat]) => {
      acc[key] = cat;
      return acc;
    }, {});
});

// Filtrer selon la recherche
const filteredCategories = computed(() => {
  if (!searchQuery.value) {
    return categorizedFields.value;
  }

  const query = searchQuery.value.toLowerCase();
  const filtered = {};

  Object.entries(categorizedFields.value).forEach(([key, category]) => {
    const matchingFields = category.fields.filter(field => 
      field.label.toLowerCase().includes(query) ||
      field.value.toLowerCase().includes(query) ||
      field.description.toLowerCase().includes(query)
    );

    if (matchingFields.length > 0) {
      filtered[key] = {
        ...category,
        fields: matchingFields
      };
    }
  });

  return filtered;
});

// Compter le total de champs filtrés
const totalFilteredFields = computed(() => {
  return Object.values(filteredCategories.value)
    .reduce((sum, cat) => sum + cat.fields.length, 0);
});

// Options de conditions
const conditionOptions = [
  {
    value: 'before',
    label: 'AVANT cette date',
    icon: '⏪',
    description: 'Envoyer le SMS X jours/heures AVANT l\'événement',
    useCase: 'Parfait pour les rappels (ex: "RDV dans 2 jours")'
  },
  {
    value: 'equals',
    label: 'LE JOUR MÊME de cette date',
    icon: '📍',
    description: 'Envoyer le SMS exactement le jour de l\'événement',
    useCase: 'Pour les rappels urgents (ex: "Votre RDV est AUJOURD\'HUI")'
  },
  {
    value: 'after',
    label: 'APRÈS cette date',
    icon: '⏩',
    description: 'Envoyer le SMS X jours/heures APRÈS l\'événement',
    useCase: 'Pour les suivis (ex: "Bienvenue, comment ça va ?")'
  },
];

// Exemples
const examples = [
  { name: 'Rappel RDV 2 jours avant', config: 'Date RDV → AVANT → 2 jours' },
  { name: 'Rappel RDV aujourd\'hui', config: 'Date RDV → LE JOUR MÊME → 0 jours' },
  { name: 'Bienvenue 1 jour après inscription', config: 'Date création → APRÈS → 1 jour' },
  { name: 'Relance inactifs 30 jours', config: 'Dernière modif → APRÈS → 30 jours' },
];

// Prévisualisation dynamique
const rulePreview = computed(() => {
  if (!form.trigger_field || !form.trigger_condition) {
    return "⚠️ Configurez les paramètres ci-dessous pour voir votre règle";
  }

  const fieldLabels = {
    'next_visit_date': 'la date du prochain RDV',
    'created_at': 'la création du dossier',
    'updated_at': 'la dernière modification',
  };

  const conditionLabels = {
    'before': 'AVANT',
    'equals': 'LE JOUR MÊME DE',
    'after': 'APRÈS',
  };

  const field = fieldLabels[form.trigger_field] || form.trigger_field;
  const condition = conditionLabels[form.trigger_condition] || form.trigger_condition;
  const value = form.trigger_value || 0;
  const unit = form.trigger_unit === 'days' ? 'jour(s)' : 'heure(s)';

  if (form.trigger_condition === 'equals' && value === 0) {
    return `📌 Envoyer un SMS ${condition} ${field}`;
  }

  return `📌 Envoyer un SMS ${value} ${unit} ${condition} ${field}`;
});

// Hint dynamique pour la valeur
const valueHint = computed(() => {
  if (form.trigger_condition === 'equals') {
    return '💡 Mettez 0 pour "le jour même"';
  }
  if (form.trigger_condition === 'before') {
    return '💡 Ex: 2 pour "2 jours avant"';
  }
  if (form.trigger_condition === 'after') {
    return '💡 Ex: 7 pour "7 jours après"';
  }
  return '';
});

// Préposition dynamique
const conditionPreposition = computed(() => {
  if (form.trigger_condition === 'before') return 'avant';
  if (form.trigger_condition === 'after') return 'après';
  return '';
});

// Update preview
const updatePreview = () => {
  // Force recompute
};

// Submit
const submit = () => {
  form.post('/rules');
};
</script>

<style scoped>
.input-field {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition;
}

.btn-primary {
  @apply px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed;
}

.btn-secondary {
  @apply px-6 py-2.5 bg-white border-2 border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition;
}

/* Bordure épaisse */
.border-3 {
  border-width: 3px;
}

/* Effet scale */
.scale-102 {
  transform: scale(1.02);
}
</style>