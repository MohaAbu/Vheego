<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
  agency: {
    type: Object,
    default: () => ({})
  },
  reservations: {
    type: Object,
    default: () => ({ data: [], links: [], from: 0, to: 0, total: 0 })
  },
  stats: {
    type: Object,
    default: () => ({ total: 0, pending: 0, confirmed: 0, completed: 0, monthlyRevenue: 0 })
  },
  filters: {
    type: Object,
    default: () => ({ status: 'all', date_from: '', date_to: '' })
  },
});

const statusFilter = ref(props.filters?.status || 'all');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');

const applyFilters = () => {
  router.get('/agency/bookings', {
    status: statusFilter.value,
    date_from: dateFrom.value,
    date_to: dateTo.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  statusFilter.value = 'all';
  dateFrom.value = '';
  dateTo.value = '';
  router.get('/agency/bookings');
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

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const updateReservationStatus = (reservationId, newStatus) => {
  if (confirm(`Are you sure you want to ${newStatus} this reservation?`)) {
    router.patch(`/reservations/${reservationId}/status`, {
      status: newStatus
    }, {
      preserveState: true,
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <Head title="Agency Reservations" />
  
  <DashboardLayout role="renter">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-blue-800">Reservations Management</h1>
          <p class="text-slate-600">Manage customer bookings for {{ agency?.name }}</p>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 bg-blue-100 text-blue-800 rounded-lg">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          <span class="text-sm font-semibold">{{ stats?.total || 0 }} Total Reservations</span>
        </div>
      </div>
    </template>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
      <!-- Total Reservations -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600">Total</p>
            <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ stats?.total || 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Pending -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600">Pending</p>
            <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ stats?.pending || 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Confirmed -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600">Confirmed</p>
            <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ stats?.confirmed || 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Monthly Revenue -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600">This Month</p>
            <p class="text-2xl sm:text-3xl font-bold text-slate-800">{{ formatCurrency(stats?.monthlyRevenue || 0) }}</p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 mb-6">
      <div class="flex flex-col lg:flex-row gap-4">
        <div class="flex-1">
          <label for="status" class="block text-sm font-medium text-slate-700 mb-2">Filter by Status</label>
          <select
            id="status"
            v-model="statusFilter"
            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
          >
            <option value="all">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        
        <div class="flex-1">
          <label for="date_from" class="block text-sm font-medium text-slate-700 mb-2">From Date</label>
          <input
            id="date_from"
            v-model="dateFrom"
            type="date"
            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
          />
        </div>
        
        <div class="flex-1">
          <label for="date_to" class="block text-sm font-medium text-slate-700 mb-2">To Date</label>
          <input
            id="date_to"
            v-model="dateTo"
            type="date"
            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
          />
        </div>
        
        <div class="flex items-end gap-2">
          <button
            @click="applyFilters"
            class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors"
          >
            Apply
          </button>
          <button
            @click="clearFilters"
            class="px-6 py-3 bg-slate-600 text-white font-semibold rounded-lg hover:bg-slate-700 transition-colors"
          >
            Clear
          </button>
        </div>
      </div>
    </div>

    <!-- Reservations List -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
      <div class="p-4 sm:p-6 border-b border-slate-200">
        <h3 class="text-lg font-bold text-slate-800">Reservation Details</h3>
        <p class="text-sm text-slate-600">Manage and track all customer bookings</p>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Vehicle</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Dates</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-slate-200">
            <tr v-for="reservation in (reservations?.data || [])" :key="reservation.id" class="hover:bg-slate-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                    {{ reservation.customer?.name?.charAt(0) || 'C' }}
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-slate-900">{{ reservation.customer?.name || 'Unknown' }}</div>
                    <div class="text-sm text-slate-500">{{ reservation.customer?.email || 'No email' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-slate-900">{{ reservation.car?.make }} {{ reservation.car?.model }}</div>
                <div class="text-sm text-slate-500">{{ reservation.car?.year }} • {{ reservation.car?.license_plate }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-900">{{ formatDate(reservation.start_date) }}</div>
                <div class="text-sm text-slate-500">to {{ formatDate(reservation.end_date) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                {{ formatCurrency(reservation.total_price) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'px-3 py-1 rounded-full text-xs font-semibold',
                  getStatusColor(reservation.status)
                ]">
                  {{ reservation.status.charAt(0).toUpperCase() + reservation.status.slice(1) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button
                  v-if="reservation.status === 'pending'"
                  @click="updateReservationStatus(reservation.id, 'confirmed')"
                  class="text-blue-600 hover:text-blue-700 font-semibold"
                >
                  Confirm
                </button>
                <button
                  v-if="reservation.status === 'confirmed'"
                  @click="updateReservationStatus(reservation.id, 'completed')"
                  class="text-green-600 hover:text-green-700 font-semibold"
                >
                  Complete
                </button>
                <button
                  v-if="['pending', 'confirmed'].includes(reservation.status)"
                  @click="updateReservationStatus(reservation.id, 'cancelled')"
                  class="text-red-600 hover:text-red-700 font-semibold"
                >
                  Cancel
                </button>
                <Link
                  :href="`/reservations/${reservation.id}`"
                  class="text-slate-600 hover:text-slate-700 font-semibold"
                >
                  View
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="reservations?.links" class="px-6 py-4 border-t border-slate-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-slate-600">
            Showing {{ reservations?.from || 0 }} to {{ reservations?.to || 0 }} of {{ reservations?.total || 0 }} results
          </div>
          <div class="flex space-x-2">
            <template v-for="link in (reservations?.links || [])" :key="link.label">
              <Link
                v-if="link.url"
                :href="link.url"
                :class="[
                  'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                  link.active
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-300'
                ]"
                v-html="link.label"
              />
              <span
                v-else
                :class="[
                  'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                  'bg-slate-100 text-slate-400 cursor-not-allowed'
                ]"
                v-html="link.label"
              />
            </template>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!reservations?.data || reservations.data.length === 0" class="text-center py-12">
        <div class="text-4xl mb-4">📅</div>
        <div class="text-xl font-semibold text-slate-400 mb-2">No reservations found</div>
        <div class="text-slate-500">Customer bookings will appear here when they reserve your vehicles</div>
      </div>
    </div>
  </DashboardLayout>
</template>