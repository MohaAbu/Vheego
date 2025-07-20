<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { debounce } from 'lodash';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    reservations: Object,
    stats: Object,
    filters: Object,
});

// Filter and search
const searchQuery = ref(props.filters?.search || '');
const selectedFilter = ref(props.filters?.filter || 'all');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const sortBy = ref(props.filters?.sort || 'created_at');
const sortDirection = ref(props.filters?.direction || 'desc');
const perPage = ref(props.filters?.per_page || 20);

// Modal state for filters
const showFiltersModal = ref(false);

// Modal filter values (separate from actual filters)
const modalFilters = ref({
    status: selectedFilter.value,
    dateFrom: dateFrom.value,
    dateTo: dateTo.value,
    sortBy: sortBy.value,
    sortDirection: sortDirection.value
});

// Apply filters by updating URL and making new request
const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (searchQuery.value) {
        params.set('search', searchQuery.value);
    }
    
    if (selectedFilter.value && selectedFilter.value !== 'all') {
        params.set('filter', selectedFilter.value);
    }
    
    if (dateFrom.value) {
        params.set('date_from', dateFrom.value);
    }
    
    if (dateTo.value) {
        params.set('date_to', dateTo.value);
    }
    
    if (sortBy.value !== 'created_at') {
        params.set('sort', sortBy.value);
    }
    
    if (sortDirection.value !== 'desc') {
        params.set('direction', sortDirection.value);
    }
    
    if (perPage.value !== 20) {
        params.set('per_page', perPage.value);
    }
    
    const url = route('admin.reservations') + (params.toString() ? '?' + params.toString() : '');
    router.get(url);
};

// Watch for changes and apply filters with debounce
const debouncedApplyFilters = debounce(applyFilters, 500);

// Watch search query and dates
watch(searchQuery, () => {
    debouncedApplyFilters();
});

watch(dateFrom, () => {
    applyFilters();
});

watch(dateTo, () => {
    applyFilters();
});

// Watch filter selection
watch(selectedFilter, () => {
    applyFilters();
});

// Watch sorting
watch([sortBy, sortDirection], () => {
    applyFilters();
});

// Watch per page changes
watch(perPage, () => {
    applyFilters();
});

// Use actual reservations data from backend
const displayReservations = computed(() => {
    return props.reservations?.data || [];
});

// Utility functions
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0);
};

const getStatusBadgeColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-blue-100 text-blue-800',
        active: 'bg-green-100 text-green-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const toggleSort = (column) => {
    if (sortBy.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDirection.value = 'asc';
    }
};

const getSortIcon = (column) => {
    if (sortBy.value !== column) return '↕️';
    return sortDirection.value === 'asc' ? '↑' : '↓';
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedFilter.value = 'all';
    dateFrom.value = '';
    dateTo.value = '';
    sortBy.value = 'created_at';
    sortDirection.value = 'desc';
    perPage.value = 20;
    applyFilters();
};

const openFiltersModal = () => {
    // Sync modal values with current filters
    modalFilters.value = {
        status: selectedFilter.value,
        dateFrom: dateFrom.value,
        dateTo: dateTo.value,
        sortBy: sortBy.value,
        sortDirection: sortDirection.value
    };
    showFiltersModal.value = true;
};

const applyFiltersFromModal = () => {
    // Apply modal values to actual filters
    selectedFilter.value = modalFilters.value.status;
    dateFrom.value = modalFilters.value.dateFrom;
    dateTo.value = modalFilters.value.dateTo;
    sortBy.value = modalFilters.value.sortBy;
    sortDirection.value = modalFilters.value.sortDirection;
    
    applyFilters();
    showFiltersModal.value = false;
};

const getDaysDifference = (startDate, endDate) => {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};
</script>

