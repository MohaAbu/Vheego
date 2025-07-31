<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  agency: {
    type: Object,
    default: () => ({})
  },
  revenue: {
    type: Object,
    default: () => ({ current_month: 0, last_month: 0, growth: 0, monthly_trend: [] })
  },
  top_cars: {
    type: Array,
    default: () => []
  },
  booking_stats: {
    type: Object,
    default: () => ({ pending: 0, confirmed: 0, completed: 0, cancelled: 0 })
  },
  fleet_size: {
    type: Number,
    default: 0
  },
  active_cars: {
    type: Number,
    default: 0
  },
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};

const growthIndicator = computed(() => {
  const growth = props.revenue?.growth || 0;
  return {
    isPositive: growth >= 0,
    percentage: Math.abs(growth).toFixed(1),
    text: growth >= 0 ? 'increase' : 'decrease'
  };
});
</script>

<template>
  <Head title="Agency Analytics" />
  
  <DashboardLayout role="renter">
    <template #header>
      <div>
        <h1 class="text-3xl font-bold text-blue-800">Analytics & Reports</h1>
        <p class="text-slate-600">Business performance insights for {{ agency?.name }}</p>
      </div>
    </template>

    <!-- Revenue Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-slate-800">Current Month Revenue</h3>
          <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
            </svg>
          </div>
        </div>
        <div class="text-3xl font-bold text-slate-800 mb-2">
          {{ formatCurrency(revenue?.current_month || 0) }}
        </div>
        <div class="flex items-center gap-2">
          <span :class="[
            'text-sm font-medium',
            growthIndicator.isPositive ? 'text-green-600' : 'text-red-600'
          ]">
            {{ growthIndicator.isPositive ? '↗' : '↘' }} {{ growthIndicator.percentage }}%
          </span>
          <span class="text-sm text-slate-500">vs last month</span>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-slate-800">Fleet Utilization</h3>
          <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 19h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
          </div>
        </div>
        <div class="text-3xl font-bold text-slate-800 mb-2">
          {{ fleet_size > 0 ? Math.round((active_cars / fleet_size) * 100) : 0 }}%
        </div>
        <div class="text-sm text-slate-500">
          {{ active_cars }} of {{ fleet_size }} vehicles active
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-slate-800">Total Bookings</h3>
          <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
        </div>
        <div class="text-3xl font-bold text-slate-800 mb-2">
          {{ Object.values(booking_stats || {}).reduce((sum, count) => sum + count, 0) }}
        </div>
        <div class="text-sm text-slate-500">All time reservations</div>
      </div>
    </div>

    <!-- Charts and Detailed Analytics -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
      <!-- Revenue Trend -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-6">Revenue Trend (Last 6 Months)</h3>
        <div class="space-y-4">
          <div v-for="month in revenue?.monthly_trend || []" :key="month.month" class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-600">{{ month.month }}</span>
            <div class="flex items-center gap-3">
              <div class="w-32 bg-slate-200 rounded-full h-2">
                <div 
                  class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full"
                  :style="{ width: revenue?.monthly_trend ? (month.revenue / Math.max(...revenue.monthly_trend.map(m => m.revenue)) * 100) + '%' : '0%' }"
                ></div>
              </div>
              <span class="text-sm font-semibold text-slate-800 w-20 text-right">{{ formatCurrency(month.revenue) }}</span>
            </div>
          </div>
        </div>
        <div v-if="!revenue?.monthly_trend || revenue.monthly_trend.length === 0" class="text-center py-8">
          <div class="text-slate-400">No revenue data available yet</div>
        </div>
      </div>

      <!-- Top Performing Cars -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-6">Top Performing Vehicles</h3>
        <div class="space-y-4">
          <div v-for="(car, index) in top_cars" :key="car.id" class="flex items-center gap-4 p-3 bg-slate-50 rounded-lg">
            <div class="w-8 h-8 bg-gradient-to-r from-amber-400 to-amber-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
              {{ index + 1 }}
            </div>
            <div class="flex-1">
              <div class="font-semibold text-slate-800">{{ car.name }}</div>
              <div class="text-sm text-slate-500">{{ car.bookings }} bookings</div>
            </div>
            <div class="text-right">
              <div class="font-semibold text-green-600">{{ formatCurrency(car.revenue) }}</div>
            </div>
          </div>
        </div>
        <div v-if="!top_cars || top_cars.length === 0" class="text-center py-8">
          <div class="text-slate-400">No booking data available yet</div>
        </div>
      </div>

      <!-- Booking Status Distribution -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-6">Booking Status Breakdown</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
            <div class="flex items-center gap-3">
              <div class="w-4 h-4 bg-amber-500 rounded-full"></div>
              <span class="font-medium text-slate-800">Pending</span>
            </div>
            <span class="font-bold text-slate-800">{{ booking_stats?.pending || 0 }}</span>
          </div>
          
          <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
            <div class="flex items-center gap-3">
              <div class="w-4 h-4 bg-blue-500 rounded-full"></div>
              <span class="font-medium text-slate-800">Confirmed</span>
            </div>
            <span class="font-bold text-slate-800">{{ booking_stats?.confirmed || 0 }}</span>
          </div>
          
          <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
            <div class="flex items-center gap-3">
              <div class="w-4 h-4 bg-green-500 rounded-full"></div>
              <span class="font-medium text-slate-800">Completed</span>
            </div>
            <span class="font-bold text-slate-800">{{ booking_stats?.completed || 0 }}</span>
          </div>
          
          <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
            <div class="flex items-center gap-3">
              <div class="w-4 h-4 bg-red-500 rounded-full"></div>
              <span class="font-medium text-slate-800">Cancelled</span>
            </div>
            <span class="font-bold text-slate-800">{{ booking_stats?.cancelled || 0 }}</span>
          </div>
        </div>
      </div>

      <!-- Performance Insights -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-6">Performance Insights</h3>
        <div class="space-y-4">
          <div class="p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
            <div class="font-semibold text-blue-800 mb-1">Revenue Growth</div>
            <div class="text-sm text-blue-700">
              {{ growthIndicator.isPositive ? 'Great job!' : 'Need improvement' }} 
              Your revenue has {{ growthIndicator.text }}d by {{ growthIndicator.percentage }}% compared to last month.
            </div>
          </div>
          
          <div class="p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
            <div class="font-semibold text-green-800 mb-1">Fleet Utilization</div>
            <div class="text-sm text-green-700">
              {{ active_cars }} out of {{ fleet_size }} vehicles are currently active and available for booking.
            </div>
          </div>
          
          <div class="p-4 bg-amber-50 rounded-lg border-l-4 border-amber-500">
            <div class="font-semibold text-amber-800 mb-1">Pending Bookings</div>
            <div class="text-sm text-amber-700">
              You have {{ booking_stats?.pending || 0 }} pending reservations that need your attention.
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>