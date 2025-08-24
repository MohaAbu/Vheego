<script setup>
import { ref, onMounted } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import FavoriteButton from '@/Components/FavoriteButton.vue';
import { Link } from '@inertiajs/vue3';
import NavBar from '../../Components/NavBar.vue';
import Footer from '../../Components/Footer.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
  favorites: Array
});

const favorites = ref(props.favorites || []);


function handleFavoriteUpdate(carId, isFavorited) {
    if (!isFavorited) {
        favorites.value = favorites.value.filter(car => car.id !== carId);
    }
}
</script>

<template>
  <Head title="My Favorites" />
  <div class="min-h-screen flex flex-col bg-gray-50">
    <NavBar :user="page.props.auth.user" />
    
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">My Favorites</h1>
        <p class="text-gray-600">Cars you've saved for later</p>
      </div>
      <div v-if="favorites.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="car in favorites" :key="car.id" class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 relative">
          <!-- Favorite Button -->
          <div class="absolute top-3 right-3 z-10">
            <FavoriteButton :car-id="car.id" :is-favorited="true" @update="isFav => handleFavoriteUpdate(car.id, isFav)" />
          </div>
          
          <!-- Car Image -->
          <div class="relative bg-gradient-to-br from-gray-50 to-gray-100 p-4">
            <img 
              v-if="car.images && car.images.length" 
              :src="car.images.find(img => img.is_primary)?.image_path ? `/storage/${car.images.find(img => img.is_primary).image_path}` : `/storage/${car.images[0].image_path}`" 
              :alt="`${car.make} ${car.model}`" 
              class="h-40 w-full object-contain rounded-lg group-hover:scale-105 transition-transform duration-300" 
            />
            <div v-else class="h-40 w-full bg-gray-200 rounded-lg flex items-center justify-center">
              <span class="text-gray-400">No Image</span>
            </div>
            
            <!-- Price Badge -->
            <div class="absolute bottom-3 left-3 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold shadow-lg">
              ${{ car.rental_price_per_day }}/day
            </div>
          </div>
          
          <!-- Content -->
          <div class="p-4">
            <h3 class="font-bold text-lg text-gray-900 mb-2">{{ car.make }} {{ car.model }}</h3>
            <p class="text-sm text-gray-600 mb-3">{{ car.year }} • {{ car.category }}</p>
            
            <div class="flex items-center gap-2 text-gray-600 mb-4">
              <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"></path>
              </svg>
              <span class="text-sm">{{ car.agency?.name || 'N/A' }}</span>
            </div>
            
            <Link 
              :href="`/cars/${car.id}`" 
              class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-2 rounded-xl font-medium text-center hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 inline-block"
            >
              View Details
            </Link>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-16">
        <div class="max-w-md mx-auto">
          <div class="text-6xl mb-4">💙</div>
          <h2 class="text-2xl font-bold text-gray-400 mb-3">No favorites yet</h2>
          <p class="text-gray-500 mb-6">Start browsing cars and save your favorites here</p>
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
  </div>
</template> 