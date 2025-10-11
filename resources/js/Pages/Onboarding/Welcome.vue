<script setup>
import { Head, router } from '@inertiajs/vue3';
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue';
import Button from '@/Components/ui/Button.vue';

const startOnboarding = () => {
  router.visit('/onboarding/company');
};
</script>

<template>
  <Head title="Bienvenue - S-Remind" />

  <OnboardingLayout :current-step="1">
    
    <!-- Welcome Content -->
    <div class="text-center space-y-8 animate-fade-in">
      
      <!-- Icon -->
      <div class="flex justify-center">
        <div class="w-20 h-20 bg-gradient-to-br from-primary-500 to-accent-500 rounded-2xl flex items-center justify-center">
          <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </div>
      </div>

      <!-- Title -->
      <div class="space-y-3">
        <h1 class="text-4xl font-bold text-dark-900">
          Bienvenue sur S-Remind ! 🎉
        </h1>
        <p class="text-xl text-dark-600 max-w-2xl mx-auto">
          Nous allons configurer votre compte en quelques minutes pour que vous puissiez 
          commencer à envoyer des rappels SMS automatisés.
        </p>
      </div>

      <!-- What to Expect -->
      <div class="bg-primary-50 border border-primary-100 rounded-xl p-8 text-left max-w-2xl mx-auto">
        <h3 class="text-lg font-semibold text-dark-900 mb-4 flex items-center">
          <svg class="w-6 h-6 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Ce que nous allons configurer
        </h3>
        
        <ul class="space-y-3">
          <li class="flex items-start">
            <span class="flex-shrink-0 w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-3 mt-0.5">1</span>
            <div>
              <p class="font-medium text-dark-900">Informations de votre organisation</p>
              <p class="text-sm text-dark-600 mt-0.5">Pays, fuseau horaire, secteur d'activité</p>
            </div>
          </li>

          <li class="flex items-start">
            <span class="flex-shrink-0 w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-3 mt-0.5">2</span>
            <div>
              <p class="font-medium text-dark-900">Connexion à CommCare</p>
              <p class="text-sm text-dark-600 mt-0.5">Domain, clé API et application</p>
            </div>
          </li>

          <li class="flex items-start">
            <span class="flex-shrink-0 w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-3 mt-0.5">3</span>
            <div>
              <p class="font-medium text-dark-900">Configuration téléphonie</p>
              <p class="text-sm text-dark-600 mt-0.5">Validation des numéros selon votre pays</p>
            </div>
          </li>

          <li class="flex items-start">
            <span class="flex-shrink-0 w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-3 mt-0.5">4</span>
            <div>
              <p class="font-medium text-dark-900">Mapping des champs</p>
              <p class="text-sm text-dark-600 mt-0.5">Lier vos champs CommCare aux variables SMS</p>
            </div>
          </li>
        </ul>
      </div>

      <!-- Estimated Time -->
      <div class="flex items-center justify-center space-x-2 text-dark-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm">Temps estimé : <strong>5 minutes</strong></span>
      </div>

      <!-- CTA Button -->
      <div class="pt-4">
        <Button 
          variant="primary" 
          size="lg"
          @click="startOnboarding"
          class="group"
        >
          Commencer la configuration
          <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </Button>
      </div>

    </div>

  </OnboardingLayout>
</template>
