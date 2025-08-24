<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import NavBar from '../../Components/NavBar.vue';
import Footer from '../../Components/Footer.vue';

const page = usePage();

const props = defineProps({
  reservation: Object
});

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

// Generate confirmation number (simple implementation)
const confirmationNumber = `VH-${props.reservation.id.toString().padStart(6, '0')}`;
</script>

<template>
  <Head title="Booking Confirmed" />
  <div class="min-h-screen flex flex-col bg-gray-50">
    <NavBar :user="page.props.auth.user" />
    
    <main class="flex-1 w-full max-w-4xl mx-auto px-4 py-8">
      <!-- Success Header -->
      <div class="text-center mb-8">
        <div class="mb-4">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          <h1 class="text-3xl font-bold text-gray-800 mb-2">Booking Confirmed! 🎉</h1>
          <p class="text-lg text-gray-600">Your car rental reservation has been successfully confirmed</p>
        </div>
        
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 inline-block">
          <p class="text-sm text-green-700 mb-1">Confirmation Number</p>
          <p class="text-2xl font-bold text-green-800">{{ confirmationNumber }}</p>
        </div>
      </div>
      
      <!-- Reservation Details -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
          <h2 class="text-xl font-bold mb-2">Reservation Details</h2>
          <p class="text-blue-100">Please save this information for your records</p>
        </div>
        
        <!-- Content -->
        <div class="p-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Car Info -->
            <div>
              <div class="mb-6">
                <img 
                  :src="getCarImage(reservation.car)" 
                  :alt="`${reservation.car.make} ${reservation.car.model}`"
                  class="w-full h-48 object-contain bg-gray-50 rounded-lg mb-4"
                />
                <h3 class="text-2xl font-bold text-gray-900 mb-2">
                  {{ reservation.car.make }} {{ reservation.car.model }}
                </h3>
                <div class="space-y-2 text-gray-600">
                  <p><span class="font-medium">Year:</span> {{ reservation.car.year }}</p>
                  <p><span class="font-medium">License Plate:</span> {{ reservation.car.license_plate }}</p>
                  <p><span class="font-medium">Category:</span> {{ reservation.car.category }}</p>
                  <p><span class="font-medium">Transmission:</span> {{ reservation.car.transmission }}</p>
                  <p><span class="font-medium">Fuel Type:</span> {{ reservation.car.fuel_type }}</p>
                </div>
              </div>
              
              <!-- Agency Information -->
              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-bold text-gray-900 mb-2">Rental Agency</h4>
                <p class="text-gray-700 font-medium">{{ reservation.car.agency.name }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ reservation.car.agency.contact_email }}</p>
                <p class="text-sm text-gray-600">{{ reservation.car.agency.contact_phone }}</p>
              </div>
            </div>
            
            <!-- Booking Info -->
            <div>
              <div class="space-y-6">
                <!-- Rental Period -->
                <div>
                  <h4 class="font-bold text-gray-900 mb-4">Rental Period</h4>
                  <div class="space-y-3">
                    <div class="flex items-center gap-3">
                      <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                      <div>
                        <p class="font-medium text-gray-900">Pick-up</p>
                        <p class="text-gray-600">{{ formatDate(reservation.start_date) }}</p>
                      </div>
                    </div>
                    <div class="flex items-center gap-3">
                      <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                      <div>
                        <p class="font-medium text-gray-900">Return</p>
                        <p class="text-gray-600">{{ formatDate(reservation.end_date) }}</p>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Payment Summary -->
                <div>
                  <h4 class="font-bold text-gray-900 mb-4">Payment Summary</h4>
                  <div class="space-y-3">
                    <div class="flex justify-between">
                      <span class="text-gray-600">Duration:</span>
                      <span class="font-medium">{{ calculateDays(reservation.start_date, reservation.end_date) }} day{{ calculateDays(reservation.start_date, reservation.end_date) > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Daily Rate:</span>
                      <span class="font-medium">${{ reservation.car.rental_price_per_day }}</span>
                    </div>
                    <div class="border-t border-gray-200 pt-3">
                      <div class="flex justify-between">
                        <span class="text-lg font-bold text-gray-900">Total Paid:</span>
                        <span class="text-lg font-bold text-green-600">${{ reservation.total_price }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Status -->
                <div>
                  <h4 class="font-bold text-gray-900 mb-2">Booking Status</h4>
                  <div class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Active
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Important Information -->
      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
        <h3 class="font-bold text-yellow-800 mb-3">📋 Important Information</h3>
        <ul class="space-y-2 text-sm text-yellow-700">
          <li>• Please bring a valid driver's license and credit card for pickup</li>
          <li>• Arrive at the agency location 15-30 minutes before your pickup time</li>
          <li>• Contact the rental agency if you need to modify or cancel your booking</li>
          <li>• Keep your confirmation number for reference</li>
          <li>• Return the vehicle with the same fuel level as pickup</li>
        </ul>
      </div>
      
      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <Link 
          href="/reservations" 
          class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
          View All Reservations
        </Link>
        <Link 
          href="/cars" 
          class="inline-flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-50 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          Browse More Cars
        </Link>
        <button 
          @click="window.print()" 
          class="inline-flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-50 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
          </svg>
          Print Confirmation
        </button>
      </div>
    </main>
    
    <Footer />
  </div>
</template>

<style scoped>
@media print {
  .no-print {
    display: none;
  }
}
</style>