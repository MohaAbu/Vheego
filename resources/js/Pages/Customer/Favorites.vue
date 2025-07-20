<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import FavoriteButton from '@/Components/FavoriteButton.vue';
import { Link } from '@inertiajs/vue3';

const favorites = ref([]);
const loading = ref(true);

const fetchFavorites = async () => {
    loading.value = true;
    try {
        const response = await fetch('/favorites');
        if (response.ok) {
            favorites.value = await response.json();
        } else {
            favorites.value = [];
        }
    } catch (e) {
        favorites.value = [];
    }
    loading.value = false;
};

onMounted(fetchFavorites);

function handleFavoriteUpdate(carId, isFavorited) {
    if (!isFavorited) {
        favorites.value = favorites.value.filter(car => car.id !== carId);
    }
}
</script>

<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-6">My Favorites</h2>
            <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>
            <div v-else>
                <div v-if="favorites.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="car in favorites" :key="car.id" class="bg-white rounded shadow p-4 flex flex-col relative">
                        <div class="absolute top-2 right-2 z-10">
                            <FavoriteButton :car-id="car.id" :is-favorited="true" @update="isFav => handleFavoriteUpdate(car.id, isFav)" />
                        </div>
                        <img v-if="car.images && car.images.length" :src="`/storage/${car.images.find(img => img.is_primary)?.image_path || car.images[0].image_path}`" alt="Car Image" class="h-40 w-full object-cover rounded mb-2" />
                        <div class="font-bold text-lg mb-1">{{ car.make }} {{ car.model }} ({{ car.year }})</div>
                        <div class="text-sm text-gray-600 mb-1">${{ car.rental_price_per_day }} / day</div>
                        <Link :href="route('cars.show', car.id)" class="text-blue-600 hover:underline text-xs mt-2">View Details</Link>
                    </div>
                </div>
                <div v-else class="text-center py-16">
                    <div class="text-2xl text-gray-400 mb-4">You have no favorites yet.</div>
                    <Link :href="route('cars.index')" class="bg-indigo-600 text-white px-6 py-3 rounded hover:bg-indigo-700 text-lg">
                        Browse Cars
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template> 