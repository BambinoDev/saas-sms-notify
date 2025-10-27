<template>
  <SettingsLayout title="Général">
    <div class="py-6 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Informations générales</h1>
          <p class="mt-1 text-sm text-gray-600">
            Configuration de base de votre organisation
          </p>
        </div>

        <!-- Card placeholder -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
          <p class="text-gray-600">
            Page en construction... Les autres pages vont arriver dans les prochaines étapes.
          </p>
        </div>
      </div>
    </div>
  </SettingsLayout>
</template>

<script setup>
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

defineProps({
  organization: Object,
});
</script>

