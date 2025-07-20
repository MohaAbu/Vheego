<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';

const name = ref('');
const email = ref('');
const message = ref('');
const errors = ref({});
const submitted = ref(false);

function validate() {
  errors.value = {};
  if (!name.value) errors.value.name = 'Name is required.';
  if (!email.value || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email.value)) errors.value.email = 'Valid email is required.';
  if (!message.value) errors.value.message = 'Message is required.';
  return Object.keys(errors.value).length === 0;
}

function submitForm() {
  if (validate()) {
    submitted.value = true;
    // Placeholder: In a real app, send the message to backend/email
  }
}
</script>

<template>
  <Head title="Contact Us" />
  <div class="py-16 min-h-screen bg-gray-50">
    <div class="max-w-lg mx-auto bg-white rounded-xl shadow-lg p-10">
      <h1 class="text-4xl font-bold mb-6 text-center text-blue-700">Contact Us</h1>
      <p class="mb-6 text-gray-700 text-center">
        Have questions, feedback, or need support? Fill out the form below or reach us at <a href="mailto:info@vheego.com" class="text-blue-600 underline">info@vheego.com</a>.
      </p>
      <form @submit.prevent="submitForm" v-if="!submitted">
        <div class="mb-4">
          <label class="block font-semibold mb-1">Name</label>
          <input v-model="name" type="text" class="w-full border rounded px-3 py-2" />
          <div v-if="errors.name" class="text-red-600 text-xs mt-1">{{ errors.name }}</div>
        </div>
        <div class="mb-4">
          <label class="block font-semibold mb-1">Email</label>
          <input v-model="email" type="email" class="w-full border rounded px-3 py-2" />
          <div v-if="errors.email" class="text-red-600 text-xs mt-1">{{ errors.email }}</div>
        </div>
        <div class="mb-4">
          <label class="block font-semibold mb-1">Message</label>
          <textarea v-model="message" rows="4" class="w-full border rounded px-3 py-2"></textarea>
          <div v-if="errors.message" class="text-red-600 text-xs mt-1">{{ errors.message }}</div>
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-semibold">Send Message</button>
      </form>
      <div v-else class="text-center text-green-600 text-lg font-semibold mt-6">
        Thank you for reaching out! We'll get back to you soon.
      </div>
      <div class="mt-8 text-center text-gray-500 text-sm">
        Vheego Car Rental System &copy; 2025. For academic demonstration only.
      </div>
    </div>
  </div>
</template> 