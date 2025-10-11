<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  password: '',
});

const submit = () => {
  form.post('/admin/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
    <Head title="Admin Login" />

    <div class="max-w-md w-full space-y-8 p-8 bg-gray-800 rounded-lg shadow-2xl border border-gray-700">
      <!-- Header avec icône admin -->
      <div class="text-center">
        <div class="mx-auto w-16 h-16 bg-red-600 rounded-full flex items-center justify-center mb-4">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
        <h2 class="text-3xl font-bold text-white">
          Administration
        </h2>
        <p class="mt-2 text-sm text-gray-400">
          Accès réservé aux administrateurs de la plateforme
        </p>
      </div>

      <!-- Formulaire -->
      <form @submit.prevent="submit" class="mt-8 space-y-6">
        <div class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-300">
              Email administrateur
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-white placeholder-gray-400"
              placeholder="admin@notify-sms.local"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-400">
              {{ form.errors.email }}
            </p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-300">
              Mot de passe
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              autocomplete="current-password"
              class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-white placeholder-gray-400"
            />
          </div>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <span v-if="!form.processing">Accéder à l'administration</span>
          <span v-else class="flex items-center gap-2">
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Connexion...
          </span>
        </button>
      </form>

      <!-- Info dev -->
      <div class="mt-6 p-4 bg-gray-700 rounded-lg border border-gray-600">
        <p class="text-center text-xs text-gray-400 mb-2">
          🔒 Accès sécurisé administrateur
        </p>
        <div class="text-center space-y-1">
          <p class="text-xs text-gray-300">
            <span class="text-gray-400">Email:</span> <code class="px-2 py-1 bg-gray-800 rounded">admin@notify-sms.local</code>
          </p>
          <p class="text-xs text-gray-300">
            <span class="text-gray-400">Password:</span> <code class="px-2 py-1 bg-gray-800 rounded">password</code>
          </p>
        </div>
      </div>

      <!-- Lien retour -->
      <div class="text-center">
        <a href="/login" class="text-sm text-gray-400 hover:text-white transition-colors">
          ← Retour à l'espace client
        </a>
      </div>
    </div>
  </div>
</template>

