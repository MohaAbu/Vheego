<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import { debounce } from 'lodash';

const props = defineProps({
    agencies: Object,
    stats: Object,
    filters: Object,
});

// Modal states
const showActionModal = ref(false);
const selectedAgency = ref(null);
const modalAction = ref('');

// Form for agency actions
const actionForm = useForm({
    admin_feedback: ''
});

// Filter and search
const searchQuery = ref(props.filters?.search || '');
const selectedFilter = ref(props.filters?.filter || 'all');
const sortBy = ref(props.filters?.sort || 'created_at');
const sortDirection = ref(props.filters?.direction || 'desc');

// Apply filters by updating URL and making new request
const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (searchQuery.value) {
        params.set('search', searchQuery.value);
    }
    
    if (selectedFilter.value && selectedFilter.value !== 'all') {
        params.set('filter', selectedFilter.value);
    }
    
    if (sortBy.value !== 'created_at') {
        params.set('sort', sortBy.value);
    }
    
    if (sortDirection.value !== 'desc') {
        params.set('direction', sortDirection.value);
    }
    
    const url = route('admin.agencies') + (params.toString() ? '?' + params.toString() : '');
    router.get(url);
};

// Watch for changes and apply filters with debounce
const debouncedApplyFilters = debounce(applyFilters, 500);

// Watch search query
watch(searchQuery, () => {
    debouncedApplyFilters();
});

// Watch filter selection
watch(selectedFilter, () => {
    applyFilters();
});

// Watch sorting
watch([sortBy, sortDirection], () => {
    applyFilters();
});

// Use actual agencies data from backend
const displayAgencies = computed(() => {
    return props.agencies?.data || [];
});

// Actions
const approveAgency = (agency) => {
    selectedAgency.value = agency;
    modalAction.value = 'approve';
    showActionModal.value = true;
};

const rejectAgency = (agency) => {
    selectedAgency.value = agency;
    modalAction.value = 'reject';
    showActionModal.value = true;
};

const submitAction = () => {
    const routeName = modalAction.value === 'approve' ? 'admin.agencies.approve' : 'admin.agencies.reject';
    
    actionForm.post(route(routeName, selectedAgency.value.id), {
        onSuccess: () => {
            showActionModal.value = false;
            actionForm.reset();
        }
    });
};

