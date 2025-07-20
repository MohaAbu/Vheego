<script setup>
import { Head, Link } from '@inertiajs/vue3';
import NavBar from '../../Components/NavBar.vue';
import Footer from '../../Components/Footer.vue';
import Breadcrumb from '../../Components/Breadcrumb.vue';
import { usePage, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const page = usePage();
const props = defineProps({
  cars: Object, // Changed from Array to Object to handle pagination
  filters: Object,
  agencies: Array,
  categories: Array,
  locations: Array,
});

// Create a reactive reference for cars data that will accumulate more cars
const carsData = ref(props.cars);
const loading = ref(false);

// Filter sidebar state
const sidebarOpen = ref(false);
const today = new Date().toISOString().split('T')[0];

// Filter form data
const filterForm = ref({
  start_date: props.filters?.start_date || '',
  end_date: props.filters?.end_date || '',
  category: props.filters?.category || '',
  agency_id: props.filters?.agency_id || '',
  location: props.filters?.location || '',
  min_price: props.filters?.min_price || '',
  max_price: props.filters?.max_price || '',
  fuel_type: props.filters?.fuel_type || '',
  transmission: props.filters?.transmission || '',
  seats: props.filters?.seats || '',
});

const activeFiltersCount = computed(() => {
  return Object.values(filterForm.value).filter(value => value !== '' && value !== null).length;
});

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const getCarImages = (car) => {
  if (car.images && car.images.length > 0) {
    // Sort images with primary first
    const images = [...car.images];
    images.sort((a, b) => {
      if (a.is_primary && !b.is_primary) return -1;
      if (!a.is_primary && b.is_primary) return 1;
      return 0;
    });
    return images.map(img => `/storage/${img.image_path}`);
  }
  return ['/images/car-placeholder.png'];
};

// Track current image index for each car
const currentImageIndexes = ref({});

const nextImage = (carId, totalImages, event) => {
  event.stopPropagation(); // Prevent card click
  if (!currentImageIndexes.value[carId]) {
    currentImageIndexes.value[carId] = 0;
  }
  currentImageIndexes.value[carId] = (currentImageIndexes.value[carId] + 1) % totalImages;
};

const prevImage = (carId, totalImages, event) => {
  event.stopPropagation(); // Prevent card click
  if (!currentImageIndexes.value[carId]) {
    currentImageIndexes.value[carId] = 0;
  }
  currentImageIndexes.value[carId] = currentImageIndexes.value[carId] === 0 
    ? totalImages - 1 
    : currentImageIndexes.value[carId] - 1;
};

const getCurrentImageIndex = (carId) => {
  return currentImageIndexes.value[carId] || 0;
};

const applyFilters = () => {
  // Remove empty filters
  const cleanFilters = Object.fromEntries(
    Object.entries(filterForm.value).filter(([key, value]) => value !== '' && value !== null)
  );
  
  router.visit('/cars', {
    data: cleanFilters,
    preserveState: false,
    onSuccess: () => {
      sidebarOpen.value = false;
    }
  });
};

const clearFilters = () => {
  filterForm.value = {
    start_date: '',
    end_date: '',
    category: '',
    agency_id: '',
    location: '',
    min_price: '',
    max_price: '',
    fuel_type: '',
    transmission: '',
    seats: '',
  };
  applyFilters();
};

const loadMore = () => {
  if (carsData.value.next_page_url && !loading.value) {
    loading.value = true;
    
    // Build the URL with current filters preserved
    const url = new URL(carsData.value.next_page_url);
    Object.entries(filterForm.value).forEach(([key, value]) => {
      if (value) url.searchParams.set(key, value);
    });
    
    router.visit(url.toString(), {
      preserveState: true,
      preserveScroll: true,
      only: ['cars'],
      onSuccess: (page) => {
        // Append new cars to existing ones
        const newCars = page.props.cars.data;
        carsData.value.data.push(...newCars);
        carsData.value.current_page = page.props.cars.current_page;
        carsData.value.next_page_url = page.props.cars.next_page_url;
        carsData.value.to = page.props.cars.to;
        loading.value = false;
      },
      onError: () => {
        loading.value = false;
      }
    });
  }
};

// Scroll to top functionality
const showScrollToTop = ref(false);

const handleScroll = () => {
  showScrollToTop.value = window.scrollY > 300;
};

const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
};

// Add scroll event listener
onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

