<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GriotLogo from '@/Components/Branding/GriotLogo.vue';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const isDev = import.meta.env.MODE === 'development' || import.meta.env.MODE === 'local';

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900">
    <Head title="Login" />

    <div class="max-w-md w-full p-8 bg-white dark:bg-gray-800 rounded-lg shadow-xl">
      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <GriotLogo variant="compact" height="60" />
      </div>
      
      <!-- Titre + Tagline -->
      <h1 class="font-display text-4xl font-bold text-center text-gray-900 dark:text-white mb-2">GRIOT</h1>
      <p class="text-sm text-gray-500 text-center italic mb-3">Your Digital Messenger</p>
      <p class="text-center text-gray-600 dark:text-gray-300 mb-8">Plateforme SMS Multi-Tenant</p>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Email
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-griot-terracotta focus:border-griot-terracotta dark:bg-gray-700 dark:text-white"
              :placeholder="isDev ? 'admin@griot.local' : 'votre.email@domaine.com'"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.email }}
            </p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Mot de passe
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              autocomplete="current-password"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-griot-terracotta focus:border-griot-terracotta dark:bg-gray-700 dark:text-white"
              placeholder="••••••••"
            />
          </div>

          <div class="flex items-center">
            <input
              id="remember"
              v-model="form.remember"
              type="checkbox"
              class="h-4 w-4 text-griot-terracotta focus:ring-griot-terracotta border-gray-300 rounded"
            />
            <label for="remember" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
              Se souvenir de moi
            </label>
          </div>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-griot-terracotta hover:bg-griot-terracotta-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-griot-terracotta disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <span v-if="!form.processing">Se connecter</span>
          <span v-else>Connexion...</span>
        </button>
      </form>

      <div v-if="isDev" class="mt-6 p-4 bg-griot-blue-50 dark:bg-griot-blue-900/20 rounded-lg">
        <p class="text-center text-xs text-gray-600 dark:text-gray-400 mb-2">
          Identifiants par défaut
        </p>
        <div class="space-y-1 text-center">
          <p class="text-sm text-gray-700 dark:text-gray-300">
            <span class="font-medium">Email:</span> <code class="px-2 py-1 bg-white dark:bg-gray-800 rounded">admin@griot.local</code>
          </p>
          <p class="text-sm text-gray-700 dark:text-gray-300">
            <span class="font-medium">Mot de passe:</span> <code class="px-2 py-1 bg-white dark:bg-gray-800 rounded">password</code>
          </p>
        </div>
      </div>

      <div class="text-center">
        <p class="text-xs text-gray-500 dark:text-gray-400">
          Automatisation intelligente de vos communications SMS
        </p>
      </div>
    </div>
  </div>
</template>

