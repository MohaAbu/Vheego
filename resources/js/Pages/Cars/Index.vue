<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import FavoriteButton from '@/Components/FavoriteButton.vue';

const props = defineProps({
    cars: Array,
    stats: Object,
});

const hasCars = computed(() => props.cars && props.cars.length > 0);

const deleteCar = (carId) => {
    if (confirm('Are you sure you want to delete this car?')) {
        router.delete(route('cars.destroy', carId));
    }
};

function capitalize(value) {
    if (!value) return '';
    return value.charAt(0).toUpperCase() + value.slice(1);
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="My Cars" />
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">My Cars</h2>
                    <Link :href="route('cars.create')" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        + Add New Car
                    </Link>
                </div>
                <div class="mb-4 flex gap-6">
                    <div class="bg-white p-4 rounded shadow text-center">
                        <div class="text-lg font-semibold">Total Cars</div>
                        <div class="text-2xl">{{ stats.total }}</div>
                    </div>
                    <div class="bg-white p-4 rounded shadow text-center">
                        <div class="text-lg font-semibold">Active Cars</div>
                        <div class="text-2xl">{{ stats.active }}</div>
                    </div>
                </div>
                <div v-if="hasCars" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="car in cars" :key="car.id" class="bg-white rounded shadow p-4 flex flex-col relative">
                        <div class="absolute top-2 right-2 z-10">
                            <FavoriteButton :car-id="car.id" :is-favorited="car.is_favorited" />
                        </div>
                        <img v-if="car.images && car.images.length" :src="`/storage/${car.images.find(img => img.is_primary)?.image_path || car.images[0].image_path}`" alt="Car Image" class="h-40 w-full object-cover rounded mb-2" />
                        <div class="font-bold text-lg mb-1 flex items-center gap-2">
                            {{ car.make }} {{ car.model }} ({{ car.year }})
                        </div>
                        <div class="text-sm text-gray-600 mb-1">{{ capitalize(car.category) }}</div>
                        <div class="text-sm text-gray-600 mb-1">{{ car.license_plate }}</div>
                        <div class="text-sm text-gray-600 mb-1">${{ car.rental_price_per_day }} / day</div>
                        <div class="mt-2">
                            <span v-if="car.is_active" class="inline-block px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Active</span>
                            <span v-else class="inline-block px-2 py-1 bg-gray-200 text-gray-700 rounded text-xs">Inactive</span>
                        </div>
                        <div class="flex gap-2 mt-4">
                            <Link v-if="car && car.id" :href="route('cars.edit', car.id)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 text-xs">Edit</Link>
                            <Link :href="route('cars.show', car.id)" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-xs">Details</Link>
                            <button @click="deleteCar(car.id)" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-xs">Delete</button>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-16">
                    <div class="text-2xl text-gray-400 mb-4">You have no cars yet.</div>
                    <Link :href="route('cars.create')" class="bg-indigo-600 text-white px-6 py-3 rounded hover:bg-indigo-700 text-lg">
                        + Add Your First Car
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
export default {
    filters: {
        capitalize(value) {
            if (!value) return '';
            return value.charAt(0).toUpperCase() + value.slice(1);
        },
    },
};
</script> 