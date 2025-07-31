<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    cars: Array,
    stats: Object,
});

const hasCars = computed(() => props.cars && props.cars.length > 0);
const searchQuery = ref('');
const filterStatus = ref('all');

const filteredCars = computed(() => {
    if (!props.cars) return [];
    
    return props.cars.filter(car => {
        const matchesSearch = !searchQuery.value || 
            car.make.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            car.model.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            car.license_plate.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchesFilter = filterStatus.value === 'all' || 
            (filterStatus.value === 'active' && car.is_active) ||
            (filterStatus.value === 'inactive' && !car.is_active);
        
        return matchesSearch && matchesFilter;
    });
});

const deleteCar = (carId) => {
    if (confirm('Are you sure you want to delete this car? This action cannot be undone.')) {
        router.delete(route('cars.destroy', carId));
    }
};

function capitalize(value) {
    if (!value) return '';
    return value.charAt(0).toUpperCase() + value.slice(1);
}

const getCarImage = (car) => {
    if (car.images && car.images.length > 0) {
        const primaryImage = car.images.find(img => img.is_primary);
        const imagePath = primaryImage ? primaryImage.image_path : car.images[0].image_path;
        return `/storage/${imagePath}`;
    }
    return '/images/car-placeholder.png';
};

const getStatusColor = (isActive) => {
    return isActive 
        ? 'bg-green-100 text-green-800' 
        : 'bg-red-100 text-red-800';
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};
</script>

<template>
    <Head title="Fleet Management" />
    
    <DashboardLayout role="renter">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-blue-800">Fleet Management</h1>
                    <p class="text-slate-600">Manage your agency's vehicle fleet and availability</p>
                </div>
                <Link 
                    :href="route('cars.create')" 
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add Vehicle
                </Link>
            </div>
        </template>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Fleet Size</p>
                        <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ stats?.total || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 19h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Available</p>
                        <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ stats?.active || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Avg. Daily Rate</p>
                        <p class="text-2xl sm:text-3xl font-bold text-slate-800">
                            {{ cars?.length ? formatCurrency(cars.reduce((sum, car) => sum + car.rental_price_per_day, 0) / cars.length) : '$0' }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div v-if="hasCars" class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-slate-700 mb-2">Search Fleet</label>
                    <div class="relative">
                        <input
                            id="search"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by make, model, or license plate..."
                            class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        />
                        <svg class="absolute left-3 top-3.5 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <label for="filter" class="block text-sm font-medium text-slate-700 mb-2">Filter by Status</label>
                    <select
                        id="filter"
                        v-model="filterStatus"
                        class="px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    >
                        <option value="all">All Vehicles</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Inactive Only</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Cars Grid -->
        <div v-if="hasCars && filteredCars.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
                v-for="car in filteredCars" 
                :key="car.id" 
                class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-lg transition-all duration-300 group"
            >
                <!-- Car Image -->
                <div class="relative h-48 bg-gradient-to-br from-slate-100 to-slate-200">
                    <img 
                        :src="getCarImage(car)" 
                        :alt="`${car.make} ${car.model}`" 
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" 
                    />
                    <div class="absolute top-3 right-3">
                        <span :class="[
                            'px-3 py-1 rounded-full text-xs font-semibold',
                            getStatusColor(car.is_active)
                        ]">
                            {{ car.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-lg">
                        <span class="text-sm font-bold text-slate-800">{{ formatCurrency(car.rental_price_per_day) }}/day</span>
                    </div>
                </div>
                
                <!-- Car Details -->
                <div class="p-6">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-slate-800 mb-1">
                            {{ car.make }} {{ car.model }}
                        </h3>
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <span>{{ car.year }}</span>
                            <span>•</span>
                            <span>{{ capitalize(car.category) }}</span>
                        </div>
                        <div class="text-sm text-slate-500 mt-1">{{ car.license_plate }}</div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <Link 
                            :href="route('cars.show', car.id)" 
                            class="flex-1 bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 px-4 py-2 rounded-lg text-center font-semibold hover:from-blue-100 hover:to-indigo-100 transition-all duration-200"
                        >
                            View
                        </Link>
                        <Link 
                            :href="route('cars.edit', car.id)" 
                            class="flex-1 bg-gradient-to-r from-amber-50 to-orange-50 text-amber-700 px-4 py-2 rounded-lg text-center font-semibold hover:from-amber-100 hover:to-orange-100 transition-all duration-200"
                        >
                            Edit
                        </Link>
                        <button 
                            @click="deleteCar(car.id)" 
                            class="px-4 py-2 bg-gradient-to-r from-red-50 to-rose-50 text-red-700 rounded-lg font-semibold hover:from-red-100 hover:to-rose-100 transition-all duration-200"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Results State -->
        <div v-else-if="hasCars && filteredCars.length === 0" class="text-center py-12">
            <div class="text-4xl mb-4">🔍</div>
            <div class="text-xl font-semibold text-slate-400 mb-2">No vehicles found</div>
            <div class="text-slate-500">Try adjusting your search or filter criteria</div>
            <button 
                @click="searchQuery = ''; filterStatus = 'all'" 
                class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
                Clear Filters
            </button>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16">
            <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 19h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-slate-400 mb-4">No vehicles in your fleet yet</div>
            <div class="text-slate-500 mb-8 max-w-md mx-auto">
                Start building your agency's fleet by adding your first vehicle to the platform
            </div>
            <Link 
                :href="route('cars.create')" 
                class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Your First Vehicle
            </Link>
        </div>
    </DashboardLayout>
</template>