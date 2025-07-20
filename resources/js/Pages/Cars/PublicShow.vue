<script setup>
import { Head, Link } from '@inertiajs/vue3';
import NavBar from '../../Components/NavBar.vue';
import Footer from '../../Components/Footer.vue';
import FavoriteButton from '../../Components/FavoriteButton.vue';
import Breadcrumb from '../../Components/Breadcrumb.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const page = usePage();
const props = defineProps({
  car: Object,
  user: Object
});

const reviews = ref([]);
const loadingReviews = ref(true);
const averageRating = ref(0);
const currentImageIndex = ref(0);

// Review form state
const showReviewForm = ref(false);
const reviewForm = ref({
  rating: 5,
  comment: ''
});
const submittingReview = ref(false);

// Related cars
const relatedCars = ref([]);
const loadingRelatedCars = ref(true);

// Computed property to check if user is authenticated
const isAuthenticated = computed(() => {
  return page.props.auth.user !== null;
});

// Computed property to check if user can book (is customer)
const canBook = computed(() => {
  return isAuthenticated.value && page.props.auth.user.user_type === 'customer';
});

// Computed property to get organized car images (primary first)
const carImages = computed(() => {
  if (!props.car.images || props.car.images.length === 0) {
    return ['/images/car-placeholder.png'];
  }
  
  const images = [...props.car.images];
  // Sort to put primary image first
  images.sort((a, b) => {
    if (a.is_primary && !b.is_primary) return -1;
    if (!a.is_primary && b.is_primary) return 1;
    return 0;
  });
  
  return images.map(img => `/storage/${img.image_path}`);
});

// Check if user can write reviews (customer who is logged in)
const canWriteReview = computed(() => {
  return isAuthenticated.value && page.props.auth.user.user_type === 'customer';
});

// Breadcrumb items
const breadcrumbItems = computed(() => [
  { label: 'Home', href: '/' },
  { label: 'Browse Cars', href: '/cars' },
  { label: `${props.car.make} ${props.car.model}` }
]);


const fetchReviews = async () => {
  loadingReviews.value = true;
  try {
    const response = await fetch(`/cars/${props.car.id}/reviews`);
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
  if (props.car && props.car.id) {
    fetchReviews();
    fetchRelatedCars();
  }
});

function renderStars(rating) {
  return Array.from({ length: 5 }, (_, i) => i < rating);
}

const getCarImage = (car) => {
  if (car.images && car.images.length > 0) {
    const primaryImage = car.images.find(img => img.is_primary);
    const imagePath = primaryImage ? primaryImage.image_path : car.images[0].image_path;
    return `/storage/${imagePath}`;
  }
  return '/images/car-placeholder.png';
};

// Helper for related car images
const getRelatedCarImage = (car) => {
  if (car.images && car.images.length > 0) {
    const primaryImage = car.images.find(img => img.is_primary);
    const imagePath = primaryImage ? primaryImage.image_path : car.images[0].image_path;
    return `/storage/${imagePath}`;
  }
  return '/images/car-placeholder.png';
};

const handleBookNow = () => {
  if (!isAuthenticated.value) {
    // Redirect to login with return URL
    window.location.href = `/login?redirect=${encodeURIComponent(window.location.pathname)}`;
  } else if (!canBook.value) {
    alert('Only customers can book cars. Please contact support if you need assistance.');
  } else {
    // Navigate to booking/reservation page
    window.location.href = `/reservations/create?car_id=${props.car.id}`;
  }
};

// Handle review form submission
const submitReview = async () => {
  if (!reviewForm.value.comment.trim()) {
    alert('Please write a review comment');
    return;
  }
  
  submittingReview.value = true;
  try {
    const response = await fetch('/reviews', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        car_id: props.car.id,
        rating: reviewForm.value.rating,
        comment: reviewForm.value.comment
      })
    });
    
    if (response.ok) {
      // Reset form and hide it
      reviewForm.value = { rating: 5, comment: '' };
      showReviewForm.value = false;
      // Refresh reviews
      await fetchReviews();
      alert('Review submitted successfully!');
    } else {
      alert('Failed to submit review. Please try again.');
    }
  } catch (error) {
    alert('Error submitting review. Please try again.');
  }
  submittingReview.value = false;
};

// Navigate to previous/next image
const previousImage = () => {
  currentImageIndex.value = currentImageIndex.value === 0 
    ? carImages.value.length - 1 
    : currentImageIndex.value - 1;
};

