<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
  title: String,
});

const page = usePage();
const currentRoute = computed(() => page.url);
</script>

<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Navbar Admin (Rouge) -->
    <nav class="bg-red-600 shadow-lg">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center gap-8">
            <!-- Logo Admin -->
            <Link href="/admin" class="flex items-center gap-2 text-white font-bold text-xl">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              Administration
            </Link>

            <!-- Navigation -->
            <div class="hidden md:flex gap-4">
              <Link 
                href="/admin" 
                :class="[
                  'px-3 py-2 rounded-lg transition-colors',
                  currentRoute === '/admin' 
                    ? 'bg-red-700 text-white' 
                    : 'text-white/90 hover:text-white hover:bg-red-700/50'
                ]"
              >
                Dashboard
              </Link>
              <Link 
                href="/admin/organizations" 
                :class="[
                  'px-3 py-2 rounded-lg transition-colors',
                  currentRoute.startsWith('/admin/organizations') 
                    ? 'bg-red-700 text-white' 
                    : 'text-white/90 hover:text-white hover:bg-red-700/50'
                ]"
              >
                Organizations
              </Link>
            </div>
          </div>

          <!-- User menu -->
          <div class="flex items-center gap-4">
            <div class="text-white/90 text-sm hidden md:block">
              {{ $page.props.auth?.user?.name }}
            </div>
            <Link 
              href="/" 
              class="text-white/90 hover:text-white text-sm px-3 py-2 rounded-lg hover:bg-red-700/50 transition-colors"
            >
              ← Espace client
            </Link>
            <Link
              href="/admin/logout"
              method="post"
              as="button"
              class="text-white/90 hover:text-white text-sm px-3 py-2 rounded-lg hover:bg-red-700/50 transition-colors"
            >
              Déconnexion
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Content -->
    <main>
      <slot />
    </main>

    <!-- Footer Admin -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <p class="text-center text-sm text-gray-500 dark:text-gray-400">
          🔒 Espace Administration S-Remind · Multi-Tenant SAAS Platform
        </p>
      </div>
    </footer>
  </div>
</template>

