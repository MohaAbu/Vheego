<script setup>
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();

if (page.props.auth && page.props.auth.user) {
  router.visit('/', { replace: true });
}
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-900 text-white">
    <h1 class="text-6xl font-bold mb-4">404</h1>
    <p class="text-2xl mb-8">Page Not Found</p>
    <a href="/" class="px-6 py-3 bg-blue-600 rounded-lg text-white font-semibold hover:bg-blue-700 transition">Go to Home</a>
  </div>
</template> 