const nextImage = () => {
  currentImageIndex.value = currentImageIndex.value === carImages.value.length - 1 
    ? 0 
    : currentImageIndex.value + 1;
};

// Fetch related cars
const fetchRelatedCars = async () => {
  loadingRelatedCars.value = true;
  try {
    const response = await fetch(`/api/cars/related/${props.car.id}`);
    if (response.ok) {
      const data = await response.json();
      relatedCars.value = data.data || [];
    } else {
      relatedCars.value = [];
    }
  } catch (error) {
    relatedCars.value = [];
  }
  loadingRelatedCars.value = false;
};
</script>

<template>
  <Head :title="`${car.make} ${car.model} - ${car.year}`" />
  <div class="min-h-screen flex flex-col bg-gray-50">
    <NavBar :user="page.props.auth.user" />
    
    <main class="flex-1">
      <!-- Breadcrumb -->
      <div class="max-w-7xl mx-auto px-4 py-6">
        <Breadcrumb :items="breadcrumbItems" />
      </div>

      <!-- Car Details Section -->
      <div class="max-w-7xl mx-auto px-4 pb-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <!-- Car Images -->
          <div class="space-y-4">
            <!-- Main Image Display -->
            <div class="relative bg-gradient-to-br from-gray-50 to-gray-100 rounded-3xl p-8 shadow-lg">
              <img 
                :src="carImages[currentImageIndex]" 
                :alt="`${car.make} ${car.model}`"
                class="w-full h-80 object-contain"
              />
              
              <!-- Navigation Arrows (if multiple images) -->
              <template v-if="carImages.length > 1">
                <button 
                  @click="previousImage"
                  class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-2 rounded-full shadow-lg transition-all"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                  </svg>
                </button>
                <button 
                  @click="nextImage"
                  class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-2 rounded-full shadow-lg transition-all"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>
              </template>
              
              <!-- Image Counter -->
              <div v-if="carImages.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/50 text-white px-3 py-1 rounded-full text-sm">
                {{ currentImageIndex + 1 }} / {{ carImages.length }}
              </div>
              
              <!-- Category Badge -->
              <div class="absolute top-6 left-6 bg-blue-600 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg">
                {{ car.category }}
              </div>
              
              <!-- Favorite Button -->
              <div class="absolute top-6 right-6">
                <FavoriteButton 
                  v-if="isAuthenticated && page.props.auth.user.user_type === 'customer'" 
                  :car-id="car.id" 
                  :is-favorited="car.is_favorited" 
                />
              </div>
            </div>

            <!-- All Thumbnail Images -->
            <div v-if="carImages.length > 1" class="grid grid-cols-6 gap-2">
              <div 
                v-for="(image, index) in carImages" 
                :key="index"
                @click="currentImageIndex = index"
                :class="[
                  'bg-gray-100 rounded-xl p-2 hover:bg-gray-200 transition-colors cursor-pointer border-2',
                  currentImageIndex === index ? 'border-blue-500' : 'border-transparent'
                ]"
              >
                <img 
                  :src="image" 
                  :alt="`${car.make} ${car.model} - Image ${index + 1}`"
                  class="w-full h-12 object-contain rounded-lg"
                />
              </div>
            </div>
          </div>

          <!-- Car Information -->
          <div class="space-y-8">
            <!-- Header -->
            <div>
              <h1 class="text-4xl font-bold text-gray-900 mb-2">
                {{ car.make }} {{ car.model }}
              </h1>
              <div class="flex items-center gap-4 text-lg text-gray-600">
                <span>{{ car.year }}</span>
                <span>•</span>
                <span class="capitalize">{{ car.category }}</span>
              </div>
            </div>

            <!-- Price & Book Now -->
            <div class="bg-green-50 border-2 border-green-200 rounded-2xl p-6 space-y-4">
              <div class="text-center">
                <div class="text-3xl font-bold text-green-700 mb-2">
                  ${{ car.rental_price_per_day }}
                </div>
                <div class="text-green-600 font-medium">per day</div>
              </div>
              
              <!-- Book Now Button -->
              <div v-if="!isAuthenticated" class="space-y-3">
                <button 
                  @click="handleBookNow"
                  class="w-full bg-gradient-to-r from-green-600 to-green-700 text-white px-8 py-4 rounded-2xl font-bold text-lg hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                >
                  Book Now
                </button>
              </div>

              <div v-else-if="canBook" class="space-y-3">
                <button 
                  @click="handleBookNow"
                  class="w-full bg-gradient-to-r from-green-600 to-green-700 text-white px-8 py-4 rounded-2xl font-bold text-lg hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                >
                  Book Now
                </button>
              </div>

              <div v-else class="space-y-3">
                <div class="text-center text-gray-600 text-sm">
                  Only customers can book cars
                </div>
              </div>
            </div>

            <!-- Car Details -->
            <div class="bg-white rounded-2xl shadow-lg p-6 space-y-4">
              <h3 class="text-xl font-bold text-gray-800 mb-4">Car Details</h3>
              
              <div class="grid grid-cols-2 gap-4">
                <div class="flex items-center gap-3">
                  <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                  </svg>
                  <div>
                    <div class="text-sm text-gray-500">Fuel Type</div>
                    <div class="font-medium capitalize">{{ car.fuel_type }}</div>
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                  </svg>
                  <div>
                    <div class="text-sm text-gray-500">Transmission</div>
                    <div class="font-medium capitalize">{{ car.transmission }}</div>
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  <div>
                    <div class="text-sm text-gray-500">Seats</div>
                    <div class="font-medium">{{ car.seats }} people</div>
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                  </svg>
                  <div>
                    <div class="text-sm text-gray-500">License Plate</div>
                    <div class="font-medium">{{ car.license_plate }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Features -->
            <div v-if="car.features && (Array.isArray(car.features) ? car.features.length : car.features !== '')" class="bg-white rounded-2xl shadow-lg p-6">
              <h3 class="text-xl font-bold text-gray-800 mb-4">Features</h3>
              <div class="flex flex-wrap gap-2">
                <span 
                  v-for="feature in (Array.isArray(car.features) ? car.features : car.features.split(','))" 
                  :key="feature"
                  class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium"
                >
                  {{ feature.trim() }}
                </span>
              </div>
            </div>

            <!-- Agency Info -->
            <div v-if="car.agency" class="bg-white rounded-2xl shadow-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">Rental Agency</h3>
                <Link 
                  :href="`/agencies/${car.agency.id}`"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2 text-sm"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                  View Agency
                </Link>
              </div>
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                </div>
                <div>
                  <div class="font-bold text-gray-900">{{ car.agency.name }}</div>
                  <div class="text-gray-600 text-sm">{{ car.agency.address }}</div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Reviews Section -->
        <div class="mt-16 bg-white rounded-3xl shadow-lg p-8">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold flex items-center gap-3">
              <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/>
              </svg>
              Customer Reviews
              <span v-if="averageRating > 0" class="flex items-center gap-1 text-yellow-500">
                <span v-for="(filled, i) in renderStars(Math.round(averageRating))" :key="i">
                  <svg v-if="filled" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/>
                  </svg>
                  <svg v-else class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/>
                  </svg>
                </span>
                <span class="ml-2 text-gray-700 font-semibold">{{ averageRating }}</span>
              </span>
              <span v-else class="text-gray-400 text-base">No reviews yet</span>
            </h3>
            
            <!-- Write Review Button -->
            <button 
              v-if="canWriteReview && !showReviewForm"
              @click="showReviewForm = true"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Write Review
            </button>
          </div>

          <!-- Review Form -->
          <div v-if="showReviewForm" class="mb-8 bg-gray-50 rounded-2xl p-6">
            <h4 class="text-lg font-bold mb-4">Write Your Review</h4>
            
            <!-- Rating Stars -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
              <div class="flex items-center gap-1">
                <button 
                  v-for="star in 5" 
                  :key="star"
                  @click="reviewForm.rating = star"
                  class="p-1"
                >
                  <svg 
                    class="w-6 h-6 transition-colors"
                    :class="star <= reviewForm.rating ? 'text-yellow-500' : 'text-gray-300'"
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Comment -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
              <textarea 
                v-model="reviewForm.comment"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Share your experience with this car..."
              ></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center gap-3">
              <button 
                @click="submitReview"
                :disabled="submittingReview"
                class="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white px-6 py-2 rounded-lg font-medium transition-colors"
              >
                {{ submittingReview ? 'Submitting...' : 'Submit Review' }}
              </button>
              <button 
                @click="showReviewForm = false; reviewForm = { rating: 5, comment: '' }"
                class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg font-medium transition-colors"
              >
                Cancel
              </button>
            </div>
          </div>

          <div v-if="loadingReviews" class="text-gray-500 text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <div class="mt-2">Loading reviews...</div>
          </div>
          
          <div v-else-if="reviews.length === 0" class="text-center py-12">
            <div class="text-6xl mb-4">💭</div>
            <div class="text-xl text-gray-400">No reviews for this car yet</div>
            <div class="text-gray-500 mt-2">Be the first to share your experience!</div>
          </div>
          
          <div v-else class="space-y-6">
            <div v-for="review in reviews" :key="review.id" class="border-b border-gray-200 pb-6 last:border-b-0">
              <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                  {{ (review.customer?.name || 'Customer')[0].toUpperCase() }}
                </div>
                <div class="flex-1">
                  <div class="font-semibold text-gray-900">{{ review.customer?.name || 'Customer' }}</div>
                  <div class="flex items-center gap-2">
                    <span class="flex items-center gap-1 text-yellow-500">
                      <span v-for="(filled, i) in renderStars(review.rating)" :key="i">
                        <svg v-if="filled" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/>
                        </svg>
                        <svg v-else class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/>
                        </svg>
                      </span>
                    </span>
                    <span class="text-sm text-gray-400">{{ new Date(review.created_at).toLocaleDateString() }}</span>
                  </div>
                </div>
              </div>
              <div class="text-gray-700 leading-relaxed">
                <div v-if="review.comment && review.comment.trim()">{{ review.comment }}</div>
                <div v-else class="text-gray-400 italic text-sm">Customer rated this car but didn't leave a written review.</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Related Cars Section -->
        <div class="mt-16 bg-white rounded-3xl shadow-lg p-8">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold flex items-center gap-3">
              <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
              </svg>
              Related Cars
            </h3>
            
            <!-- View All Button -->
            <Link 
              :href="`/cars?agency_id=${car.agency_id}`"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2 text-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
              </svg>
              View All
            </Link>
          </div>

          <div v-if="loadingRelatedCars" class="text-gray-500 text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <div class="mt-2">Loading related cars...</div>
          </div>

          <div v-else-if="relatedCars.length === 0" class="text-center py-12">
            <div class="text-6xl mb-4">🚗</div>
            <div class="text-xl text-gray-400">No related cars available</div>
            <div class="text-gray-500 mt-2">Check out other cars from our fleet</div>
            <Link href="/cars" class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-lg mt-4">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
              </svg>
              Browse All Cars
            </Link>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div 
              v-for="relatedCar in relatedCars" 
              :key="relatedCar.id"
              class="group bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden cursor-pointer border border-gray-100"
              @click="$inertia.visit(`/cars/${relatedCar.id}`)"
            >
              <!-- Car Image -->
              <div class="relative bg-gradient-to-br from-gray-50 to-gray-100 p-4 rounded-t-2xl">
                <img 
                  :src="getRelatedCarImage(relatedCar)" 
                  :alt="`${relatedCar.make} ${relatedCar.model}`" 
                  class="h-32 w-full object-contain group-hover:scale-105 transition-transform duration-300"
                />
                
                <!-- Category Badge -->
                <div class="absolute top-2 left-2 bg-blue-600 text-white px-2 py-1 rounded-full text-xs font-bold">
                  {{ relatedCar.category }}
                </div>
                
                <!-- Price Badge -->
                <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                  ${{ relatedCar.rental_price_per_day }}/day
                </div>
              </div>

              <!-- Content Section -->
              <div class="p-4">
                <!-- Car Title -->
                <h4 class="font-bold text-lg text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                  {{ relatedCar.make }} {{ relatedCar.model }}
                </h4>
                
                <!-- Car Details -->
                <div class="space-y-1 mb-3">
                  <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-xs">{{ relatedCar.year }}</span>
                  </div>
                  
                  <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-xs">{{ relatedCar.agency?.name || 'N/A' }}</span>
                  </div>
                  
                  <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    <span class="text-xs">{{ relatedCar.transmission }} • {{ relatedCar.fuel_type }}</span>
                  </div>
                </div>

                <!-- View Details Button -->
                <button class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-2 rounded-xl font-medium text-sm hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                  View Details
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    
    <Footer />
  </div>
</template>