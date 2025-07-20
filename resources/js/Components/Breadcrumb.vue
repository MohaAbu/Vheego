<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  items: {
    type: Array,
    required: true,
    // Expected format: [{ label: 'Home', href: '/' }, { label: 'Cars', href: '/cars' }, { label: 'BMW 3 Series' }]
  }
});
</script>

<template>
  <nav class="flex items-center space-x-2 text-sm text-gray-600" aria-label="Breadcrumb">
    <div class="flex items-center space-x-2">
      <template v-for="(item, index) in items" :key="index">
        <!-- Separator -->
        <svg 
          v-if="index > 0" 
          class="w-4 h-4 text-gray-400" 
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        
        <!-- Breadcrumb Item -->
        <div class="flex items-center">
          <!-- Clickable link -->
          <Link 
            v-if="item.href && index < items.length - 1"
            :href="item.href"
            class="text-gray-500 hover:text-blue-600 transition-colors duration-200 font-medium"
          >
            {{ item.label }}
          </Link>
          
          <!-- Current page (non-clickable) -->
          <span 
            v-else
            class="text-gray-900 font-semibold"
            :class="{ 'text-gray-900 font-semibold': index === items.length - 1 }"
          >
            {{ item.label }}
          </span>
        </div>
      </template>
    </div>
  </nav>
</template>

<style scoped>
/* Additional hover effects */
.breadcrumb-link:hover {
  text-decoration: underline;
}
</style>