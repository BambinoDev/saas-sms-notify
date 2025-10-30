<script setup>
import { Link } from '@inertiajs/vue3'
import GriotLogo from './GriotLogo.vue'

/**
 * Composant Hero Section GRIOT
 * Usage: <GriotHero />
 * 
 * Props optionnelles pour personnalisation
 */

const props = defineProps({
  title: {
    type: String,
    default: 'Transform Your Field Data Into Timely SMS'
  },
  subtitle: {
    type: String,
    default: 'Like the traditional griot who carried messages across villages, GRIOT ensures your critical communications reach the right people at the right time.'
  },
  showLogo: {
    type: Boolean,
    default: true
  },
  primaryCtaText: {
    type: String,
    default: 'Get Started'
  },
  primaryCtaHref: {
    type: String,
    default: '/register'
  },
  secondaryCtaText: {
    type: String,
    default: 'Learn More'
  },
  secondaryCtaHref: {
    type: String,
    default: '/about'
  },
  showCta: {
    type: Boolean,
    default: true
  }
})
</script>

<template>
  <div class="griot-hero min-h-screen bg-gradient-to-br from-[#1A4D7C] via-[#153E63] to-[#0d2a45] relative overflow-hidden">
    <!-- Pattern Background (optionnel) -->
    <div class="absolute inset-0 opacity-5">
      <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
          <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
        </pattern>
        <rect width="100%" height="100%" fill="url(#grid)" />
      </svg>
    </div>

    <!-- Content -->
    <div class="relative z-10 flex flex-col items-center justify-center min-h-screen px-4 py-16">
      <!-- Logo GRIOT P1 -->
      <div v-if="showLogo" class="mb-8 md:mb-12 animate-fade-in">
        <GriotLogo 
          variant="full" 
          height="120"
          :clickable="false"
        />
      </div>
      
      <!-- Headline -->
      <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-display font-bold text-white text-center mb-6 max-w-4xl leading-tight animate-slide-up">
        {{ title }}
      </h1>
      
      <!-- Subheadline -->
      <p class="text-lg sm:text-xl md:text-2xl text-white/90 text-center max-w-3xl mb-10 leading-relaxed animate-slide-up animation-delay-100">
        {{ subtitle }}
      </p>
      
      <!-- CTA Buttons -->
      <div v-if="showCta" class="flex flex-col sm:flex-row gap-4 animate-slide-up animation-delay-200">
        <Link
          :href="primaryCtaHref"
          class="bg-[#C85A3C] hover:bg-[#B34F33] text-white font-medium px-8 py-4 rounded-lg text-base md:text-lg transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5 text-center"
        >
          {{ primaryCtaText }}
        </Link>
        <Link
          :href="secondaryCtaHref"
          class="bg-white/10 hover:bg-white/20 text-white font-medium px-8 py-4 rounded-lg text-base md:text-lg border-2 border-white/30 transition-all duration-200 backdrop-blur-sm text-center"
        >
          {{ secondaryCtaText }}
        </Link>
      </div>

      <!-- Trust Badge (optionnel) -->
      <div class="mt-12 md:mt-16 text-white/70 text-sm text-center animate-fade-in animation-delay-300">
        <p class="flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
          Built in Abidjan 🇨🇮 | Trusted by Organizations Worldwide 🌍
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fadeIn 0.8s ease-out;
}

.animate-slide-up {
  animation: slideUp 0.6s ease-out;
}

.animation-delay-100 {
  animation-delay: 0.1s;
  opacity: 0;
  animation-fill-mode: forwards;
}

.animation-delay-200 {
  animation-delay: 0.2s;
  opacity: 0;
  animation-fill-mode: forwards;
}

.animation-delay-300 {
  animation-delay: 0.3s;
  opacity: 0;
  animation-fill-mode: forwards;
}

/* Responsive font-display fallback */
@supports not (font-family: 'Poppins') {
  .font-display {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
  }
}
</style>
