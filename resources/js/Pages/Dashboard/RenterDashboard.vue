<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
  user: Object,
  agency: Object,
  stats: {
    type: Object,
    default: () => ({
      totalCars: 0,
      activeCars: 0,
      totalBookings: 0,
      monthlyRevenue: 0,
      pendingBookings: 0,
      completedBookings: 0
    })
  },
  recentBookings: {
    type: Array,
    default: () => []
  },
  topCars: {
    type: Array,
    default: () => []
  }
});

// Computed properties for stats with fallbacks
const dashboardStats = computed(() => ({
  totalCars: props.stats?.totalCars || 0,
  activeCars: props.stats?.activeCars || 0,
  totalBookings: props.stats?.totalBookings || 0,
  monthlyRevenue: props.stats?.monthlyRevenue || 0,
  pendingBookings: props.stats?.pendingBookings || 0,
  completedBookings: props.stats?.completedBookings || 0
}));

const getCarImage = (car) => {
  if (car?.images && car.images.length > 0) {
    const primaryImage = car.images.find(img => img.is_primary);
    const imagePath = primaryImage ? primaryImage.image_path : car.images[0].image_path;
    return `/storage/${imagePath}`;
  }
  return '/images/car-placeholder.png';
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};

const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-amber-100 text-amber-800',
    confirmed: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
};

// Use real data from props
const displayBookings = computed(() => props.recentBookings || []);
const displayTopCars = computed(() => props.topCars || []);
</script>

