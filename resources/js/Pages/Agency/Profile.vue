<script setup>
import { ref, onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import NavBar from '../../Components/NavBar.vue';
import Footer from '../../Components/Footer.vue';
import Breadcrumb from '../../Components/Breadcrumb.vue';

const page = usePage();
const props = defineProps({
  agency: Object,
});

const reviews = ref([]);
const loadingReviews = ref(true);
const averageRating = ref(0);

const fetchReviews = async () => {
  loadingReviews.value = true;
  try {
    const response = await fetch(`/agencies/${props.agency.id}/reviews`);
    if (response.ok) {
      const data = await response.json();
      reviews.value = data;
      if (data.length) {
        averageRating.value = (
          data.reduce((sum, r) => sum + r.rating, 0) / data.length
        ).toFixed(1);
      } else {
        averageRating.value = 0;
      }
    } else {
      reviews.value = [];
      averageRating.value = 0;
    }
  } catch (e) {
    reviews.value = [];
    averageRating.value = 0;
  }
  loadingReviews.value = false;
};

onMounted(() => {
  if (props.agency && props.agency.id) fetchReviews();
});

const renderStars = (rating) => {
  const stars = [];
  const fullStars = Math.floor(rating);
  const hasHalfStar = rating % 1 >= 0.5;
  
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

const getCarImage = (car) => {
  if (car.images && car.images.length > 0) {
    const primaryImage = car.images.find(img => img.is_primary);
    const imagePath = primaryImage ? primaryImage.image_path : car.images[0].image_path;
    return `/storage/${imagePath}`;
  }
  return '/images/car-placeholder.png';
};

const getAgencyLogo = (agency) => {
  if (agency.logo) {
    return agency.logo.startsWith('agency_logos/') ? `/storage/${agency.logo}` : `/storage/agency_logos/${agency.logo}`;
  }
  return '/images/agency-placeholder.png';
};

// Breadcrumb items
const breadcrumbItems = computed(() => [
  { label: 'Home', href: '/' },
  { label: 'Agencies', href: '/agencies' },
  { label: props.agency?.name || 'Agency Profile' }
]);
</script>

<template>
  <Head :title="props.agency?.name + ' - Agency Profile' || 'Agency Profile'" />
  <div class="min-h-screen flex flex-col">
    <!-- Background Elements -->
    <div class="fixed inset-0 z-0 bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/30"></div>
    <div class="fixed inset-0 z-0 opacity-40" style="background-image: radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.1) 0%, transparent 50%), radial-gradient(circle at 75% 75%, rgba(16, 185, 129, 0.1) 0%, transparent 50%)"></div>
    
    <NavBar :user="page.props.auth.user" />
    
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-8 relative z-10">
      <!-- Breadcrumb -->
      <div class="mb-6">
        <Breadcrumb :items="breadcrumbItems" />
      </div>

      <!-- Agency Header -->
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-8 mb-8">
        <div class="flex flex-col lg:flex-row items-center lg:items-start gap-8">
          <!-- Agency Logo Section -->
          <div class="relative flex-shrink-0">
            <div class="relative">
              <img 
                :src="getAgencyLogo(props.agency)" 
                alt="Agency Logo" 
                class="w-32 h-32 rounded-2xl object-cover border-4 border-white shadow-2xl ring-4 ring-blue-100/50" 
              />
              <!-- Verification Badge -->
              <div class="absolute -bottom-2 -right-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white p-3 rounded-2xl shadow-xl ring-4 ring-white">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
              </div>
            </div>
            <div class="text-center mt-4">
              <div class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Verified Agency
              </div>
            </div>
          </div>
          
          <!-- Agency Info -->
          <div class="text-center lg:text-left flex-1">
            <h1 class="text-4xl lg:text-5xl font-bold bg-gradient-to-r from-slate-800 to-blue-800 bg-clip-text text-transparent mb-4">
              {{ props.agency.name }}
            </h1>
            
            <!-- Rating -->
            <div class="flex items-center justify-center lg:justify-start gap-3 mb-6">
              <div class="flex items-center gap-1">
                <template v-for="(star, index) in renderStars(averageRating || 0)" :key="index">
                  <svg 
                    v-if="star === 'full'"
                    class="w-6 h-6 text-yellow-400 drop-shadow-sm" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"></path>
                  </svg>
                  <svg 
                    v-else
                    class="w-6 h-6 text-slate-300" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"></path>
                  </svg>
                </template>
              </div>
              <div class="text-xl font-bold text-slate-700">{{ averageRating || '0.0' }}</div>
              <div class="text-slate-500">
                <span v-if="averageRating > 0">({{ reviews.length }} {{ reviews.length === 1 ? 'review' : 'reviews' }})</span>
                <span v-else>No reviews yet</span>
              </div>
            </div>
            
            <!-- Description -->
            <p class="text-slate-600 text-lg leading-relaxed mb-8 max-w-3xl">{{ props.agency.description }}</p>
            
            <!-- Contact Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
              <div class="flex items-center gap-4 p-4 bg-blue-50/50 rounded-xl border border-blue-100/50">
                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Email</div>
                  <div class="font-semibold text-slate-800 truncate">{{ props.agency.contact_email }}</div>
                </div>
              </div>
              
              <div class="flex items-center gap-4 p-4 bg-green-50/50 rounded-xl border border-green-100/50">
                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                  </svg>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="text-sm font-semibold text-green-600 uppercase tracking-wide">Phone</div>
                  <div class="font-semibold text-slate-800">{{ props.agency.contact_phone }}</div>
                </div>
              </div>
              
              <div class="flex items-center gap-4 p-4 bg-purple-50/50 rounded-xl border border-purple-100/50 md:col-span-2 xl:col-span-1">
                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="text-sm font-semibold text-purple-600 uppercase tracking-wide">Location</div>
                  <div class="font-semibold text-slate-800">{{ props.agency.address }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cars Section -->
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-8 mb-8">
        <div class="flex items-center justify-between mb-8">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 19h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-3xl font-bold text-slate-800">Available Fleet</h2>
              <p class="text-slate-600">Browse our collection of premium vehicles</p>
            </div>
            <div class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-sm font-bold rounded-full">
              {{ props.agency.cars?.length || 0 }} vehicles
            </div>
          </div>
          
          <Link 
            :href="`/cars?agency_id=${props.agency.id}`" 
            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
          >
            View All Cars
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>
          </Link>
        </div>
        
        <!-- Cars Grid -->
        <div v-if="props.agency.cars && props.agency.cars.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="car in props.agency.cars.slice(0, 6)" 
            :key="car.id" 
            class="group bg-white/70 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden cursor-pointer border border-white/40 hover:border-blue-200/50 transform hover:-translate-y-1"
            @click="$inertia.visit(`/cars/${car.id}`)"
          >
            <!-- Car Image -->
            <div class="relative bg-gradient-to-br from-slate-100 to-slate-200 p-6">
              <img 
                :src="getCarImage(car)" 
                :alt="car.make + ' ' + car.model" 
                class="h-40 w-full object-contain group-hover:scale-110 transition-all duration-500" 
              />
              
              <!-- Price Badge -->
              <div class="absolute top-4 right-4 bg-gradient-to-r from-green-500 to-emerald-500 text-white px-3 py-2 rounded-xl text-sm font-bold shadow-lg ring-2 ring-white/20">
                ${{ car.rental_price_per_day }}/day
              </div>
              
              <!-- Special Offer Badge -->
              <div v-if="car.special_offer" class="absolute top-4 left-4 bg-gradient-to-r from-orange-500 to-red-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg animate-pulse">
                Special Offer
              </div>
            </div>
            
            <!-- Car Details -->
            <div class="p-6">
              <h3 class="font-bold text-xl text-slate-800 mb-2 group-hover:text-blue-600 transition-colors">
                {{ car.make }} {{ car.model }}
              </h3>
              <div class="flex items-center gap-4 text-sm text-slate-600 mb-4">
                <div class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                  </svg>
                  {{ car.year }}
                </div>
                <div class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  {{ car.seats }} seats
                </div>
                <div class="flex items-center gap-1 capitalize">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                  </svg>
                  {{ car.fuel_type }}
                </div>
              </div>
              
              <button class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-3 rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                View Details
              </button>
            </div>
          </div>
        </div>
        
        <div v-else class="text-center py-16">
          <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 19h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-slate-400 mb-2">No Vehicles Available</h3>
          <p class="text-slate-500 max-w-md mx-auto">This agency is currently building their fleet. Check back soon for exciting vehicle options!</p>
        </div>
      </div>

      <!-- Reviews Section -->
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"></path>
            </svg>
          </div>
          <div>
            <h2 class="text-3xl font-bold text-slate-800">Customer Reviews</h2>
            <p class="text-slate-600">See what our customers have to say</p>
          </div>
        </div>
        
        <div v-if="loadingReviews" class="text-center py-12">
          <div class="inline-flex items-center gap-3 px-6 py-3 bg-blue-50 rounded-xl">
            <div class="animate-spin w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full"></div>
            <div class="text-blue-600 font-semibold">Loading reviews...</div>
          </div>
        </div>
        
        <div v-else-if="reviews.length === 0" class="text-center py-16">
          <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"></path>
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-slate-400 mb-2">No Reviews Yet</h3>
          <p class="text-slate-500 max-w-md mx-auto">Be the first to share your experience with this agency! Your feedback helps other customers make informed decisions.</p>
        </div>
        
        <div v-else class="space-y-6">
          <div 
            v-for="review in reviews" 
            :key="review.id" 
            class="bg-slate-50/70 backdrop-blur-sm rounded-2xl p-6 border border-slate-200/50 hover:shadow-lg transition-all duration-300"
          >
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-xl flex items-center justify-center font-bold text-lg shadow-lg">
                  {{ (review.customer?.name || 'C').charAt(0).toUpperCase() }}
                </div>
                <div>
                  <div class="font-bold text-slate-800 text-lg">{{ review.customer?.name || 'Customer' }}</div>
                  <div class="flex items-center gap-2">
                    <div class="flex items-center">
                      <template v-for="(star, index) in renderStars(review.rating)" :key="index">
                        <svg 
                          v-if="star === 'full'"
                          class="w-5 h-5 text-yellow-400" 
                          fill="currentColor" 
                          viewBox="0 0 20 20"
                        >
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"></path>
                        </svg>
                        <svg 
                          v-else
                          class="w-5 h-5 text-slate-300" 
                          fill="currentColor" 
                          viewBox="0 0 20 20"
                        >
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"></path>
                        </svg>
                      </template>
                    </div>
                    <span class="text-sm font-semibold text-slate-600">{{ review.rating }}/5</span>
                  </div>
                </div>
              </div>
              <div class="text-sm text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                {{ new Date(review.created_at).toLocaleDateString() }}
              </div>
            </div>
            <p class="text-slate-700 leading-relaxed text-lg">{{ review.comment || 'No comment provided.' }}</p>
          </div>
        </div>
      </div>
    </main>
    
    <Footer />
  </div>
</template> 