<script setup>
import { ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import NavBar from '../../Components/NavBar.vue';
import Footer from '../../Components/Footer.vue';

const page = usePage();

const props = defineProps({
  reservation: Object
});

const paymentForm = ref({
  card_number: '',
  expiry: '',
  cvv: '',
  card_name: ''
});

const processing = ref(false);
const errors = ref({});

// Format card number with spaces
const formatCardNumber = () => {
  let value = paymentForm.value.card_number.replace(/\s/g, '').replace(/[^0-9]/gi, '');
  const formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
  if (formattedValue.length <= 19) {
    paymentForm.value.card_number = formattedValue;
  }
};

// Format expiry date MM/YY
const formatExpiry = () => {
  let value = paymentForm.value.expiry.replace(/\D/g, '');
  if (value.length >= 2) {
    value = value.substring(0, 2) + '/' + value.substring(2, 4);
  }
  paymentForm.value.expiry = value;
};

// Format CVV (3-4 digits only)
const formatCVV = () => {
  paymentForm.value.cvv = paymentForm.value.cvv.replace(/\D/g, '').substring(0, 4);
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
    month: 'long',
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

// Submit payment
const submitPayment = () => {
  processing.value = true;
  errors.value = {};
  
  router.post(`/reservations/${props.reservation.id}/payment`, paymentForm.value, {
    onSuccess: () => {
      // Redirect handled by controller
    },
    onError: (formErrors) => {
      errors.value = formErrors;
      processing.value = false;
    },
    onFinish: () => {
      processing.value = false;
    }
  });
};
</script>

<template>
  <Head title="Complete Payment" />
  <div class="min-h-screen flex flex-col bg-gray-50">
    <NavBar :user="page.props.auth.user" />
    
    <main class="flex-1 w-full max-w-4xl mx-auto px-4 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Complete Payment</h1>
        <p class="text-gray-600">Secure payment for your car rental</p>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Payment Form -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
          <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Payment Details</h2>
            <p class="text-sm text-gray-600">All transactions are secure and encrypted</p>
          </div>
          
          <form @submit.prevent="submitPayment" class="space-y-6">
            <!-- Card Number -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Card Number</label>
              <input 
                type="text" 
                v-model="paymentForm.card_number"
                @input="formatCardNumber"
                placeholder="1234 5678 9012 3456"
                maxlength="19"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-500': errors.card_number }"
                required
              />
              <p v-if="errors.card_number" class="mt-1 text-sm text-red-600">{{ errors.card_number[0] }}</p>
            </div>
            
            <!-- Expiry & CVV -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                <input 
                  type="text" 
                  v-model="paymentForm.expiry"
                  @input="formatExpiry"
                  placeholder="MM/YY"
                  maxlength="5"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.expiry }"
                  required
                />
                <p v-if="errors.expiry" class="mt-1 text-sm text-red-600">{{ errors.expiry[0] }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">CVV</label>
                <input 
                  type="text" 
                  v-model="paymentForm.cvv"
                  @input="formatCVV"
                  placeholder="123"
                  maxlength="4"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.cvv }"
                  required
                />
                <p v-if="errors.cvv" class="mt-1 text-sm text-red-600">{{ errors.cvv[0] }}</p>
              </div>
            </div>
            
            <!-- Cardholder Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Cardholder Name</label>
              <input 
                type="text" 
                v-model="paymentForm.card_name"
                placeholder="John Doe"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-500': errors.card_name }"
                required
              />
              <p v-if="errors.card_name" class="mt-1 text-sm text-red-600">{{ errors.card_name[0] }}</p>
            </div>
            
            <!-- Security Notice -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
              <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <div>
                  <p class="text-sm font-medium text-green-800">Secure Payment</p>
                  <p class="text-xs text-green-600">Your payment information is encrypted and secure</p>
                </div>
              </div>
            </div>
            
            <!-- Submit Button -->
            <button 
              type="submit"
              :disabled="processing"
              class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-4 rounded-lg font-bold text-lg hover:from-blue-700 hover:to-blue-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
            >
              <span v-if="processing" class="flex items-center justify-center gap-2">
                <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Processing Payment...
              </span>
              <span v-else>Pay ${{ reservation.total_price }}</span>
            </button>
          </form>
        </div>
        
        <!-- Reservation Summary -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 h-fit">
          <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Reservation Summary</h2>
          </div>
          
          <!-- Car Details -->
          <div class="mb-6">
            <img 
              :src="getCarImage(reservation.car)" 
              :alt="`${reservation.car.make} ${reservation.car.model}`"
              class="w-full h-40 object-contain bg-gray-50 rounded-lg mb-4"
            />
            <h3 class="text-lg font-bold text-gray-900 mb-1">
              {{ reservation.car.make }} {{ reservation.car.model }}
            </h3>
            <p class="text-sm text-gray-600 mb-4">
              {{ reservation.car.year }} • {{ reservation.car.license_plate }}
            </p>
          </div>
          
          <!-- Rental Details -->
          <div class="space-y-4 mb-6">
            <div class="flex justify-between">
              <span class="text-gray-600">Pick-up Date:</span>
              <span class="font-medium text-gray-900">{{ formatDate(reservation.start_date) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Return Date:</span>
              <span class="font-medium text-gray-900">{{ formatDate(reservation.end_date) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Duration:</span>
              <span class="font-medium text-gray-900">{{ calculateDays(reservation.start_date, reservation.end_date) }} day{{ calculateDays(reservation.start_date, reservation.end_date) > 1 ? 's' : '' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Daily Rate:</span>
              <span class="font-medium text-gray-900">${{ reservation.car.rental_price_per_day }}</span>
            </div>
          </div>
          
          <!-- Agency Info -->
          <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h4 class="font-medium text-gray-900 mb-1">Rental Agency</h4>
            <p class="text-sm text-gray-600">{{ reservation.car.agency.name }}</p>
          </div>
          
          <!-- Total -->
          <div class="pt-6 border-t border-gray-200">
            <div class="flex justify-between items-center">
              <span class="text-lg font-bold text-gray-900">Total Amount:</span>
              <span class="text-2xl font-bold text-green-600">${{ reservation.total_price }}</span>
            </div>
          </div>
        </div>
      </div>
    </main>
    
    <Footer />
  </div>
</template>