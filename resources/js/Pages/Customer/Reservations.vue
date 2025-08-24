<script setup>
import { ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import NavBar from '../../Components/NavBar.vue';
import Footer from '../../Components/Footer.vue';

const page = usePage();

const props = defineProps({
  reservations: Array
});

const reservations = ref(props.reservations || []);

const showReviewModal = ref(false);
const reviewReservation = ref(null);
const reviewRating = ref(0);
const reviewComment = ref('');
const reviewSubmitting = ref(false);
const reviewError = ref('');

// Status colors and labels
const getStatusConfig = (status) => {
  const configs = {
    'pending_payment': { color: 'text-yellow-600 bg-yellow-50', label: 'Pending Payment' },
    'active': { color: 'text-blue-600 bg-blue-50', label: 'Active' },
    'confirmed': { color: 'text-green-600 bg-green-50', label: 'Confirmed' },
    'completed': { color: 'text-green-800 bg-green-100', label: 'Completed' },
    'cancelled': { color: 'text-red-600 bg-red-50', label: 'Cancelled' },
  };
  return configs[status] || { color: 'text-gray-600 bg-gray-50', label: status };
};

// Get car image
const getCarImage = (car) => {
  if (car.images && car.images.length > 0) {
    const primaryImage = car.images.find(img => img.is_primary);
    return `/storage/${primaryImage?.image_path || car.images[0].image_path}`;
  }
  return '/images/car-placeholder.png';
};

// Format date
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

// Calculate days
const calculateDays = (startDate, endDate) => {
  const start = new Date(startDate);
  const end = new Date(endDate);
  const timeDiff = end - start;
  return Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1;
};

function openReviewModal(reservation) {
    reviewReservation.value = reservation;
    reviewRating.value = 0;
    reviewComment.value = '';
    reviewError.value = '';
    showReviewModal.value = true;
}

function closeReviewModal() {
    showReviewModal.value = false;
    reviewReservation.value = null;
    reviewRating.value = 0;
    reviewComment.value = '';
    reviewError.value = '';
}

async function submitReview() {
    if (!reviewRating.value) {
        reviewError.value = 'Please select a rating.';
        return;
    }
    reviewSubmitting.value = true;
    reviewError.value = '';
    try {
        const response = await fetch('/reviews', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                reservation_id: reviewReservation.value.id,
                rating: reviewRating.value,
                comment: reviewComment.value,
            }),
        });
        if (response.ok) {
            // Mark reservation as reviewed in UI
            reviewReservation.value.reviewed = true;
            closeReviewModal();
        } else {
            const data = await response.json();
            reviewError.value = data.message || 'Failed to submit review.';
        }
    } catch (e) {
        reviewError.value = 'Failed to submit review.';
    }
    reviewSubmitting.value = false;
}
</script>

