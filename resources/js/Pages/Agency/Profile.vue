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
  <div class="min-h-screen flex flex-col bg-gray-50">
    <NavBar :user="page.props.auth.user" />
    
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-4">
      <!-- Breadcrumb -->
      <div class="mb-2">
        <Breadcrumb :items="breadcrumbItems" />
      </div>

      <!-- Agency Header -->
      <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6 mb-6">
          <!-- Agency Logo -->
          <div class="relative">
            <img 
              :src="getAgencyLogo(props.agency)" 
              alt="Agency Logo" 
              class="w-24 h-24 rounded-full object-cover border-4 border-blue-100 shadow-lg" 
            />
            <!-- Verification Badge -->
            <div class="absolute -bottom-1 -right-1 bg-green-500 text-white p-2 rounded-full shadow-lg">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          
          <!-- Agency Info -->
          <div class="text-center md:text-left flex-1">
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-3">{{ props.agency.name }}</h1>
            
            <!-- Rating -->
            <div class="flex items-center justify-center md:justify-start gap-2 mb-4">
              <div class="flex items-center">
                <template v-for="(star, index) in renderStars(averageRating || 0)" :key="index">
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
                    class="w-5 h-5 text-gray-300" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"></path>
                  </svg>
                </template>
              </div>
              <span class="text-lg font-bold text-gray-700">{{ averageRating }}</span>
              <span v-if="averageRating > 0" class="text-gray-500">({{ reviews.length }} reviews)</span>
              <span v-else class="text-gray-400">No reviews yet</span>
            </div>
            
            <!-- Description -->
            <p class="text-gray-600 text-lg leading-relaxed mb-6">{{ props.agency.description }}</p>
            
            <!-- Contact Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center gap-3 text-gray-600">
                <div class="bg-blue-100 p-2 rounded-lg">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <div>
                  <div class="text-sm font-medium text-gray-500">Email</div>
                  <div class="font-semibold">{{ props.agency.contact_email }}</div>
                </div>
              </div>
              
              <div class="flex items-center gap-3 text-gray-600">
                <div class="bg-green-100 p-2 rounded-lg">
                  <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                  </svg>
                </div>
                <div>
                  <div class="text-sm font-medium text-gray-500">Phone</div>
                  <div class="font-semibold">{{ props.agency.contact_phone }}</div>
                </div>
              </div>
              
              <div class="flex items-center gap-3 text-gray-600 md:col-span-2">
                <div class="bg-purple-100 p-2 rounded-lg">
                  <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                </div>
                <div>
                  <div class="text-sm font-medium text-gray-500">Address</div>
                  <div class="font-semibold">{{ props.agency.address }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cars Section -->
      <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 19h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            Available Cars
            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm font-medium">{{ props.agency.cars?.length || 0 }}</span>
          </h2>
          
          <Link 
            :href="`/cars?agency_id=${props.agency.id}`" 
            class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
          >
            View All
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </Link>
        </div>
        
        <!-- Cars Grid -->
        <div v-if="props.agency.cars && props.agency.cars.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="car in props.agency.cars.slice(0, 6)" 
            :key="car.id" 
            class="group bg-gray-50 rounded-2xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden cursor-pointer border border-gray-100"
            @click="$inertia.visit(`/cars/${car.id}`)"
          >
            <!-- Car Image -->
            <div class="relative bg-gradient-to-br from-gray-100 to-gray-200 p-4">
              <img 
                :src="getCarImage(car)" 
                :alt="car.make + ' ' + car.model" 
                class="h-32 w-full object-contain group-hover:scale-105 transition-transform duration-300" 
              />
              
              <!-- Price Badge -->
              <div class="absolute top-3 right-3 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold shadow-lg">
                ${{ car.rental_price_per_day }}/day
              </div>
            </div>
            
            <!-- Car Details -->
            <div class="p-4">
              <h3 class="font-bold text-lg text-gray-900 mb-1 group-hover:text-blue-600 transition-colors">
                {{ car.make }} {{ car.model }}
              </h3>
              <div class="text-sm text-gray-600 mb-3">{{ car.year }}</div>
              
              <button class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-2 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                View Details
              </button>
            </div>
          </div>
        </div>
        
        <div v-else class="text-center py-12">
          <div class="text-4xl mb-4">🚗</div>
          <div class="text-xl font-semibold text-gray-400 mb-2">No cars available</div>
          <div class="text-gray-500">This agency hasn't listed any cars yet.</div>
        </div>
      </div>

      <!-- Reviews Section -->
      <div class="bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
          <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"></path>
          </svg>
          Customer Reviews
        </h2>
        
        <div v-if="loadingReviews" class="text-center py-8">
          <div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
          <div class="text-gray-500">Loading reviews...</div>
        </div>
        
        <div v-else-if="reviews.length === 0" class="text-center py-12">
          <div class="text-4xl mb-4">⭐</div>
          <div class="text-xl font-semibold text-gray-400 mb-2">No reviews yet</div>
          <div class="text-gray-500">Be the first to review this agency!</div>
        </div>
        
        <div v-else class="space-y-6">
          <div 
            v-for="review in reviews" 
            :key="review.id" 
            class="bg-gray-50 rounded-xl p-6 border border-gray-100"
          >
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold">
                  {{ (review.customer?.name || 'C').charAt(0).toUpperCase() }}
                </div>
                <div>
                  <div class="font-semibold text-gray-900">{{ review.customer?.name || 'Customer' }}</div>
                  <div class="flex items-center gap-1">
                    <template v-for="(star, index) in renderStars(review.rating)" :key="index">
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
                </div>
              </div>
              <div class="text-sm text-gray-500">
                {{ new Date(review.created_at).toLocaleDateString() }}
              </div>
            </div>
            <p class="text-gray-700 leading-relaxed">{{ review.comment || 'No comment provided.' }}</p>
          </div>
        </div>
      </div>
    </main>
    
    <Footer />
  </div>
</template> 