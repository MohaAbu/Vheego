<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import NavBar from '../../Components/NavBar.vue';
import Footer from '../../Components/Footer.vue';
import Breadcrumb from '../../Components/Breadcrumb.vue';
import { computed } from 'vue';

const page = usePage();
const props = defineProps({
  agencies: Array,
});

const getAgencyLogo = (agency) => {
  if (agency.logo) {
    return `/storage/agency_logos/${agency.logo}`;
  }
  return '/images/agency-placeholder.png';
};

const renderStars = (rating) => {
  const stars = [];
  const numRating = parseFloat(rating || 0);
  const fullStars = Math.floor(numRating);
  const hasHalfStar = numRating % 1 >= 0.5;
  
  for (let i = 0; i < 5; i++) {
    if (i < fullStars) {
      stars.push('full');
    } else if (i === fullStars && hasHalfStar) {
      stars.push('half');
    } else {
      stars.push('empty');
    }
  }
  return stars;
};

const formatRating = (rating) => {
  return parseFloat(rating || 0).toFixed(1);
};

// Breadcrumb items
const breadcrumbItems = computed(() => [
  { label: 'Home', href: '/' },
  { label: 'Agencies' }
]);
</script>

<template>
  <Head title="Browse Agencies" />
  <div class="min-h-screen flex flex-col bg-gray-50">
    <NavBar :user="page.props.auth.user" />
    
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-4">
      <!-- Breadcrumb -->
      <div class="mb-2">
        <Breadcrumb :items="breadcrumbItems" />
      </div>

      <!-- Page Header -->
      <div class="text-center mb-6">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-800 mb-2">Browse Agencies</h1>
        <p class="text-base text-gray-600 max-w-xl mx-auto">
          Discover trusted car rental agencies with excellent service
        </p>
      </div>

      <!-- Agencies Grid -->
      <div v-if="agencies && agencies.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="agency in agencies" 
          :key="agency.id" 
          class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden cursor-pointer border border-gray-100"
          @click="$inertia.visit(`/agencies/${agency.id}`)"
        >
          <!-- Agency Header -->
          <div class="relative bg-gradient-to-br from-blue-50 to-indigo-50 p-6">
            <!-- Agency Logo -->
            <div class="flex justify-center mb-4">
              <div class="relative">
                <img 
                  :src="getAgencyLogo(agency)" 
                  :alt="agency.name + ' Logo'" 
                  class="w-20 h-20 object-cover rounded-full border-4 border-white shadow-lg group-hover:scale-110 transition-transform duration-300" 
                />
                <!-- Verification Badge -->
                <div class="absolute -bottom-1 -right-1 bg-green-500 text-white p-1.5 rounded-full shadow-lg">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                  </svg>
                </div>
              </div>
            </div>
            
            <!-- Agency Name -->
            <h3 class="text-xl font-bold text-gray-900 text-center mb-2 group-hover:text-blue-600 transition-colors">
              {{ agency.name }}
            </h3>

            <!-- Rating -->
            <div class="flex items-center justify-center gap-2 mb-3">
              <div class="flex items-center">
                <template v-for="(star, index) in renderStars(agency.rating)" :key="index">
                  <svg 
                    v-if="star === 'full'"
                    class="w-4 h-4 text-yellow-400" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"></path>
                  </svg>
                  <svg 
                    v-else
                    class="w-4 h-4 text-gray-300" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"></path>
                  </svg>
                </template>
              </div>
              <span class="text-sm font-semibold text-gray-700">{{ formatRating(agency.rating) }}</span>
            </div>
          </div>

          <!-- Agency Details -->
          <div class="p-6">
            <!-- Stats -->
            <div class="space-y-3 mb-4">
              <div class="flex items-center gap-3 text-gray-600">
                <div class="bg-blue-100 p-2 rounded-lg">
                  <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 19h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <div>
                  <div class="text-sm font-medium">Available Cars</div>
                  <div class="text-lg font-bold text-blue-600">{{ agency.cars_count || 0 }}</div>
                </div>
              </div>
              
              <div class="flex items-center gap-3 text-gray-600 min-w-0">
                <div class="bg-green-100 p-2 rounded-lg flex-shrink-0">
                  <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium">Location</div>
                  <div class="text-sm text-gray-700 truncate w-full" :title="agency.address || 'Not specified'">{{ agency.address || 'Not specified' }}</div>
                </div>
              </div>
            </div>

            <!-- View Profile Button -->
            <button class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-3 rounded-xl font-bold text-base hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
              View Agency Profile
            </button>
          </div>
        </div>
      </div>
      
      <!-- No Agencies Message -->
      <div v-else class="text-center py-16">
        <div class="max-w-md mx-auto">
          <div class="text-6xl mb-6">🏢</div>
          <div class="text-2xl font-bold text-gray-400 mb-4">
            No agencies found
          </div>
          <div class="text-gray-500 mb-6">
            We're working on bringing you the best car rental agencies. Check back soon!
          </div>
          <Link href="/" class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Back to Home
          </Link>
        </div>
      </div>
    </main>
    
    <Footer />
  </div>
</template> 