<template>
  <Head title="My Reservations" />
  <div class="min-h-screen flex flex-col bg-gray-50">
    <NavBar :user="page.props.auth.user" />
    
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">My Reservations</h1>
        <p class="text-gray-600">Track your car rental bookings and history</p>
      </div>
      <div v-if="reservations.length" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div v-for="reservation in reservations" :key="reservation.id" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300">
          <!-- Header -->
          <div class="p-6 border-b border-gray-100">
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900 mb-1">
                  {{ reservation.car.make }} {{ reservation.car.model }}
                </h3>
                <p class="text-sm text-gray-600">{{ reservation.car.year }} • {{ reservation.car.license_plate }}</p>
              </div>
              <div class="ml-4">
                <span 
                  :class="`px-3 py-1 rounded-full text-xs font-medium ${getStatusConfig(reservation.status).color}`"
                >
                  {{ getStatusConfig(reservation.status).label }}
                </span>
              </div>
            </div>
            
            <!-- Car Image -->
            <div class="mb-4">
              <img 
                :src="getCarImage(reservation.car)" 
                :alt="`${reservation.car.make} ${reservation.car.model}`"
                class="w-full h-40 object-contain bg-gray-50 rounded-lg"
              />
            </div>
          </div>
          
          <!-- Details -->
          <div class="p-6">
            <!-- Dates & Duration -->
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <p class="text-xs text-gray-500 mb-1">Pick-up Date</p>
                <p class="font-semibold text-gray-900">{{ formatDate(reservation.start_date) }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 mb-1">Return Date</p>
                <p class="font-semibold text-gray-900">{{ formatDate(reservation.end_date) }}</p>
              </div>
            </div>
            
            <!-- Price & Duration -->
            <div class="grid grid-cols-2 gap-4 mb-6">
              <div>
                <p class="text-xs text-gray-500 mb-1">Duration</p>
                <p class="font-semibold text-gray-900">{{ calculateDays(reservation.start_date, reservation.end_date) }} day{{ calculateDays(reservation.start_date, reservation.end_date) > 1 ? 's' : '' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 mb-1">Total Price</p>
                <p class="font-semibold text-green-600 text-lg">${{ reservation.total_price }}</p>
              </div>
            </div>
            
            <!-- Agency Info -->
            <div class="mb-6">
              <p class="text-xs text-gray-500 mb-1">Rental Agency</p>
              <p class="font-medium text-gray-900">{{ reservation.car.agency.name }}</p>
            </div>
            
            <!-- Actions -->
            <div class="flex gap-3">
              <template v-if="reservation.status === 'completed'">
                <template v-if="reservation.reviewed">
                  <span class="flex-1 text-center py-2 bg-green-50 text-green-600 rounded-lg font-medium text-sm">
                    ✓ Reviewed
                  </span>
                </template>
                <template v-else>
                  <button 
                    @click="openReviewModal(reservation)" 
                    class="flex-1 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white py-2 rounded-lg font-medium hover:from-yellow-600 hover:to-yellow-700 transition-colors"
                  >
                    Leave Review
                  </button>
                </template>
              </template>
              <template v-else-if="reservation.status === 'pending_payment'">
                <Link 
                  :href="`/reservations/${reservation.id}/payment`" 
                  class="flex-1 text-center bg-gradient-to-r from-blue-600 to-blue-700 text-white py-2 rounded-lg font-medium hover:from-blue-700 hover:to-blue-800 transition-colors"
                >
                  Complete Payment
                </Link>
              </template>
              
              <Link 
                :href="`/cars/${reservation.car.id}`" 
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm"
              >
                View Car
              </Link>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-16">
        <div class="max-w-md mx-auto">
          <div class="text-6xl mb-4">🚗</div>
          <h2 class="text-2xl font-bold text-gray-400 mb-3">No reservations yet</h2>
          <p class="text-gray-500 mb-6">Start exploring our amazing car collection</p>
          <Link 
            href="/cars" 
            class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-xl font-semibold hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            Browse Cars
          </Link>
        </div>
      </div>
    </main>
    
    <Footer />

    <!-- Review Modal -->
    <Teleport to="body">
      <div v-if="showReviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full" @click.stop>
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Leave a Review</h3>
            <button 
              @click="closeReviewModal" 
              class="p-2 hover:bg-gray-100 rounded-full transition-colors"
            >
              <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <!-- Modal Content -->
          <div class="p-6">
            <!-- Rating Stars -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-3">Rating</label>
              <div class="flex items-center gap-1">
                <span 
                  v-for="star in 5" 
                  :key="star" 
                  @click="reviewRating = star" 
                  class="cursor-pointer hover:scale-110 transition-transform"
                >
                  <svg 
                    :class="reviewRating >= star ? 'text-yellow-400' : 'text-gray-300'" 
                    class="w-8 h-8" 
                    fill="currentColor" 
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/>
                  </svg>
                </span>
              </div>
            </div>
            
            <!-- Comment -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Comment (Optional)</label>
              <textarea 
                v-model="reviewComment" 
                rows="4" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                placeholder="Share your experience with this car rental..."
              ></textarea>
            </div>
            
            <!-- Error Message -->
            <div v-if="reviewError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-sm text-red-600">{{ reviewError }}</p>
            </div>
            
            <!-- Submit Button -->
            <button 
              @click="submitReview" 
              :disabled="reviewSubmitting || !reviewRating" 
              class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="reviewSubmitting" class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Submitting...
              </span>
              <span v-else>Submit Review</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template> 