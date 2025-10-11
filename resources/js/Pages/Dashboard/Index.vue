<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

const props = defineProps({
  stats: Object,
  smsActivity: Array,
  recentCases: Array,
  upcomingSms: Array,
  recentActivity: Array,
});

// Filtre période
const periodFilter = ref('7months'); // '7months', '3months', '1month'

const filteredSmsActivity = computed(() => {
  if (periodFilter.value === '3months') {
    return props.smsActivity.slice(-3);
  } else if (periodFilter.value === '1month') {
    return props.smsActivity.slice(-1);
  }
  return props.smsActivity; // 7 months
});

const chartData = computed(() => ({
  labels: filteredSmsActivity.value.map(item => item.month),
  datasets: [
    {
      label: 'SMS Sent',
      data: filteredSmsActivity.value.map(item => item.sent),
      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      fill: true,
      tension: 0.4,
    },
    {
      label: 'SMS Delivered',
      data: filteredSmsActivity.value.map(item => item.delivered),
      borderColor: '#10b981',
      backgroundColor: 'rgba(16, 185, 129, 0.1)',
      fill: true,
      tension: 0.4,
    },
  ],
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
    },
    tooltip: {
      mode: 'index',
      intersect: false,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        precision: 0,
      },
    },
  },
};
</script>

<template>
  <AppLayout>
    <Head title="Tableau de bord" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Tableau de bord
          </h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">
            Bienvenue ! Voici ce qui se passe avec vos campagnes SMS.
          </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Total Cases Card -->
          <a 
            href="/cases"
            class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ stats.totalCases.label }}
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats.totalCases.value.toLocaleString() }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ stats.totalCases.growth }}% du mois dernier
                </p>
              </div>
              <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
            </div>
          </a>

          <!-- SMS Actifs Card -->
          <a 
            href="/sms?status=sending"
            class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ stats.activeSms.label }}
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats.activeSms.value.toLocaleString() }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ stats.activeSms.sublabel }}
                </p>
              </div>
              <div class="p-3 bg-cyan-100 dark:bg-cyan-900 rounded-lg">
                <svg class="w-8 h-8 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
              </div>
            </div>
          </a>

          <!-- File d'attente Card -->
          <a 
            href="/sms?status=pending"
            class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ stats.queuedSms.label }}
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats.queuedSms.value.toLocaleString() }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ stats.queuedSms.sublabel }}
                </p>
              </div>
              <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </a>

          <!-- Success Rate Card -->
          <a 
            href="/analytics"
            class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ stats.successRate.label }}
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats.successRate.value }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ stats.successRate.sublabel }}
                </p>
              </div>
              <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </a>
        </div>

        <!-- SMS Activity Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              SMS Activity (Last {{ periodFilter === '7months' ? '7' : periodFilter === '3months' ? '3' : '1' }} month{{ periodFilter === '1month' ? '' : 's' }})
            </h2>
            
            <!-- Filtre Période -->
            <div class="flex gap-2">
              <button
                @click="periodFilter = '1month'"
                :class="[
                  'px-3 py-1 text-sm rounded-lg transition-colors',
                  periodFilter === '1month'
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                ]"
              >
                1M
              </button>
              <button
                @click="periodFilter = '3months'"
                :class="[
                  'px-3 py-1 text-sm rounded-lg transition-colors',
                  periodFilter === '3months'
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                ]"
              >
                3M
              </button>
              <button
                @click="periodFilter = '7months'"
                :class="[
                  'px-3 py-1 text-sm rounded-lg transition-colors',
                  periodFilter === '7months'
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                ]"
              >
                7M
              </button>
            </div>
          </div>
          
          <div style="height: 300px">
            <Line :data="chartData" :options="chartOptions" />
          </div>
        </div>

        <!-- Recent Cases & Upcoming SMS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Recent Cases -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Recent Cases
              </h2>
              <a href="/cases" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
                View all
              </a>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div v-for="case_ in recentCases" :key="case_.id" class="flex items-center justify-between">
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">
                      {{ case_.name }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      {{ case_.phone }}
                    </p>
                  </div>
                  <span class="px-2 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                    {{ case_.type }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Upcoming SMS -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Upcoming SMS
              </h2>
              <a href="/sms?status=pending" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
                View all
              </a>
            </div>
            <div class="p-6">
              <div v-if="upcomingSms.length === 0" class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">
                  Aucun SMS programmé pour les prochaines 24h
                </p>
              </div>
              <div v-else class="space-y-4">
                <div v-for="sms in upcomingSms" :key="sms.id" class="flex items-start justify-between">
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">
                      {{ sms.recipient }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      {{ sms.phone }}
                    </p>
                  </div>
                  <div class="text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      {{ sms.template }}
                    </p>
                    <p class="text-xs text-gray-600 dark:text-gray-300">
                      {{ sms.scheduled }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
