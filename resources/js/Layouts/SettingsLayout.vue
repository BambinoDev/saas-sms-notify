<template>
  <AppLayout :title="title">
    <div class="min-h-screen bg-gray-50">
      <div class="flex">
        <!-- Sidebar Desktop -->
        <aside class="hidden lg:flex lg:flex-shrink-0">
          <div class="flex flex-col w-64">
            <div class="flex flex-col flex-grow border-r border-gray-200 bg-white overflow-y-auto">
              <!-- Header -->
              <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Paramètres</h2>
                <p class="mt-1 text-sm text-gray-600">Configuration de l'organisation</p>
              </div>

              <!-- Navigation -->
              <nav class="flex-1 px-3 py-4 space-y-1">
                <!-- Général -->
                <Link
                  href="/settings/general"
                  :class="isActive('/settings/general')"
                  class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                >
                  <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  Général
                </Link>

                <!-- Synchronisation -->
                <Link
                  href="/settings/sync"
                  :class="isActive('/settings/sync')"
                  class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                >
                  <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  Synchronisation
                </Link>

                <!-- Configuration SMS -->
                <Link
                  href="/settings/sms"
                  :class="isActive('/settings/sms')"
                  class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                >
                  <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                  </svg>
                  Configuration SMS
                </Link>

                <!-- Mapping des champs -->
                <Link
                  href="/settings/mapping"
                  :class="isActive('/settings/mapping')"
                  class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                >
                  <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                  </svg>
                  Mapping des champs
                </Link>

                <!-- Paramètres d'envoi -->
                <Link
                  href="/settings/sending"
                  :class="isActive('/settings/sending')"
                  class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                >
                  <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Paramètres d'envoi
                </Link>

                <!-- Divider -->
                <div class="pt-4 pb-2">
                  <div class="h-px bg-gray-200"></div>
                </div>

                <!-- Limites & Quotas (Phase 2) -->
                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  À venir
                </div>

                <div class="px-3 py-2 text-sm text-gray-400 cursor-not-allowed flex items-center">
                  <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                  Limites & Quotas
                </div>

                <div class="px-3 py-2 text-sm text-gray-400 cursor-not-allowed flex items-center">
                  <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                  Notifications
                </div>
              </nav>

              <!-- Footer -->
              <div class="flex-shrink-0 border-t border-gray-200 p-4">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                      <span class="text-blue-600 font-semibold text-sm">
                        {{ organizationInitials }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">
                      {{ organizationName }}
                    </p>
                    <p class="text-xs text-gray-500">
                      {{ userRole }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
          <!-- Mobile header -->
          <div class="lg:hidden sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900">Paramètres</h2>
              <button
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100"
              >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Mobile menu (dropdown) -->
          <div
            v-if="mobileMenuOpen"
            class="lg:hidden fixed inset-0 z-50 bg-gray-600 bg-opacity-75"
            @click="mobileMenuOpen = false"
          >
            <div
              class="fixed inset-y-0 left-0 w-64 bg-white"
              @click.stop
            >
              <!-- Same navigation as desktop -->
              <div class="h-full flex flex-col">
                <div class="px-6 py-5 border-b border-gray-200">
                  <h2 class="text-lg font-semibold text-gray-900">Paramètres</h2>
                  <p class="mt-1 text-sm text-gray-600">Configuration</p>
                </div>
                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                  <Link
                    href="/settings/general"
                    :class="isActive('/settings/general')"
                    class="group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                    @click="mobileMenuOpen = false"
                  >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Général
                  </Link>
                  <Link
                    href="/settings/sync"
                    :class="isActive('/settings/sync')"
                    class="group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                    @click="mobileMenuOpen = false"
                  >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Synchronisation
                  </Link>
                  <Link
                    href="/settings/sms"
                    :class="isActive('/settings/sms')"
                    class="group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                    @click="mobileMenuOpen = false"
                  >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    Configuration SMS
                  </Link>
                  <Link
                    href="/settings/mapping"
                    :class="isActive('/settings/mapping')"
                    class="group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                    @click="mobileMenuOpen = false"
                  >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                    Mapping des champs
                  </Link>
                  <Link
                    href="/settings/sending"
                    :class="isActive('/settings/sending')"
                    class="group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                    @click="mobileMenuOpen = false"
                  >
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Paramètres d'envoi
                  </Link>
                </nav>
              </div>
            </div>
          </div>

          <!-- Page content -->
          <main class="flex-1 overflow-y-auto">
            <slot />
          </main>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  title: String,
});

const page = usePage();
const mobileMenuOpen = ref(false);

// Organisation info
const organizationName = computed(() => page.props.auth?.user?.organization?.name || 'Organisation');
const organizationInitials = computed(() => {
  const name = organizationName.value;
  return name.split(' ').map(word => word[0]).join('').substring(0, 2).toUpperCase();
});
const userRole = computed(() => page.props.auth?.user?.role || 'Utilisateur');

// Check if route is active
const isActive = (path) => {
  const currentPath = window.location.pathname;
  return currentPath === path
    ? 'bg-blue-50 text-blue-700'
    : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900';
};
</script>