// Breadcrumb items for browse page
const breadcrumbItems = computed(() => {
  const items = [
    { label: 'Home', href: '/' },
    { label: 'Browse Cars' }
  ];

  // Add filter-specific breadcrumb if filtering by agency
  if (props.filters?.agency_id && props.agencies) {
    const selectedAgency = props.agencies.find(agency => agency.id == props.filters.agency_id);
    if (selectedAgency) {
      items.push({ label: selectedAgency.name });
    }
  }

  return items;
});
</script>

<template>
  <Head title="Browse Cars" />
  <div class="min-h-screen flex flex-col bg-gray-50">
    <NavBar :user="page.props.auth.user" />
    
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-4">
      <!-- Breadcrumb -->
      <div class="mb-2">
        <Breadcrumb :items="breadcrumbItems" />
      </div>

      <!-- Page Header -->
      <div class="text-center mb-4">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-800 mb-2">Browse Available Cars</h1>
        <p class="text-base text-gray-600 max-w-xl mx-auto">
          Discover the perfect vehicle for your journey
        </p>
      </div>

      <!-- Filter Toggle Button (Mobile) -->
      <div class="lg:hidden mb-3">
        <button
          @click="sidebarOpen = !sidebarOpen"
          class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
          </svg>
          Filters
          <span v-if="activeFiltersCount > 0" class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ activeFiltersCount }}</span>
        </button>
      </div>

      <!-- Main Content Layout -->
      <div class="flex flex-col lg:flex-row gap-4">
        <!-- Filter Sidebar -->
        <div :class="['lg:block', sidebarOpen ? 'block' : 'hidden']" class="w-full lg:w-64 lg:flex-shrink-0">
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 lg:sticky lg:top-4 max-h-screen lg:max-h-[calc(100vh-2rem)] flex flex-col">
            <!-- Fixed Header -->
            <div class="sticky top-0 z-10 flex items-center justify-between p-4 border-b-2 border-blue-100 bg-gradient-to-r from-blue-50 to-white rounded-t-2xl flex-shrink-0 shadow-sm">
              <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                </svg>
                Filters
              </h3>
              <button 
                v-if="activeFiltersCount > 0"
                @click="clearFilters"
                class="text-sm text-red-600 hover:text-red-800 font-semibold bg-red-50 hover:bg-red-100 px-2 py-1 rounded-md transition-colors"
              >
                Clear All
              </button>
            </div>

            <!-- Scrollable Form Content -->
            <div class="flex-1 overflow-y-auto p-3">
              <form @submit.prevent="applyFilters" class="space-y-3">
              <!-- Date Range -->
              <div>
                <h4 class="font-medium text-gray-700 mb-1.5 text-sm">Date Range</h4>
                <div class="space-y-1.5">
                  <div>
                    <input 
                      type="date" 
                      v-model="filterForm.start_date"
                      :min="today"
                      placeholder="From"
                      class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <input 
                      type="date" 
                      v-model="filterForm.end_date"
                      :min="filterForm.start_date || today"
                      placeholder="To"
                      class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                </div>
              </div>

              <!-- Category -->
              <div>
                <h4 class="font-medium text-gray-700 mb-1.5 text-sm">Category</h4>
                <select 
                  v-model="filterForm.category"
                  class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">All Categories</option>
                  <option value="economy">Economy</option>
                  <option value="luxury">Luxury</option>
                  <option value="suv">SUV</option>
                  <option value="sedan">Sedan</option>
                  <option value="hatchback">Hatchback</option>
                  <option value="convertible">Convertible</option>
                </select>
              </div>

              <!-- Price Range -->
              <div>
                <h4 class="font-medium text-gray-700 mb-1.5 text-sm">Price Range</h4>
                <div class="grid grid-cols-2 gap-1.5">
                  <input 
                    type="number" 
                    v-model="filterForm.min_price"
                    placeholder="Min $"
                    min="0"
                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                  />
                  <input 
                    type="number" 
                    v-model="filterForm.max_price"
                    placeholder="Max $"
                    min="0"
                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Fuel Type -->
              <div>
                <h4 class="font-medium text-gray-700 mb-1.5 text-sm">Fuel Type</h4>
                <select 
                  v-model="filterForm.fuel_type"
                  class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">All Types</option>
                  <option value="gasoline">Gasoline</option>
                  <option value="diesel">Diesel</option>
                  <option value="electric">Electric</option>
                  <option value="hybrid">Hybrid</option>
                </select>
              </div>

              <!-- Transmission -->
              <div>
                <h4 class="font-medium text-gray-700 mb-1.5 text-sm">Transmission</h4>
                <select 
                  v-model="filterForm.transmission"
                  class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">All Types</option>
                  <option value="manual">Manual</option>
                  <option value="automatic">Automatic</option>
                </select>
              </div>

              <!-- Seats -->
              <div>
                <h4 class="font-medium text-gray-700 mb-1.5 text-sm">Seats</h4>
                <select 
                  v-model="filterForm.seats"
                  class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Any</option>
                  <option value="2">2 Seats</option>
                  <option value="4">4 Seats</option>
                  <option value="5">5 Seats</option>
                  <option value="7">7 Seats</option>
                  <option value="8">8+ Seats</option>
                </select>
              </div>

              <!-- Agency -->
              <div v-if="agencies && agencies.length">
                <h4 class="font-medium text-gray-700 mb-1.5 text-sm">Agency</h4>
                <select 
                  v-model="filterForm.agency_id"
                  class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">All Agencies</option>
                  <option v-for="agency in agencies" :key="agency.id" :value="agency.id">{{ agency.name }}</option>
                </select>
              </div>

              </form>
            </div>

            <!-- Fixed Footer with Apply Button -->
            <div class="p-3 border-t border-gray-100 bg-white rounded-b-2xl flex-shrink-0">
              <button 
                @click="applyFilters"
                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white px-3 py-2 rounded font-medium text-sm hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md"
              >
                Apply Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Cars Content -->
        <div class="flex-1">
      
      <!-- Filter Summary -->
      <div v-if="filters && (filters.start_date || filters.end_date || filters.category)" class="mb-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-200 shadow-sm">
        <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
          </svg>
          Applied Filters
        </h3>
        <div class="flex flex-wrap gap-3 text-sm">
          <div v-if="filters.start_date && filters.end_date" class="flex items-center gap-2">
            <span class="text-blue-600 font-medium">📅 Date Range:</span>
            <span class="bg-blue-100 px-2 py-1 rounded-full font-medium">{{ formatDate(filters.start_date) }} - {{ formatDate(filters.end_date) }}</span>
          </div>
          <div v-if="filters.category" class="flex items-center gap-2">
            <span class="text-blue-600 font-medium">🚗 Category:</span>
            <span class="bg-blue-100 px-2 py-1 rounded-full font-medium capitalize">{{ filters.category }}</span>
          </div>
        </div>
        <div class="mt-2 text-sm text-blue-700 font-medium">
          Showing {{ carsData.from || 0 }}-{{ carsData.to || 0 }} of {{ carsData.total || 0 }} car{{ carsData.total === 1 ? '' : 's' }} available
        </div>
      </div>
      
          <!-- Cars Grid -->
          <div v-if="carsData && carsData.data && carsData.data.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="car in carsData.data" :key="car.id" class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden cursor-pointer border border-gray-100" @click="$inertia.visit(`/cars/${car.id}`)">
          <!-- Car Image Slider -->
          <div class="relative bg-gradient-to-br from-gray-50 to-gray-100 p-4 rounded-t-2xl">
            <div class="relative h-40 w-full overflow-hidden rounded-lg">
              <!-- Images -->
              <div 
                v-for="(image, index) in getCarImages(car)" 
                :key="index"
                class="absolute inset-0 transition-opacity duration-300"
                :class="index === getCurrentImageIndex(car.id) ? 'opacity-100' : 'opacity-0'"
              >
                <img 
                  :src="image" 
                  :alt="`${car.make} ${car.model} - Image ${index + 1}`" 
                  class="h-full w-full object-contain group-hover:scale-105 transition-transform duration-300" 
                />
              </div>
              
              <!-- Navigation Arrows (only show if more than 1 image) -->
              <template v-if="getCarImages(car).length > 1">
                <!-- Previous Button -->
                <button 
                  @click="prevImage(car.id, getCarImages(car).length, $event)"
                  class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-1.5 rounded-full transition-all duration-200 opacity-0 group-hover:opacity-100"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                  </svg>
                </button>
                
                <!-- Next Button -->
                <button 
                  @click="nextImage(car.id, getCarImages(car).length, $event)"
                  class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-1.5 rounded-full transition-all duration-200 opacity-0 group-hover:opacity-100"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>
                
                <!-- Image Dots Indicator -->
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1">
                  <div 
                    v-for="(image, index) in getCarImages(car)" 
                    :key="index"
                    class="w-2 h-2 rounded-full transition-all duration-200"
                    :class="index === getCurrentImageIndex(car.id) ? 'bg-white' : 'bg-white/50'"
                  ></div>
                </div>
              </template>
            </div>
            
            <!-- Category Badge -->
            <div class="absolute top-3 left-3 bg-blue-600 text-white px-2 py-1 rounded-full text-xs font-bold shadow-lg z-10">
              {{ car.category }}
            </div>
            
            <!-- Price Badge -->
            <div class="absolute top-3 right-3 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold shadow-lg z-10">
              ${{ car.rental_price_per_day }}/day
            </div>
            
            <!-- Image Counter (top right, below price) -->
            <div v-if="getCarImages(car).length > 1" class="absolute top-12 right-3 bg-black/60 text-white px-2 py-1 rounded text-xs font-medium z-10">
              {{ getCurrentImageIndex(car.id) + 1 }}/{{ getCarImages(car).length }}
            </div>
          </div>

          <!-- Content Section -->
          <div class="p-4">
            <!-- Car Title -->
            <h3 class="font-bold text-lg text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
              {{ car.make }} {{ car.model }}
            </h3>
            
            <!-- Car Details -->
            <div class="space-y-1.5 mb-3">
              <div class="flex items-center gap-2 text-gray-600">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="text-sm">{{ car.year }}</span>
              </div>
              
              <div class="flex items-center gap-2 text-gray-600">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="text-sm">{{ car.agency?.name || 'N/A' }}</span>
              </div>
              
              <div class="flex items-center gap-2 text-gray-600">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                <span class="text-sm">{{ car.transmission }} • {{ car.fuel_type }}</span>
              </div>
            </div>

            <!-- Book Button -->
            <button class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-2.5 rounded-xl font-bold text-base hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
              Book Now
            </button>
          </div>
        </div>
      </div>
      
      <!-- Show More Button -->
      <div v-if="carsData && carsData.next_page_url" class="flex justify-center mt-8">
        <button
          @click="loadMore"
          :disabled="loading"
          class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-xl font-bold text-base hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
        >
          <svg v-if="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          <svg v-else class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          {{ loading ? 'Loading...' : 'Show More Cars' }}
        </button>
      </div>

      <!-- Results Info -->
      <div v-if="carsData && carsData.data && carsData.data.length" class="text-center mt-4">
        <p class="text-sm text-gray-600">
          Showing <span class="font-medium">{{ carsData.data.length }}</span> of 
          <span class="font-medium">{{ carsData.total }}</span> cars
          <span v-if="carsData.next_page_url" class="text-blue-600 font-medium"> • Load more to see all results</span>
        </p>
      </div>
      
      <!-- No Cars Message -->
      <div v-else class="text-center py-12">
        <div class="max-w-md mx-auto">
          <div class="text-5xl mb-4">🚗</div>
          <div class="text-xl font-bold text-gray-400 mb-3">
            <div v-if="filters && (filters.start_date || filters.end_date || filters.category)">
              No cars available for your selected criteria.
            </div>
            <div v-else>
              No cars available at the moment.
            </div>
          </div>
          <div v-if="filters && (filters.start_date || filters.end_date)" class="text-gray-500 mb-4">
            Try adjusting your date range or removing some filters to see more options.
          </div>
          <Link href="/cars" class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
            </svg>
            View All Cars
          </Link>
        </div>
      </div>
        </div>
      </div>
    </main>
    
    <!-- Scroll to Top Button -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 scale-75 translate-y-4"
      enter-to-class="opacity-100 scale-100 translate-y-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100 scale-100 translate-y-0"
      leave-to-class="opacity-0 scale-75 translate-y-4"
    >
      <button
        v-if="showScrollToTop"
        @click="scrollToTop"
        class="fixed bottom-6 right-6 z-50 bg-gradient-to-r from-blue-600 to-blue-700 text-white p-3 rounded-full shadow-lg hover:from-blue-700 hover:to-blue-800 hover:shadow-xl transform hover:scale-110 transition-all duration-200"
        aria-label="Scroll to top"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
      </button>
    </Transition>
    
    <Footer />
  </div>
</template> 