<template>
    <DashboardLayout role="admin">
        <Head title="Reservations Management - Admin" />
        
        <template #header>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-800 mb-1">Reservations Management</h1>
                <div class="text-slate-500 text-base">Monitor and manage all car rental reservations</div>
            </div>
        </template>

        <!-- Stats Cards - 5 then 4 layout -->
        <div class="mb-8 space-y-4">
            <!-- First row - 5 cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="text-2xl font-bold text-slate-800">{{ stats.totalReservations }}</div>
                    <div class="text-sm text-slate-500">Total</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="text-2xl font-bold text-yellow-600">{{ stats.pendingReservations }}</div>
                    <div class="text-sm text-slate-500">Pending</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="text-2xl font-bold text-blue-600">{{ stats.confirmedReservations }}</div>
                    <div class="text-sm text-slate-500">Confirmed</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="text-2xl font-bold text-green-600">{{ stats.activeReservations }}</div>
                    <div class="text-sm text-slate-500">Active</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="text-2xl font-bold text-green-600">{{ stats.completedReservations }}</div>
                    <div class="text-sm text-slate-500">Completed</div>
                </div>
            </div>
            
            <!-- Second row - 4 cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="text-2xl font-bold text-red-600">{{ stats.cancelledReservations }}</div>
                    <div class="text-sm text-slate-500">Cancelled</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="text-2xl font-bold text-purple-600">{{ formatCurrency(stats.totalRevenue) }}</div>
                    <div class="text-sm text-slate-500">Total Revenue</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="text-2xl font-bold text-indigo-600">{{ formatCurrency(stats.monthlyRevenue) }}</div>
                    <div class="text-sm text-slate-500">This Month</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="text-2xl font-bold text-teal-600">{{ ((stats.completedReservations / Math.max(stats.totalReservations, 1)) * 100).toFixed(1) }}%</div>
                    <div class="text-sm text-slate-500">Success Rate</div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Controls -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search by customer, car, or agency..."
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <div class="flex gap-3">
                    <button @click="openFiltersModal"
                            class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-filter"></i>
                        Filters
                        <span v-if="selectedFilter !== 'all' || dateFrom || dateTo" 
                              class="ml-1 px-2 py-0.5 bg-blue-800 text-xs rounded-full">
                            {{ [selectedFilter !== 'all' ? 1 : 0, dateFrom ? 1 : 0, dateTo ? 1 : 0].reduce((a, b) => a + b, 0) }}
                        </span>
                    </button>
                    <button @click="clearFilters"
                            class="px-4 py-2 bg-slate-600 text-white text-sm rounded-md hover:bg-slate-700 transition-colors">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Reservations Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-800">
                    Reservations ({{ displayReservations.length }})
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                <button @click="toggleSort('created_at')" class="flex items-center gap-1 hover:text-slate-700">
                                    Reservation {{ getSortIcon('created_at') }}
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Customer & Car
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                <button @click="toggleSort('start_date')" class="flex items-center gap-1 hover:text-slate-700">
                                    Rental Period {{ getSortIcon('start_date') }}
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                <button @click="toggleSort('total_price')" class="flex items-center gap-1 hover:text-slate-700">
                                    Price {{ getSortIcon('total_price') }}
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                <button @click="toggleSort('status')" class="flex items-center gap-1 hover:text-slate-700">
                                    Status {{ getSortIcon('status') }}
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <tr v-for="reservation in displayReservations" :key="reservation.id" class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">#{{ reservation.id }}</div>
                                <div class="text-sm text-slate-500">{{ formatDateTime(reservation.created_at) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">{{ reservation.customer?.name }}</div>
                                <div class="text-sm text-slate-500">{{ reservation.customer?.email }}</div>
                                <div class="text-xs text-slate-400 mt-1">
                                    {{ reservation.car?.make }} {{ reservation.car?.model }} ({{ reservation.car?.year }})
                                </div>
                                <div class="text-xs text-slate-400">Agency: {{ reservation.car?.agency?.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">
                                    {{ formatDate(reservation.start_date) }}
                                </div>
                                <div class="text-sm text-slate-500">
                                    to {{ formatDate(reservation.end_date) }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ getDaysDifference(reservation.start_date, reservation.end_date) }} days
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900">
                                    {{ formatCurrency(reservation.total_price) }}
                                </div>
                                <div v-if="reservation.car?.rental_price_per_day" class="text-xs text-slate-500">
                                    {{ formatCurrency(reservation.car.rental_price_per_day) }}/day
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    getStatusBadgeColor(reservation.status)
                                ]">
                                    {{ reservation.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <Link :href="route('reservations.show', reservation.id)"
                                          class="px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors">
                                        View
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="reservations.links" class="px-6 py-4 border-t border-slate-200">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="text-sm text-slate-500">
                            Showing {{ reservations.from }} to {{ reservations.to }} of {{ reservations.total }} results
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-slate-500">Show:</label>
                            <select v-model="perPage" 
                                    class="px-3 py-1 text-sm border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="text-sm text-slate-500">per page</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <template v-for="link in reservations.links" :key="link.label">
                            <button v-if="link.url" 
                                    @click="router.get(link.url)"
                                    :class="[
                                        'px-3 py-1 text-sm rounded-md transition-colors',
                                        link.active 
                                            ? 'bg-blue-600 text-white' 
                                            : 'bg-slate-200 text-slate-700 hover:bg-slate-300'
                                    ]"
                                    v-html="link.label">
                            </button>
                            <span v-else 
                                  class="px-3 py-1 text-sm text-slate-400"
                                  v-html="link.label">
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Modal -->
        <Modal :show="showFiltersModal" @close="showFiltersModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Filter Reservations</h3>
                
                <div class="space-y-4">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                        <select v-model="modalFilters.status"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">From Date</label>
                            <input v-model="modalFilters.dateFrom"
                                   type="date"
                                   class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">To Date</label>
                            <input v-model="modalFilters.dateTo"
                                   type="date"
                                   class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Sorting -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Sort By</label>
                            <select v-model="modalFilters.sortBy"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="created_at">Created Date</option>
                                <option value="start_date">Start Date</option>
                                <option value="end_date">End Date</option>
                                <option value="total_price">Price</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Direction</label>
                            <select v-model="modalFilters.sortDirection"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="asc">Ascending</option>
                                <option value="desc">Descending</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex gap-3 mt-6 pt-4 border-t border-slate-200">
                    <button @click="applyFiltersFromModal"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Apply Filters
                    </button>
                    <button @click="clearFilters"
                            class="px-4 py-2 bg-slate-600 text-white rounded-md hover:bg-slate-700 transition-colors">
                        Clear All
                    </button>
                    <button @click="showFiltersModal = false"
                            class="px-4 py-2 bg-slate-300 text-slate-700 rounded-md hover:bg-slate-400 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </Modal>
    </DashboardLayout>
</template>