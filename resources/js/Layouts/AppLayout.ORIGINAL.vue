<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useAuth } from '@/Composables/useAuth';
import { useI18n } from 'vue-i18n';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

const { t } = useI18n();

const sidebarOpen = ref(true);
const userMenuOpen = ref(false);

const page = usePage();
const { user, organization } = useAuth();
const currentRoute = page.url;

const navigation = computed(() => [
  { 
    name: t('nav.dashboard'), 
    href: '/dashboard', 
    icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    current: currentRoute === '/dashboard'
  },
  { 
    name: t('nav.cases'), 
    href: '/cases', 
    icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    current: currentRoute.startsWith('/cases')
  },
  { 
    name: t('nav.sms_queue'), 
    href: '/sms', 
    icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
    current: currentRoute.startsWith('/sms')
  },
  { 
    name: t('nav.templates'), 
    href: '/templates', 
    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    current: currentRoute.startsWith('/templates')
  },
  { 
    name: t('nav.rules'), 
    href: '/rules', 
    icon: 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4',
    current: currentRoute.startsWith('/rules')
  },
  { 
    name: t('nav.analytics'), 
    href: '/analytics', 
    icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    current: currentRoute.startsWith('/analytics')
  },
]);

const secondaryNavigation = computed(() => [
  { 
    name: t('nav.settings'), 
    href: '/settings', 
    icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    current: currentRoute.startsWith('/settings')
  },
  { 
    name: t('nav.support'), 
    href: '/support', 
    icon: 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z',
    current: currentRoute.startsWith('/support')
  },
]);
</script>

<template>
  <div class="min-h-screen bg-dark-50">
    
    <!-- Sidebar -->
    <aside 
      :class="[
        'fixed inset-y-0 left-0 z-50 flex w-64 flex-col transition-transform duration-300',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <!-- Sidebar Content -->
      <div class="flex flex-col flex-1 bg-white border-r border-dark-200">
        
        <!-- Logo -->
        <div class="flex items-center h-16 px-6 border-b border-dark-100">
          <Link href="/dashboard" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-accent-500 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
            </div>
            <span class="text-xl font-bold text-dark-900">S-Remind</span>
          </Link>
        </div>

        <!-- Organization -->
        <div class="px-6 py-4 border-b border-dark-100">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-dark-900 truncate">
                {{ user?.company_name || 'My Organization' }}
              </p>
              <p class="text-xs text-dark-500">Standard Plan</p>
            </div>
          </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
          <Link
            v-for="item in navigation"
            :key="item.name"
            :href="item.href"
            :class="[
              'flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors',
              item.current
                ? 'bg-primary-50 text-primary-700'
                : 'text-dark-700 hover:bg-dark-100 hover:text-dark-900'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
            </svg>
            {{ item.name }}
          </Link>
        </nav>

        <!-- Secondary Navigation -->
        <div class="px-3 py-4 border-t border-dark-100 space-y-1">
          <Link
            v-for="item in secondaryNavigation"
            :key="item.name"
            :href="item.href"
            :class="[
              'flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors',
              item.current
                ? 'bg-primary-50 text-primary-700'
                : 'text-dark-700 hover:bg-dark-100 hover:text-dark-900'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
            </svg>
            {{ item.name }}
          </Link>
        </div>

      </div>
    </aside>

    <!-- Main Content -->
    <div :class="['transition-all duration-300', sidebarOpen ? 'lg:pl-64' : '']">
      
      <!-- Top Header -->
      <header class="sticky top-0 z-40 bg-white border-b border-dark-200 shadow-sm">
        <div class="flex items-center justify-between h-16 px-6">
          
          <!-- Left: Toggle + Breadcrumb -->
          <div class="flex items-center space-x-4">
            <button
              @click="sidebarOpen = !sidebarOpen"
              class="p-2 text-dark-500 hover:text-dark-900 hover:bg-dark-100 rounded-lg transition-colors lg:hidden"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
            
            <slot name="breadcrumb" />
          </div>

          <!-- Right: Actions + User -->
          <div class="flex items-center space-x-3">
            
            <!-- Language Switcher -->
            <LanguageSwitcher />
            
            <!-- Sync Button -->
            <button class="p-2 text-dark-500 hover:text-dark-900 hover:bg-dark-100 rounded-lg transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>

            <!-- Notifications -->
            <button class="p-2 text-dark-500 hover:text-dark-900 hover:bg-dark-100 rounded-lg transition-colors relative">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- User Menu -->
            <div class="relative">
              <button
                @click="userMenuOpen = !userMenuOpen"
                class="flex items-center space-x-3 p-2 rounded-lg hover:bg-dark-100 transition-colors"
              >
                <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-accent-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                  {{ user?.name?.charAt(0) || 'U' }}
                </div>
                <div class="hidden md:block text-left">
                  <p class="text-sm font-medium text-dark-900">{{ user?.name || 'User' }}</p>
                  <p class="text-xs text-dark-500">{{ user?.email || 'user@example.com' }}</p>
                </div>
                <svg class="w-4 h-4 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Dropdown -->
              <div
                v-if="userMenuOpen"
                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-dark-200 py-1"
              >
                <Link href="/profile" class="block px-4 py-2 text-sm text-dark-700 hover:bg-dark-50">
                  {{ t('auth.profile') }}
                </Link>
                <Link href="/settings" class="block px-4 py-2 text-sm text-dark-700 hover:bg-dark-50">
                  {{ t('nav.settings') }}
                </Link>
                <div class="border-t border-dark-100 my-1"></div>
                <Link href="/logout" method="post" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                  {{ t('auth.logout') }}
                </Link>
              </div>
            </div>

          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="p-6">
        <slot />
      </main>

    </div>

  </div>
</template>
