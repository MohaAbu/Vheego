<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <Head title="About Us" />
  <div class="py-16 min-h-screen bg-gray-50">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-10">
      <h1 class="text-4xl font-bold mb-4 text-center text-blue-700">About Vheego</h1>
      <p class="mb-4 text-lg text-gray-700 text-center">
        <strong>Vheego</strong> is a full-stack car rental system built as a school project to demonstrate comprehensive web development skills. The platform supports customers, renters, and administrators, providing a modern, intuitive interface for car rentals, agency management, and system oversight.
      </p>
      <div class="mb-4">
        <h2 class="text-2xl font-semibold mb-2 text-blue-600">Project Purpose</h2>
        <p class="text-gray-700">
          This project showcases proficiency in Laravel, Vite, and modern web development practices. It demonstrates clean architecture, proper database design, and role-based access control, fulfilling academic requirements for a professional, maintainable MVP.
        </p>
      </div>
      <div class="mb-4">
        <h2 class="text-2xl font-semibold mb-2 text-blue-600">Technology Stack</h2>
        <ul class="list-disc list-inside text-gray-700">
          <li>Backend: Laravel 10+</li>
          <li>Frontend: Vite, Vue.js, Inertia.js</li>
          <li>Database: PostgreSQL</li>
          <li>Authentication: Laravel Breeze</li>
          <li>Styling: Tailwind CSS</li>
        </ul>
      </div>
      <div>
        <h2 class="text-2xl font-semibold mb-2 text-blue-600">Academic Focus</h2>
        <p class="text-gray-700">
          Vheego is designed to impress academically by adhering to best practices, clean code standards, and a well-structured, scalable architecture. The project is a demonstration of full-stack proficiency and professional project delivery.
        </p>
      </div>
    </div>
  </div>
</template> 