<template>
  <Head title="Agency Dashboard" />
  
  <DashboardLayout role="renter">
    <template #header>
      <div class="flex items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold text-blue-800 mb-1">
            {{ agency?.name || 'Agency Dashboard' }}
          </h1>
          <p class="text-slate-600">
            {{ agency ? 'Monitor your agency performance and manage operations' : 'Set up your agency to get started' }}
          </p>
        </div>
        <div v-if="agency" class="ml-auto">
          <div class="flex items-center gap-2 px-4 py-2 bg-green-100 text-green-800 rounded-lg">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-semibold">Verified Agency</span>
          </div>
        </div>
      </div>
    </template>

    <!-- Quick Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
      <!-- Total Cars -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600">Fleet Size</p>
            <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ dashboardStats.totalCars }}</p>
            <p class="text-xs text-green-600 mt-1">{{ dashboardStats.activeCars }} active</p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Monthly Revenue -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600">Agency Revenue</p>
            <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ formatCurrency(dashboardStats.monthlyRevenue) }}</p>
            <p class="text-xs text-slate-500 mt-1">This month's earnings</p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Total Bookings -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600">Reservations</p>
            <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ dashboardStats.totalBookings }}</p>
            <p class="text-xs text-blue-600 mt-1">{{ dashboardStats.pendingBookings }} pending</p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Completion Rate -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600">Completion Rate</p>
            <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ dashboardStats.totalBookings > 0 ? Math.round((dashboardStats.completedBookings / dashboardStats.totalBookings) * 100) : 0 }}%</p>
            <p class="text-xs text-green-600 mt-1">{{ dashboardStats.completedBookings }} completed</p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 sm:gap-8">
      <!-- Recent Bookings -->
      <div class="xl:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
          <div class="p-4 sm:p-6 border-b border-slate-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <h3 class="text-lg font-bold text-slate-800">Recent Reservations</h3>
                <p class="text-sm text-slate-600">Latest customer bookings for your fleet</p>
              </div>
              <Link
                href="/agency/bookings"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-lg hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200 shadow-sm hover:shadow-md"
              >
                Manage All
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </Link>
            </div>
          </div>
          <div class="p-4 sm:p-6">
            <div class="space-y-4">
              <div
                v-for="booking in displayBookings"
                :key="booking.id"
                class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors duration-200"
              >
                <div class="flex-1 mb-3 sm:mb-0">
                  <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                      {{ booking.customer_name.charAt(0) }}
                    </div>
                    <div>
                      <h4 class="font-semibold text-slate-800">{{ booking.customer_name }}</h4>
                      <p class="text-sm text-slate-600">{{ booking.car.make }} {{ booking.car.model }} {{ booking.car.year }}</p>
                    </div>
                  </div>
                  <div class="text-sm text-slate-500">
                    {{ new Date(booking.start_date).toLocaleDateString() }} - {{ new Date(booking.end_date).toLocaleDateString() }}
                  </div>
                </div>
                <div class="flex items-center justify-between sm:justify-end gap-4">
                  <span class="font-bold text-slate-800">{{ formatCurrency(booking.total_price) }}</span>
                  <span :class="[
                    'px-3 py-1 rounded-full text-xs font-semibold',
                    getStatusColor(booking.status)
                  ]">
                    {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
                  </span>
                </div>
              </div>
            </div>
            <div v-if="displayBookings.length === 0" class="text-center py-8">
              <div class="text-4xl mb-4">📅</div>
              <div class="text-lg font-semibold text-slate-400 mb-2">No recent bookings</div>
              <div class="text-slate-500">Your booking requests will appear here</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Performing Cars -->
      <div class="xl:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
          <div class="p-4 sm:p-6 border-b border-slate-200">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-slate-800">Top Performers</h3>
                <p class="text-sm text-slate-600">Best revenue-generating vehicles</p>
              </div>
              <Link
                href="/manage/cars"
                class="text-blue-600 hover:text-blue-700 text-sm font-semibold"
              >
                View All
              </Link>
            </div>
          </div>
          <div class="p-4 sm:p-6">
            <div class="space-y-4">
              <div
                v-for="(car, index) in displayTopCars"
                :key="car.id"
                class="flex items-center gap-4 p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors duration-200"
              >
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-gradient-to-br from-slate-200 to-slate-300 rounded-lg flex items-center justify-center">
                    <img
                      :src="getCarImage(car)"
                      :alt="`${car.make} ${car.model}`"
                      class="w-8 h-8 object-contain"
                      @error="$event.target.style.display = 'none'"
                    />
                    <svg v-if="!getCarImage(car)" class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 19h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2">
                    <span class="w-6 h-6 bg-gradient-to-r from-amber-400 to-amber-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                      {{ index + 1 }}
                    </span>
                    <h4 class="font-semibold text-slate-800 truncate">{{ car.make }} {{ car.model }}</h4>
                  </div>
                  <div class="flex items-center justify-between mt-1">
                    <span class="text-sm text-slate-500">{{ car.bookings_count }} bookings</span>
                    <span class="text-sm font-semibold text-green-600">{{ formatCurrency(car.revenue) }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="displayTopCars.length === 0" class="text-center py-8">
              <div class="text-4xl mb-4">🚗</div>
              <div class="text-lg font-semibold text-slate-400 mb-2">No cars yet</div>
              <div class="text-slate-500">Add your first car to get started</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8">
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6">
        <div class="mb-4">
          <h3 class="text-lg font-bold text-slate-800 mb-2">Agency Management</h3>
          <p class="text-sm text-slate-600">Quick access to key agency operations</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <Link
            href="/manage/cars/create"
            class="flex flex-col items-center p-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl hover:from-blue-100 hover:to-indigo-100 transition-all duration-200 group"
          >
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
            </div>
            <span class="font-semibold text-slate-800 text-center">Add Vehicle</span>
            <span class="text-sm text-slate-600 text-center mt-1">Expand your fleet</span>
          </Link>

          <Link
            href="/agency/bookings"
            class="flex flex-col items-center p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl hover:from-green-100 hover:to-emerald-100 transition-all duration-200 group"
          >
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </div>
            <span class="font-semibold text-slate-800 text-center">Reservations</span>
            <span class="text-sm text-slate-600 text-center mt-1">Manage bookings</span>
          </Link>

          <Link
            :href="(user?.agency && user.agency.verification_status === 'approved') ? '/agency/settings' : '/agency/status'"
            class="flex flex-col items-center p-6 bg-gradient-to-br from-purple-50 to-violet-50 rounded-xl hover:from-purple-100 hover:to-violet-100 transition-all duration-200 group"
          >
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 19h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2z"/>
              </svg>
            </div>
            <span class="font-semibold text-slate-800 text-center">Agency Profile</span>
            <span class="text-sm text-slate-600 text-center mt-1">Edit information</span>
          </Link>

          <Link
            href="/agency/analytics"
            class="flex flex-col items-center p-6 bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl hover:from-amber-100 hover:to-orange-100 transition-all duration-200 group"
          >
            <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-200">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
            </div>
            <span class="font-semibold text-slate-800 text-center">Analytics</span>
            <span class="text-sm text-slate-600 text-center mt-1">View reports</span>
          </Link>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>