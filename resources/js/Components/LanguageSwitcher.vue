<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();
const isOpen = ref(false);

const languages = [
  { code: 'fr', name: 'Français', flag: '🇫🇷' },
  { code: 'en', name: 'English', flag: '🇬🇧' },
];

const currentLanguage = computed(() => {
  return languages.find(lang => lang.code === locale.value);
});

const changeLanguage = (code) => {
  locale.value = code;
  localStorage.setItem('locale', code);
  isOpen.value = false;
};
</script>

<template>
  <div class="relative">
    <button
      @click="isOpen = !isOpen"
      class="flex items-center space-x-2 p-2 rounded-lg hover:bg-dark-100 transition-colors"
    >
      <span class="text-xl">{{ currentLanguage.flag }}</span>
      <span class="text-sm font-medium text-dark-700">{{ currentLanguage.code.toUpperCase() }}</span>
      <svg class="w-4 h-4 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Dropdown -->
    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-dark-200 py-1 z-50"
    >
      <button
        v-for="lang in languages"
        :key="lang.code"
        @click="changeLanguage(lang.code)"
        :class="[
          'w-full flex items-center space-x-3 px-4 py-2 text-sm transition-colors',
          locale === lang.code
            ? 'bg-primary-50 text-primary-700 font-medium'
            : 'text-dark-700 hover:bg-dark-50'
        ]"
      >
        <span class="text-xl">{{ lang.flag }}</span>
        <span>{{ lang.name }}</span>
        <svg
          v-if="locale === lang.code"
          class="w-4 h-4 ml-auto text-primary-600"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </button>
    </div>
  </div>
</template>

