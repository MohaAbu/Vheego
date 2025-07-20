<script setup>
import NavBar from '../Components/NavBar.vue';
import Footer from '../Components/Footer.vue';
import FlashMessage from '../Components/FlashMessage.vue';
import { computed, ref } from 'vue';
import { usePage, Head } from '@inertiajs/vue3';
import { router, Link } from '@inertiajs/vue3';

const page = usePage();

const categoryIcons = {
  economy: '💰',
  luxury: '✨',
  suv: '🏔️',
  sedan: '👔',
  hatchback: '🏙️',
  convertible: '🌞',
};

const categoryDescriptions = {
  economy: 'Budget-friendly options',
  luxury: 'Premium experience',
  suv: 'Family adventures',
  sedan: 'Business comfort',
  hatchback: 'City driving',
  convertible: 'Open-air freedom',
};

const categories = computed(() => (page.props.categories || []).map(cat => {
  const key = cat.toLowerCase();
  return {
    name: cat,
    icon: categoryIcons[key] || '🚗',
    description: categoryDescriptions[key] || '',
  };
}));
const featuredCars = computed(() => page.props.featuredCars || []);
const topAgencies = computed(() => page.props.topAgencies || []);
const stats = [
  { number: '10K+', label: 'Happy Customers' },
  { number: '500+', label: 'Cars Available' },
  { number: '50+', label: 'Locations' },
  { number: '24/7', label: 'Support' },
];
const heroCar = computed(() => page.props.heroCar || null);

const today = new Date().toISOString().split('T')[0];
const startDate = ref(today);
const endDate = ref(today);

const goToCategory = (cat) => {
  router.get('/cars', { category: cat.name });
};
const goToCar = (car) => {
  router.get(`/cars/${car.id}`);
};
const goToAgency = (agency) => {
  router.get(`/agencies/${agency.id}`);
};
const goToAllCars = () => {
  router.get('/cars');
};
const submitSearch = (e) => {
  e.preventDefault();
  router.get('/cars', {
    start_date: startDate.value,
    end_date: endDate.value,
  });
};

function renderStars(rating) {
  const fullStars = Math.floor(rating);
  const halfStar = rating - fullStars >= 0.5;
  return Array.from({ length: 5 }, (_, i) =>
    i < fullStars ? 'full' : (i === fullStars && halfStar ? 'half' : 'empty')
  );
}

// Flash messages
const flashMessages = ref([]);

const flash = computed(() => page.props.flash || {});

// Add flash messages on mount
const checkFlashMessages = () => {
  if (flash.value.success) {
    flashMessages.value.push({ type: 'success', message: flash.value.success, id: Date.now() });
  }
  if (flash.value.error) {
    flashMessages.value.push({ type: 'error', message: flash.value.error, id: Date.now() + 1 });
  }
  if (flash.value.info) {
    flashMessages.value.push({ type: 'info', message: flash.value.info, id: Date.now() + 2 });
  }
};

const removeFlashMessage = (id) => {
  flashMessages.value = flashMessages.value.filter(msg => msg.id !== id);
};

// Check for flash messages when component mounts
if (flash.value.success || flash.value.error || flash.value.info) {
  checkFlashMessages();
}
</script>

