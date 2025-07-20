<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import { debounce } from 'lodash';

const props = defineProps({
    users: Object,
    stats: Object,
});

// Modal states
const showBanModal = ref(false);
const selectedUser = ref(null);

// Form for banning users
const banForm = useForm({
    reason: ''
});

// Filter and search
const searchQuery = ref(new URLSearchParams(window.location.search).get('search') || '');
const selectedFilter = ref(new URLSearchParams(window.location.search).get('filter') || 'all');

// Apply filters by updating URL and making new request
const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (searchQuery.value) {
        params.set('search', searchQuery.value);
    }
    
    if (selectedFilter.value && selectedFilter.value !== 'all') {
        params.set('filter', selectedFilter.value);
    }
    
    const url = route('admin.users') + (params.toString() ? '?' + params.toString() : '');
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

// Use actual users data from backend (no filtering needed on frontend)
const displayUsers = computed(() => {
    return props.users?.data || [];
});

// Actions
const banUser = (user) => {
    selectedUser.value = user;
    showBanModal.value = true;
};

const unbanUser = (user) => {
    router.post(route('admin.users.unban', user.id));
};

const submitBan = () => {
    banForm.post(route('admin.users.ban', selectedUser.value.id), {
        onSuccess: () => {
            showBanModal.value = false;
            banForm.reset();
        }
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getUserBadgeColor = (userType) => {
    const colors = {
        admin: 'bg-red-100 text-red-800',
        renter: 'bg-blue-100 text-blue-800',
        customer: 'bg-green-100 text-green-800'
    };
    return colors[userType] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <DashboardLayout role="admin">
        <Head title="Users Management - Admin" />
        
        <template #header>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-800 mb-1">Users Management</h1>
                <div class="text-slate-500 text-base">Manage platform users and permissions</div>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-2xl font-bold text-slate-800">{{ stats.totalUsers }}</div>
                <div class="text-sm text-slate-500">Total Users</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-2xl font-bold text-green-600">{{ stats.activeUsers }}</div>
                <div class="text-sm text-slate-500">Active</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-2xl font-bold text-red-600">{{ stats.bannedUsers }}</div>
                <div class="text-sm text-slate-500">Banned</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-2xl font-bold text-purple-600">{{ stats.adminUsers }}</div>
                <div class="text-sm text-slate-500">Admins</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-2xl font-bold text-blue-600">{{ stats.renterUsers }}</div>
                <div class="text-sm text-slate-500">Renters</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="text-2xl font-bold text-yellow-600">{{ stats.customerUsers }}</div>
                <div class="text-sm text-slate-500">Customers</div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search users by name or email..."
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <div>
                    <select 
                        v-model="selectedFilter"
                        class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="all">All Users</option>
                        <option value="active">Active</option>
                        <option value="banned">Banned</option>
                        <option value="admin">Admins</option>
                        <option value="renter">Renters</option>
                        <option value="customer">Customers</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-800">
                    Users ({{ displayUsers.length }})
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <tr v-for="user in displayUsers" :key="user.id" class="hover:bg-slate-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img :src="user.profile_picture_url || '/images/user-placeholder.svg'" 
                                         :alt="user.name" 
                                         class="w-10 h-10 rounded-full object-cover bg-gray-100"
                                         @error="$event.target.src = '/images/user-placeholder.svg'">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-slate-900">{{ user.name }}</div>
                                        <div class="text-sm text-slate-500">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    getUserBadgeColor(user.user_type)
                                ]">
                                    {{ user.user_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div v-if="user.is_banned">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Banned</span>
                                    <div class="text-xs text-slate-500 mt-1">{{ user.banned_reason }}</div>
                                </div>
                                <span v-else class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ formatDate(user.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <button v-if="!user.is_banned && user.user_type !== 'admin'" 
                                            @click="banUser(user)"
                                            class="px-3 py-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition-colors">
                                        Ban
                                    </button>
                                    <button v-if="user.is_banned" 
                                            @click="unbanUser(user)"
                                            class="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition-colors">
                                        Unban
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="users.links" class="px-6 py-4 border-t border-slate-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-500">
                        Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
                    </div>
                    <div class="flex gap-2">
                        <template v-for="link in users.links" :key="link.label">
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

        <!-- Ban User Modal -->
        <Modal :show="showBanModal" @close="showBanModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Ban User</h3>
                <div v-if="selectedUser" class="mb-4">
                    <p class="text-sm text-slate-600 mb-4">
                        You are about to ban <strong>{{ selectedUser.name }}</strong> ({{ selectedUser.email }})
                    </p>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Reason for ban *
                    </label>
                    <textarea v-model="banForm.reason" 
                              class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              rows="3" 
                              placeholder="Enter reason for banning this user..."
                              required></textarea>
                    <div v-if="banForm.errors.reason" class="text-red-500 text-sm mt-1">
                        {{ banForm.errors.reason }}
                    </div>
                </div>
                <div class="flex gap-3">
                    <button @click="submitBan"
                            :disabled="banForm.processing || !banForm.reason"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ banForm.processing ? 'Banning...' : 'Ban User' }}
                    </button>
                    <button @click="showBanModal = false"
                            class="px-4 py-2 bg-slate-300 text-slate-700 rounded-md hover:bg-slate-400">
                        Cancel
                    </button>
                </div>
            </div>
        </Modal>
    </DashboardLayout>
</template>