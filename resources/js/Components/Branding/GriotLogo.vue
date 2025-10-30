<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

/**
 * Composant Logo GRIOT
 * Usage:
 * - <GriotLogo variant="full" /> (logo complet avec tagline)
 * - <GriotLogo variant="compact" /> (logo sans tagline)
 * - <GriotLogo variant="icon" /> (icon seul)
 * - <GriotLogo variant="white" /> (version blanche)
 * - <GriotLogo variant="black" /> (version noire)
 */

const props = defineProps({
  variant: {
    type: String,
    default: 'compact',
    validator: (value) => ['full', 'compact', 'icon', 'white', 'black'].includes(value)
  },
  height: {
    type: String,
    default: '40' // hauteur en pixels (sans unité)
  },
  href: {
    type: String,
    default: '/'
  },
  clickable: {
    type: Boolean,
    default: true
  },
  showTagline: {
    type: Boolean,
    default: false // Si true, force l'affichage de la tagline en dessous
  }
})

const logoPath = computed(() => {
  const paths = {
    full: '/images/branding/logo-griot-p1-full-tagline.png',
    compact: '/images/branding/logo-griot-p2-compact.png',
    icon: '/images/branding/logo-griot-icon.png',
    white: '/images/branding/logo-griot-white.png',
    black: '/images/branding/logo-griot-black.png'
  }
  return paths[props.variant] || paths.compact
})

const altText = computed(() => {
  return props.variant === 'full' 
    ? 'GRIOT - Your Digital Messenger'
    : 'GRIOT'
})

const heightStyle = computed(() => ({
  height: `${props.height}px`,
}))
</script>

<template>
  <div class="griot-logo-wrapper" :class="{ 'cursor-pointer': clickable }">
    <!-- Logo cliquable -->
    <Link 
      v-if="clickable" 
      :href="href"
      class="inline-flex flex-col items-center transition-opacity hover:opacity-80"
    >
      <img 
        :src="logoPath" 
        :alt="altText"
        :style="heightStyle"
        class="w-auto"
      />
      <span 
        v-if="showTagline && variant !== 'full'" 
        class="text-xs text-gray-600 mt-1 font-medium"
      >
        Your Digital Messenger
      </span>
    </Link>

    <!-- Logo non-cliquable -->
    <div v-else class="inline-flex flex-col items-center">
      <img 
        :src="logoPath" 
        :alt="altText"
        :style="heightStyle"
        class="w-auto"
      />
      <span 
        v-if="showTagline && variant !== 'full'" 
        class="text-xs text-gray-600 mt-1 font-medium"
      >
        Your Digital Messenger
      </span>
    </div>
  </div>
</template>

<style scoped>
/* Styles additionnels si nécessaire */
.griot-logo-wrapper img {
  object-fit: contain;
}

/* Animation subtile au hover */
.griot-logo-wrapper a:hover img {
  transform: scale(1.02);
  transition: transform 0.2s ease;
}
</style>