<template>
  <Head title="Home" />
  
  <div class="min-h-screen flex flex-col bg-gray-50">
    <!-- Flash Messages -->
    <FlashMessage
      v-for="message in flashMessages"
      :key="message.id"
      :type="message.type"
      :message="message.message"
      @close="removeFlashMessage(message.id)"
    />
    
    <NavBar :user="page.props.user" />
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 text-white overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-10">
        <div class="hero-pattern"></div>
      </div>
      <!-- Hero Content -->
      <div class="relative w-full max-w-7xl mx-auto px-4 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <!-- Left Column - Text Content -->
          <div class="space-y-8">
            <div class="space-y-4">
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-600/20 border border-blue-400/30">
                <span class="text-blue-200 text-sm font-medium">Premium Car Rental</span>
              </div>
              <h1 class="text-4xl lg:text-6xl font-bold leading-tight">
                Your Perfect
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-400">
                  Ride Awaits
                </span>
              </h1>
              <p class="text-xl text-blue-100 max-w-lg">
                Discover premium vehicles at unbeatable prices. Book instantly and drive with confidence.
              </p>
            </div>
            <!-- Search Form -->
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
              <form class="space-y-4" @submit="submitSearch">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-blue-200 mb-2">From</label>
                    <input 
                      type="date" 
                      v-model="startDate" 
                      :min="today"
                      class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-blue-200 mb-2">To</label>
                    <input 
                      type="date" 
                      v-model="endDate" 
                      :min="startDate"
                      class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"
                    />
                  </div>
                </div>
                <button 
                  type="submit" 
                  class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:from-blue-600 hover:to-cyan-600 transform hover:scale-105 transition-all duration-200 shadow-lg"
                >
                  Search Available Cars
                </button>
              </form>
            </div>
          </div>
          <!-- Right Column - Hero Car -->
          <div class="relative">
            <div v-if="heroCar" class="relative z-10">
              <div class="bg-white/15 backdrop-blur-sm rounded-2xl p-6 border border-white/20 transform rotate-3 hover:rotate-0 transition-transform duration-300">
                <div class="bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl h-48 flex items-center justify-center mb-4 overflow-hidden">
                  <img :src="heroCar.image_path || '/images/car-placeholder.png'" :alt="heroCar.make + ' ' + heroCar.model" class="h-44 object-contain" />
                </div>
                <h3 class="text-xl font-bold text-white mb-2">{{ heroCar.make }} {{ heroCar.model }}</h3>
                <p class="text-blue-200 mb-2">Starting from ${{ heroCar.rental_price_per_day }}/day</p>
                <div class="flex items-center gap-2 mb-2">
                  <span class="flex items-center">
                    <span v-for="(type, i) in renderStars(heroCar.reviews_avg_rating || 0)" :key="i">
                      <svg v-if="type === 'full'" class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/></svg>
                      <svg v-else-if="type === 'half'" class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><defs><linearGradient id="half"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="white" stop-opacity="1"/></linearGradient></defs><path fill="url(#half)" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/></svg>
                      <svg v-else class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/></svg>
                    </span>
                  </span>
                  <span class="ml-2 text-base font-semibold text-white">
                    {{ (parseFloat(heroCar.reviews_avg_rating) || 0).toFixed(1) }}
                  </span>
                  <span class="ml-2 text-xs text-blue-100">({{ heroCar.reviews_count }} review{{ heroCar.reviews_count === 1 ? '' : 's' }})</span>
                </div>
                <button class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-600 transition mt-2" @click.stop="goToCar(heroCar)">
                  Book Now
                </button>
              </div>
            </div>
            <div v-else class="relative z-10">
              <div class="bg-white/15 backdrop-blur-sm rounded-2xl p-6 border border-white/20 h-64 flex items-center justify-center text-white text-xl font-bold opacity-60">
                No car data available
              </div>
            </div>
            <!-- Background Decorative Elements -->
            <div class="absolute -top-4 -right-4 w-72 h-72 bg-blue-400/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-8 -left-8 w-56 h-56 bg-cyan-400/20 rounded-full blur-3xl"></div>
          </div>
        </div>
      </div>
      <!-- Stats Section -->
      <div class="relative border-t border-white/10 bg-white/5 backdrop-blur-sm">
        <div class="w-full max-w-7xl mx-auto px-4 py-8">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div v-for="stat in stats" :key="stat.label" class="text-center">
              <div class="text-2xl lg:text-3xl font-bold text-white mb-1">{{ stat.number }}</div>
              <div class="text-blue-200 text-sm">{{ stat.label }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-12">
      <!-- Car Categories -->
      <section class="mb-16">
        <div class="text-center mb-12">
          <h2 class="text-3xl lg:text-4xl font-bold text-gray-800 mb-4">Browse by Category</h2>
          <p class="text-gray-600 max-w-2xl mx-auto">Find the perfect vehicle for your needs from our diverse fleet</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-6">
          <div v-for="cat in categories" :key="cat.name" class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 text-center cursor-pointer hover:-translate-y-2" @click="goToCategory(cat)">
            <div class="text-4xl mb-3 group-hover:scale-110 transition-transform duration-300">{{ cat.icon }}</div>
            <div class="font-bold text-gray-800 mb-1">{{ cat.name }}</div>
            <div class="text-sm text-gray-500">{{ cat.description }}</div>
          </div>
        </div>
      </section>
      <!-- Featured Cars -->
      <section class="mb-16">
        <div class="text-center mb-12">
          <h2 class="text-3xl lg:text-4xl font-bold text-gray-800 mb-4">Featured Cars</h2>
          <p class="text-gray-600 max-w-2xl mx-auto">Handpicked premium vehicles with the best value and ratings</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="car in featuredCars" :key="car.id" class="group bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden cursor-pointer flex flex-col border border-gray-100" @click="goToCar(car)">
            <!-- Car Image -->
            <div class="relative bg-gradient-to-br from-gray-50 to-gray-100 p-8 rounded-t-3xl">
              <img :src="car.image_path || '/images/car-placeholder.png'" :alt="car.make + ' ' + car.model" class="h-40 w-full object-contain" />
              
              <!-- Badges -->
              <div v-if="car.special_offer" class="absolute top-4 left-4 bg-red-500 text-white px-4 py-2 rounded-full text-xs font-bold shadow-lg">
                Special Offer
              </div>
              <div v-if="car.average_rating === 5" class="absolute top-4 right-4 bg-yellow-400 text-black px-4 py-2 rounded-full text-xs font-bold shadow-lg">
                Perfect 5.0
              </div>
            </div>

            <!-- Content Section -->
            <div class="p-6 flex-1 flex flex-col">
              <!-- Rating -->
              <div class="flex items-center gap-2 mb-4">
                <span class="flex items-center">
                  <span v-for="(type, i) in renderStars(car.average_rating)" :key="i">
                    <svg v-if="type === 'full'" class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/></svg>
                    <svg v-else-if="type === 'half'" class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><defs><linearGradient id="half"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="white" stop-opacity="1"/></linearGradient></defs><path fill="url(#half)" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/></svg>
                    <svg v-else class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/></svg>
                  </span>
                </span>
                <span class="ml-2 text-lg font-bold text-gray-900">{{ car.average_rating }}</span>
                <span class="text-sm text-gray-500">({{ car.reviews_count }} review{{ car.reviews_count === 1 ? '' : 's' }})</span>
              </div>

              <!-- Car Title & Price -->
              <div class="mb-3">
                <h3 class="font-bold text-xl text-gray-900 mb-1">{{ car.make }} {{ car.model }}</h3>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">{{ car.year }} • {{ car.category }}</span>
                  <span class="text-2xl font-bold text-blue-600">${{ car.rental_price_per_day }}</span>
                </div>
              </div>

              <!-- Features -->
              <div v-if="car.features && car.features.length" class="mb-6">
                <div class="flex flex-wrap gap-2">
                  <span v-for="feature in (Array.isArray(car.features) ? car.features.slice(0, 3) : (typeof car.features === 'string' ? JSON.parse(car.features).slice(0, 3) : []) )" :key="feature" class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-sm font-medium">
                    {{ feature }}
                  </span>
                  <span v-if="(Array.isArray(car.features) ? car.features.length : (typeof car.features === 'string' ? JSON.parse(car.features).length : 0)) > 3" class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm">
                    +{{ (Array.isArray(car.features) ? car.features.length : (typeof car.features === 'string' ? JSON.parse(car.features).length : 0)) - 3 }} more
                  </span>
                </div>
              </div>

              <!-- Book Button -->
              <button class="w-full mt-auto bg-blue-600 text-white px-6 py-4 rounded-2xl font-bold text-lg hover:bg-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105" @click.stop="goToCar(car)">
                Book Now - ${{ car.rental_price_per_day }}/day
              </button>
            </div>
          </div>
        </div>
        <!-- View All CTA -->
        <div class="text-center mt-12">
          <button class="bg-white border-2 border-blue-600 text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-600 hover:text-white transition-all duration-200" @click="goToAllCars">
            View All Cars →
          </button>
        </div>
      </section>
      <!-- Top Rated Agencies -->
      <section class="mb-16">
        <div class="text-center mb-12">
          <h2 class="text-3xl lg:text-4xl font-bold text-gray-800 mb-4">Top Rated Agencies</h2>
          <p class="text-gray-600 max-w-2xl mx-auto">Partner with the most trusted car rental agencies in the industry</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="agency in topAgencies" :key="agency.id" class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 cursor-pointer" @click="goToAgency(agency)">
            <div class="flex items-center gap-4 mb-4">
              <img :src="agency.logo || '/images/agency-placeholder.png'" :alt="agency.name" class="w-16 h-16 rounded-full object-cover border" />
              <div>
                <h3 class="font-bold text-xl text-gray-800">{{ agency.name }}</h3>
                <div class="flex items-center text-yellow-500">
                  <span class="flex items-center mr-2">
                    <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="{'text-yellow-400': i <= Math.round(agency.rating), 'text-gray-200': i > Math.round(agency.rating)}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/></svg>
                  </span>
                  <span class="text-gray-600 ml-1 text-base font-semibold">{{ agency.rating.toFixed(2) }}</span>
                </div>
                <div class="flex items-center gap-1 mt-1 text-gray-500 text-sm">
                  <svg class="w-4 h-4 text-blue-400 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  <span class="truncate max-w-[180px] block" :title="agency.locations">{{ agency.locations }}</span>
                </div>
              </div>
            </div>
            <div class="flex items-center justify-between mb-4">
              <div></div>
              <div class="flex items-center gap-1">
                <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l1-5a2 2 0 012-2h12a2 2 0 012 2l1 5M5 16h14M7 16v2a2 2 0 01-2 2h0a2 2 0 01-2-2v-2m14 0v2a2 2 0 002 2h0a2 2 0 002-2v-2"/></svg>
                  {{ agency.cars }} Cars
                </span>
              </div>
            </div>
            <button class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
              View Agency
            </button>
          </div>
        </div>
      </section>
      <!-- Final CTA Section -->
      <section class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-3xl p-8 lg:p-12 text-white text-center">
        <div class="max-w-3xl mx-auto">
          <h2 class="text-3xl lg:text-4xl font-bold mb-4">Ready to Hit the Road?</h2>
          <p class="text-xl text-blue-100 mb-8">Join thousands of satisfied customers who chose us for their car rental needs</p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button class="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold hover:bg-blue-50 transform hover:scale-105 transition-all duration-200 shadow-lg" @click="goToAllCars">
              Browse All Cars
            </button>
            <button class="bg-blue-700 text-white px-8 py-4 rounded-lg font-semibold hover:bg-blue-800 transform hover:scale-105 transition-all duration-200 shadow-lg">
              Contact Support
            </button>
          </div>
        </div>
      </section>
    </main>
    <Footer />
  </div>
</template>

<style scoped>
.hero-pattern {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
::-webkit-scrollbar {
  width: 8px;
}
::-webkit-scrollbar-track {
  background: #f1f1f1;
}
::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
* {
  transition: all 0.3s ease;
}
.group:hover {
  transform: translateY(-8px);
}
.bg-clip-text {
  background-clip: text;
  -webkit-background-clip: text;
}
</style>