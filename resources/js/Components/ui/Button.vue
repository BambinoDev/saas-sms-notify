<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'outline', 'ghost'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  href: String,
  as: String,
});

const classes = computed(() => {
  const base = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200';
  
  const variants = {
    primary: 'bg-dark-900 text-white hover:bg-dark-800 hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0',
    outline: 'border-2 border-dark-900 text-dark-900 hover:bg-dark-900 hover:text-white hover:shadow-lg hover:-translate-y-0.5',
    ghost: 'text-dark-900 hover:bg-dark-100'
  };
  
  const sizes = {
    sm: 'px-4 py-2 text-sm',
    md: 'px-6 py-3 text-base',
    lg: 'px-8 py-4 text-lg'
  };
  
  return `${base} ${variants[props.variant]} ${sizes[props.size]}`;
});
</script>

<template>
  <component 
    :is="as || (href ? 'a' : 'button')"
    :href="href"
    :class="classes"
  >
    <slot />
  </component>
</template>
