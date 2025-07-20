<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
</script>

<template>
  <DashboardLayout role="renter">
    <template #header>
      <h1 class="text-3xl font-bold text-blue-800">Renter Dashboard</h1>
    </template>
    <!-- Dashboard content here -->
    <slot />
  </DashboardLayout>
</template> 