// Utility functions
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getStatusBadgeColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const formatRating = (rating) => {
    return parseFloat(rating || 0).toFixed(1);
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
</script>

<template>
    <DashboardLayout role="admin">
        <Head title="Agencies Management - Admin" />
        
        <template #header>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-800 mb-1">Agencies Management</h1>
                <div class="text-slate-500 text-base">Manage car rental agencies and their verification status</div>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-2xl font-bold text-slate-800">{{ stats.totalAgencies }}</div>
                <div class="text-sm text-slate-500">Total Agencies</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-2xl font-bold text-yellow-600">{{ stats.pendingAgencies }}</div>
                <div class="text-sm text-slate-500">Pending</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-2xl font-bold text-green-600">{{ stats.approvedAgencies }}</div>
                <div class="text-sm text-slate-500">Approved</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-2xl font-bold text-red-600">{{ stats.rejectedAgencies }}</div>
                <div class="text-sm text-slate-500">Rejected</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-2xl font-bold text-blue-600">{{ stats.totalCars }}</div>
                <div class="text-sm text-slate-500">Total Cars</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-2xl font-bold text-purple-600">{{ formatRating(stats.avgRating) }}</div>
                <div class="text-sm text-slate-500">Avg Rating</div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex flex-col lg:flex-row gap-4">
                <div class="flex-1">
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search agencies by name, email, or owner..."
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <div class="flex gap-4">
                    <select 
                        v-model="selectedFilter"
                        class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <select 
                        v-model="sortBy"
                        class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="created_at">Sort by Date</option>
                        <option value="name">Sort by Name</option>
                        <option value="verification_status">Sort by Status</option>
                        <option value="rating">Sort by Rating</option>
                    </select>
                    <select 
                        v-model="sortDirection"
                        class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Agencies Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-800">
                    Agencies ({{ displayAgencies.length }})
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                <button @click="toggleSort('name')" class="flex items-center gap-1 hover:text-slate-700">
                                    Agency {{ getSortIcon('name') }}
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Owner & Contact
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                <button @click="toggleSort('verification_status')" class="flex items-center gap-1 hover:text-slate-700">
                                    Status {{ getSortIcon('verification_status') }}
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Cars & Rating
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                <button @click="toggleSort('created_at')" class="flex items-center gap-1 hover:text-slate-700">
                                    Date {{ getSortIcon('created_at') }}
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <tr v-for="agency in displayAgencies" :key="agency.id" class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img :src="agency.logo_url || '/images/agency-placeholder.svg'" 
                                         :alt="agency.name" 
                                         class="w-10 h-10 rounded-lg object-cover bg-gray-100 mr-3"
                                         @error="$event.target.src = '/images/agency-placeholder.svg'">
                                    <div>
                                        <div class="text-sm font-medium text-slate-900">{{ agency.name }}</div>
                                        <div class="text-sm text-slate-500 truncate max-w-xs">{{ agency.description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">{{ agency.renter?.name }}</div>
                                <div class="text-sm text-slate-500">{{ agency.contact_email }}</div>
                                <div class="text-xs text-slate-400">{{ agency.contact_phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    getStatusBadgeColor(agency.verification_status)
                                ]">
                                    {{ agency.verification_status }}
                                </span>
                                <div v-if="agency.admin_feedback" class="text-xs text-slate-500 mt-1 max-w-xs truncate">
                                    {{ agency.admin_feedback }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">
                                    {{ agency.active_cars }}/{{ agency.total_cars }} Cars
                                </div>
                                <div class="flex items-center text-sm text-slate-500">
                                    <span class="text-yellow-400">★</span>
                                    <span class="ml-1">{{ formatRating(agency.rating) }}</span>
                                    <span class="ml-1">({{ agency.total_reviews || 0 }})</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                <div>{{ formatDate(agency.created_at) }}</div>
                                <div v-if="agency.reviewed_at" class="text-xs">
                                    Reviewed: {{ formatDate(agency.reviewed_at) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <!-- Pending Status Actions -->
                                    <button v-if="agency.verification_status === 'pending'" 
                                            @click="approveAgency(agency)"
                                            class="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition-colors">
                                        Approve
                                    </button>
                                    <button v-if="agency.verification_status === 'pending'" 
                                            @click="rejectAgency(agency)"
                                            class="px-3 py-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition-colors">
                                        Reject
                                    </button>
                                    
                                    <!-- Approved Status Actions -->
                                    <button v-if="agency.verification_status === 'approved'" 
                                            @click="rejectAgency(agency)"
                                            class="px-3 py-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition-colors">
                                        Reject
                                    </button>
                                    
                                    <!-- Rejected Status Actions -->
                                    <button v-if="agency.verification_status === 'rejected'" 
                                            @click="approveAgency(agency)"
                                            class="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition-colors">
                                        Approve
                                    </button>
                                    
                                    <!-- View Button for all statuses -->
                                    <Link :href="route('agencies.show', agency.id)"
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
            <div v-if="agencies.links" class="px-6 py-4 border-t border-slate-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-500">
                        Showing {{ agencies.from }} to {{ agencies.to }} of {{ agencies.total }} results
                    </div>
                    <div class="flex gap-2">
                        <template v-for="link in agencies.links" :key="link.label">
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

        <!-- Agency Action Modal -->
        <Modal :show="showActionModal" @close="showActionModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">
                    {{ modalAction === 'approve' ? 'Approve Agency' : 'Reject Agency' }}
                </h3>
                <div v-if="selectedAgency" class="mb-4">
                    <div class="flex items-center gap-3 mb-4 p-3 bg-slate-50 rounded-lg">
                        <img :src="selectedAgency.logo_url || '/images/agency-placeholder.svg'" 
                             :alt="selectedAgency.name" 
                             class="w-12 h-12 rounded-lg object-cover">
                        <div>
                            <div class="font-medium text-slate-800">{{ selectedAgency.name }}</div>
                            <div class="text-sm text-slate-600">{{ selectedAgency.renter?.name }}</div>
                        </div>
                    </div>
                    
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Admin Feedback {{ modalAction === 'reject' ? '(Required)' : '(Optional)' }}
                    </label>
                    <textarea v-model="actionForm.admin_feedback" 
                              class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              rows="3" 
                              :placeholder="modalAction === 'approve' ? 'Welcome to our platform!' : 'Please provide a reason for rejection...'"
                              :required="modalAction === 'reject'"></textarea>
                    <div v-if="actionForm.errors.admin_feedback" class="text-red-500 text-sm mt-1">
                        {{ actionForm.errors.admin_feedback }}
                    </div>
                </div>
                <div class="flex gap-3">
                    <button @click="submitAction"
                            :disabled="actionForm.processing || (modalAction === 'reject' && !actionForm.admin_feedback)"
                            :class="[
                                'px-4 py-2 text-white rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed',
                                modalAction === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'
                            ]">
                        {{ actionForm.processing ? 'Processing...' : (modalAction === 'approve' ? 'Approve Agency' : 'Reject Agency') }}
                    </button>
                    <button @click="showActionModal = false"
                            class="px-4 py-2 bg-slate-300 text-slate-700 rounded-md hover:bg-slate-400 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </Modal>
    </DashboardLayout>
</template>