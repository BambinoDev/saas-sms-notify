<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900">
    <Head title="Login" />

    <div class="max-w-md w-full space-y-8 p-8 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
      <div>
        <h2 class="text-center text-3xl font-bold text-gray-900 dark:text-white">
          S-Remind
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
          Plateforme SMS CPN Multi-Tenant
        </p>
      </div>

      <form @submit.prevent="submit" class="mt-8 space-y-6">
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
              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              placeholder="admin@notify-sms.local"
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
              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
              placeholder="••••••••"
            />
          </div>

          <div class="flex items-center">
            <input
              id="remember"
              v-model="form.remember"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="remember" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
              Se souvenir de moi
            </label>
          </div>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <span v-if="!form.processing">Se connecter</span>
          <span v-else>Connexion...</span>
        </button>
      </form>

      <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <p class="text-center text-xs text-gray-600 dark:text-gray-400 mb-2">
          Identifiants par défaut
        </p>
        <div class="space-y-1 text-center">
          <p class="text-sm text-gray-700 dark:text-gray-300">
            <span class="font-medium">Email:</span> <code class="px-2 py-1 bg-white dark:bg-gray-800 rounded">admin@notify-sms.local</code>
          </p>
          <p class="text-sm text-gray-700 dark:text-gray-300">
            <span class="font-medium">Mot de passe:</span> <code class="px-2 py-1 bg-white dark:bg-gray-800 rounded">password</code>
          </p>
        </div>
      </div>

      <div class="text-center">
        <p class="text-xs text-gray-500 dark:text-gray-400">
          Système de rappel SMS pour consultations prénatales
        </p>
      </div>
    </div>
  </div>
</template>

