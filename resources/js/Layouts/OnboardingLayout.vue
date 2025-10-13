<script setup>
defineProps({
    currentStep: {
        type: Number,
        required: true
    },
    totalSteps: {
        type: Number,
        default: 5
    }
})

const steps = [
    { number: 1, name: 'Welcome' },
    { number: 2, name: 'Company' },
    { number: 3, name: 'CommCare' },
    { number: 4, name: 'Mapping' },
    { number: 5, name: 'Completion' }
]
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Progress bar -->
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <!-- Steps -->
                <div class="flex items-center justify-between">
                    <div
                        v-for="(step, index) in steps"
                        :key="step.number"
                        class="flex items-center"
                        :class="{ 'flex-1': index < steps.length - 1 }"
                    >
                        <!-- Step circle -->
                        <div class="flex items-center">
                            <div
                                class="flex items-center justify-center w-10 h-10 rounded-full transition-all"
                                :class="step.number <= currentStep
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-200 text-gray-600'"
                            >
                                <svg
                                    v-if="step.number < currentStep"
                                    class="w-6 h-6"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span v-else class="text-sm font-semibold">{{ step.number }}</span>
                            </div>
                            <span
                                class="ml-2 text-sm font-medium hidden sm:inline"
                                :class="step.number <= currentStep ? 'text-blue-600' : 'text-gray-500'"
                            >
                                {{ step.name }}
                            </span>
                        </div>

                        <!-- Connector line -->
                        <div
                            v-if="index < steps.length - 1"
                            class="flex-1 h-0.5 mx-4"
                            :class="step.number < currentStep ? 'bg-blue-600' : 'bg-gray-200'"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="py-12 px-4 sm:px-6 lg:px-8">
            <slot />
        </div>
    </div>
</template>
