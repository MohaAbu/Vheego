<script setup>
import { Head, Link } from '@inertiajs/vue3';
import NavBar from '../../Components/NavBar.vue';
import Footer from '../../Components/Footer.vue';
import Breadcrumb from '../../Components/Breadcrumb.vue';
import FavoriteButton from '../../Components/FavoriteButton.vue';
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

// Check if car is favorited by current user
const isCarFavorited = (car) => {
  return car.is_favorited || false;
};

const handleFavoriteUpdate = (carId, isFavorited) => {
  // Find and update the car in the current data
  const carIndex = carsData.value.data.findIndex(car => car.id === carId);
  if (carIndex !== -1) {
    carsData.value.data[carIndex].is_favorited = isFavorited;
  }
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

// Booking modal state
const showBookingModal = ref(false);
const selectedCar = ref(null);
const bookingForm = ref({
  start_date: '',
  end_date: '',
  total_price: 0,
  days: 0
});
const bookingLoading = ref(false);
const availabilityChecking = ref(false);
const availabilityStatus = ref(null); // 'available', 'unavailable', null
const availabilityMessage = ref('');

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

// Booking modal functions
const openBookingModal = (car, event) => {
  event.stopPropagation();
  selectedCar.value = car;
  
  // Pre-fill dates from filters if available
  bookingForm.value.start_date = filterForm.value.start_date || today;
  bookingForm.value.end_date = filterForm.value.end_date || today;
  
  calculateBookingPrice();
  showBookingModal.value = true;
};

const closeBookingModal = () => {
  showBookingModal.value = false;
  selectedCar.value = null;
  bookingForm.value = {
    start_date: '',
    end_date: '',
    total_price: 0,
    days: 0
  };
  availabilityStatus.value = null;
  availabilityMessage.value = '';
  availabilityChecking.value = false;
};

const calculateBookingPrice = () => {
  if (bookingForm.value.start_date && bookingForm.value.end_date && selectedCar.value) {
    const startDate = new Date(bookingForm.value.start_date);
    const endDate = new Date(bookingForm.value.end_date);
    const timeDiff = endDate - startDate;
    const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1;
    
    if (days > 0) {
      bookingForm.value.days = days;
      bookingForm.value.total_price = days * selectedCar.value.rental_price_per_day;
      checkAvailability();
    } else {
      bookingForm.value.days = 0;
      bookingForm.value.total_price = 0;
      availabilityStatus.value = null;
    }
  }
};

const checkAvailability = async () => {
  if (!selectedCar.value || !bookingForm.value.start_date || !bookingForm.value.end_date) {
    availabilityStatus.value = null;
    return;
  }
  
  availabilityChecking.value = true;
  availabilityStatus.value = null;
  
  try {
    const response = await fetch(`/api/cars/${selectedCar.value.id}/availability?start_date=${bookingForm.value.start_date}&end_date=${bookingForm.value.end_date}`);
    const data = await response.json();
    
    if (data.available) {
      availabilityStatus.value = 'available';
      availabilityMessage.value = 'Car is available for selected dates';
    } else {
      availabilityStatus.value = 'unavailable';
      availabilityMessage.value = data.message || 'Car is not available for selected dates';
    }
  } catch (error) {
    console.error('Error checking availability:', error);
    availabilityStatus.value = 'error';
    availabilityMessage.value = 'Unable to check availability. Please try again.';
  } finally {
    availabilityChecking.value = false;
  }
};

const submitBooking = async () => {
  if (!selectedCar.value || !page.props.auth.user) return;
  
  bookingLoading.value = true;
  
  try {
    router.post('/reservations', {
      car_id: selectedCar.value.id,
      start_date: bookingForm.value.start_date,
      end_date: bookingForm.value.end_date
    }, {
      onSuccess: () => {
        closeBookingModal();
      },
      onError: (errors) => {
        console.error('Booking failed:', errors);
        // Handle validation errors here if needed
      },
      onFinish: () => {
        bookingLoading.value = false;
      }
    });
  } catch (error) {
    console.error('Booking failed:', error);
    bookingLoading.value = false;
  }
};

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
            
            <!-- Favorite Button -->
            <div v-if="page.props.auth.user && page.props.auth.user.user_type === 'customer'" class="absolute top-3 left-3 z-10">
              <FavoriteButton 
                :car-id="car.id" 
                :is-favorited="isCarFavorited(car)" 
                @update="(isFav) => handleFavoriteUpdate(car.id, isFav)"
              />
            </div>
            
            <!-- Category Badge -->
            <div class="absolute bottom-3 left-3 bg-blue-600 text-white px-2 py-1 rounded-full text-xs font-bold shadow-lg z-10">
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
            <button 
              @click.stop="openBookingModal(car, $event)"
              class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-2.5 rounded-xl font-bold text-base hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
            >
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
    
    <!-- Booking Modal -->
    <Teleport to="body">
      <div 
        v-if="showBookingModal" 
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        @click="closeBookingModal"
      >
        <div 
          class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto"
          @click.stop
        >
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div>
              <h3 class="text-xl font-bold text-gray-900">Book Your Car</h3>
              <p class="text-sm text-gray-600 mt-1">Complete your reservation details</p>
            </div>
            <button 
              @click="closeBookingModal"
              class="p-2 hover:bg-gray-100 rounded-full transition-colors"
            >
              <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <!-- Modal Content -->
          <div class="p-6">
            <!-- Car Info -->
            <div v-if="selectedCar" class="mb-6 p-4 bg-gray-50 rounded-xl">
              <div class="flex items-center gap-4">
                <img 
                  :src="getCarImages(selectedCar)[0]" 
                  :alt="`${selectedCar.make} ${selectedCar.model}`"
                  class="w-20 h-16 object-contain rounded-lg bg-white"
                />
                <div class="flex-1">
                  <h4 class="font-bold text-lg text-gray-900">{{ selectedCar.make }} {{ selectedCar.model }}</h4>
                  <p class="text-sm text-gray-600">{{ selectedCar.year }} • {{ selectedCar.category }}</p>
                  <p class="text-lg font-bold text-blue-600">${{ selectedCar.rental_price_per_day }}/day</p>
                </div>
              </div>
            </div>
            
            <!-- Booking Form -->
            <form @submit.prevent="submitBooking" class="space-y-4">
              <!-- Date Range -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                  <input 
                    type="date" 
                    v-model="bookingForm.start_date"
                    @change="calculateBookingPrice"
                    :min="today"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                  <input 
                    type="date" 
                    v-model="bookingForm.end_date"
                    @change="calculateBookingPrice"
                    :min="bookingForm.start_date || today"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>
              
              <!-- Availability Check -->
              <div v-if="bookingForm.start_date && bookingForm.end_date" class="mb-4">
                <div v-if="availabilityChecking" class="flex items-center gap-2 text-blue-600">
                  <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                  </svg>
                  Checking availability...
                </div>
                <div v-else-if="availabilityStatus === 'available'" class="flex items-center gap-2 text-green-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  {{ availabilityMessage }}
                </div>
                <div v-else-if="availabilityStatus === 'unavailable'" class="flex items-center gap-2 text-red-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                  {{ availabilityMessage }}
                </div>
                <div v-else-if="availabilityStatus === 'error'" class="flex items-center gap-2 text-orange-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>
                  {{ availabilityMessage }}
                </div>
              </div>
              
              <!-- Pricing Summary -->
              <div v-if="bookingForm.days > 0 && availabilityStatus === 'available'" class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h5 class="font-medium text-gray-900 mb-3">Booking Summary</h5>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Duration:</span>
                    <span class="font-medium">{{ bookingForm.days }} day{{ bookingForm.days > 1 ? 's' : '' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Rate per day:</span>
                    <span class="font-medium">${{ selectedCar?.rental_price_per_day }}</span>
                  </div>
                  <div class="flex justify-between font-bold text-lg pt-2 border-t border-blue-200">
                    <span>Total:</span>
                    <span class="text-blue-600">${{ bookingForm.total_price.toFixed(2) }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Submit Button -->
              <div class="flex gap-3 pt-4">
                <button 
                  type="button"
                  @click="closeBookingModal"
                  class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                >
                  Cancel
                </button>
                <button 
                  type="submit"
                  :disabled="bookingLoading || bookingForm.days <= 0 || !page.props.auth.user || availabilityStatus !== 'available' || availabilityChecking"
                  class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-2 rounded-lg font-bold hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="bookingLoading" class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Booking...
                  </span>
                  <span v-else-if="!page.props.auth.user">
                    Login to Book
                  </span>
                  <span v-else-if="availabilityStatus === 'unavailable'">
                    Not Available
                  </span>
                  <span v-else-if="availabilityChecking">
                    Checking...
                  </span>
                  <span v-else>
                    Confirm Booking
                  </span>
                </button>
              </div>
              
              <!-- Login prompt -->
              <div v-if="!page.props.auth.user" class="text-center text-sm text-gray-600 mt-3">
                <Link href="/login" class="text-blue-600 hover:text-blue-700 font-medium">Login</Link> or 
                <Link href="/register" class="text-blue-600 hover:text-blue-700 font-medium">Register</Link> to make a reservation
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template> 