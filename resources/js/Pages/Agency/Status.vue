<script setup>
const props = defineProps({
  agency: Object
});
</script>

<template>
  <div class="max-w-xl mx-auto mt-16 p-8 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Agency Verification Status</h1>
    <div v-if="agency">
      <p>
        <strong>Status:</strong>
        <span :class="{
          'text-yellow-600': agency.verification_status === 'pending',
          'text-green-600': agency.verification_status === 'approved',
          'text-red-600': agency.verification_status === 'rejected'
        }">
          {{ agency.verification_status }}
        </span>
      </p>
      <p v-if="agency.admin_feedback" class="mt-2 text-gray-700">
        <strong>Admin Feedback:</strong> {{ agency.admin_feedback }}
      </p>
    </div>
    <div v-else>
      <p>No agency information found.</p>
    </div>
  </div>
</template> 