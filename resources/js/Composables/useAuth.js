import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useAuth() {
  const page = usePage();

  const user = computed(() => page.props.auth?.user || null);
  const organization = computed(() => user.value?.organization || null);
  const isAuthenticated = computed(() => !!user.value);

  return {
    user,
    organization,
    isAuthenticated,
  };
}


