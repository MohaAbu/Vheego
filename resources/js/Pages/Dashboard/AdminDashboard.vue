<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head, useForm, router, usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
    pendingAgencies: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    cars: { type: Array, default: () => [] },
    reservations: { type: Array, default: () => [] },
});

const page = usePage();
const activeTab = ref('agencies');

// Modal states
const showAgencyModal = ref(false);
const showUserModal = ref(false);
const showCarModal = ref(false);
const selectedAgency = ref(null);
const selectedUser = ref(null);
const selectedCar = ref(null);

// Forms
const agencyForm = useForm({
    admin_feedback: ''
});

const userForm = useForm({
    reason: ''
});

const carForm = useForm({
    reason: ''
});

// Agency actions
const approveAgency = (agency) => {
    selectedAgency.value = agency;
    showAgencyModal.value = true;
};

const rejectAgency = (agency) => {
    selectedAgency.value = agency;
    showAgencyModal.value = true;
};

const submitAgencyAction = (action) => {
    if (action === 'approve') {
        router.post(route('admin.agencies.approve', selectedAgency.value.id), {
            admin_feedback: agencyForm.admin_feedback
        });
    } else {
        router.post(route('admin.agencies.reject', selectedAgency.value.id), {
            admin_feedback: agencyForm.admin_feedback
        });
    }
    showAgencyModal.value = false;
    agencyForm.reset();
};

// User actions
const banUser = (user) => {
    selectedUser.value = user;
    showUserModal.value = true;
};

const unbanUser = (user) => {
    router.post(route('admin.users.unban', user.id));
};

const submitBanUser = () => {
    router.post(route('admin.users.ban', selectedUser.value.id), {
        reason: userForm.reason
    });
    showUserModal.value = false;
    userForm.reset();
};

// Car actions
const delistCar = (car) => {
    selectedCar.value = car;
    showCarModal.value = true;
};

const relistCar = (car) => {
    router.post(route('admin.cars.relist', car.id));
};

const submitDelistCar = () => {
    router.post(route('admin.cars.delist', selectedCar.value.id), {
        reason: carForm.reason
    });
    showCarModal.value = false;
    carForm.reset();
};

// Computed values
const bannedUsers = computed(() => props.users.filter(user => user.is_banned));
const activeUsers = computed(() => props.users.filter(user => !user.is_banned));
const delistedCars = computed(() => props.cars.filter(car => !car.is_active));
const activeCars = computed(() => props.cars.filter(car => car.is_active));

// Limit items to first 10 for dashboard preview
const limitedUsers = computed(() => props.users.slice(0, 10));
const limitedCars = computed(() => props.cars.slice(0, 10));
const limitedReservations = computed(() => props.reservations.slice(0, 10));

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <DashboardLayout role="admin">
        <Head :title="`Admin Dashboard - ${$page.props.appName}`" />
        
        <template #header>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-800 mb-1">Admin Dashboard</h1>
                <div class="text-slate-500 text-base">Welcome back, manage your platform</div>
            </div>
        </template>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-800">{{ stats.totalUsers || 0 }}</div>
                        <div class="text-sm text-slate-500">Total Users</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="bg-green-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-800">{{ stats.totalCars || 0 }}</div>
                        <div class="text-sm text-slate-500">Total Cars</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="bg-purple-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m9-5a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2h7a2 2 0 002-2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-800">{{ stats.totalReservations || 0 }}</div>
                        <div class="text-sm text-slate-500">Total Reservations</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="bg-yellow-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-800">{{ formatCurrency(stats.totalRevenue) }}</div>
                        <div class="text-sm text-slate-500">Total Revenue</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Period Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Today's Stats</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Reservations</span>
                        <span class="font-semibold">{{ stats.todayReservations || 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Revenue</span>
                        <span class="font-semibold">{{ formatCurrency(stats.todayRevenue) }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">This Week</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Reservations</span>
                        <span class="font-semibold">{{ stats.weekReservations || 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Revenue</span>
                        <span class="font-semibold">{{ formatCurrency(stats.weekRevenue) }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">This Month</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Reservations</span>
                        <span class="font-semibold">{{ stats.monthReservations || 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Revenue</span>
                        <span class="font-semibold">{{ formatCurrency(stats.monthRevenue) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Tabs -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <div class="border-b border-slate-200">
                <nav class="flex space-x-8 px-6">
                    <button 
                        @click="activeTab = 'agencies'"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                            activeTab === 'agencies' 
                                ? 'border-blue-500 text-blue-600' 
                                : 'border-transparent text-slate-500 hover:text-slate-700'
                        ]"
                    >
                        Pending Agencies ({{ pendingAgencies.length }})
                    </button>
                    <button 
                        @click="activeTab = 'users'"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                            activeTab === 'users' 
                                ? 'border-blue-500 text-blue-600' 
                                : 'border-transparent text-slate-500 hover:text-slate-700'
                        ]"
                    >
                        Users ({{ users.length > 10 ? '10+' : users.length }})
                    </button>
                    <button 
                        @click="activeTab = 'cars'"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                            activeTab === 'cars' 
                                ? 'border-blue-500 text-blue-600' 
                                : 'border-transparent text-slate-500 hover:text-slate-700'
                        ]"
                    >
                        Cars ({{ cars.length > 10 ? '10+' : cars.length }})
                    </button>
                    <button 
                        @click="activeTab = 'reservations'"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                            activeTab === 'reservations' 
                                ? 'border-blue-500 text-blue-600' 
                                : 'border-transparent text-slate-500 hover:text-slate-700'
                        ]"
                    >
                        Reservations ({{ reservations.length > 10 ? '10+' : reservations.length }})
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <!-- Pending Agencies Tab -->
                <div v-if="activeTab === 'agencies'">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-slate-800">Pending Agencies</h3>
                        <Link :href="route('admin.agencies')" 
                              class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors">
                            View All Agencies
                        </Link>
                    </div>
                    <div v-if="pendingAgencies.length === 0" class="text-center py-8 text-slate-500">
                        No pending agencies to review.
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="agency in pendingAgencies" :key="agency.id" 
                             class="border border-slate-200 rounded-lg p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-slate-800">{{ agency.name }}</h4>
                                    <p class="text-sm text-slate-600 mb-2">{{ agency.description }}</p>
                                    <div class="text-xs text-slate-500 space-y-1">
                                        <div>Owner: {{ agency.renter?.name }}</div>
                                        <div>Email: {{ agency.contact_email }}</div>
                                        <div>Phone: {{ agency.contact_phone }}</div>
                                        <div>Address: {{ agency.address }}</div>
                                        <div>Submitted: {{ formatDate(agency.submitted_at) }}</div>
                                    </div>
                                </div>
                                <div class="flex gap-2 ml-4">
                                    <button @click="approveAgency(agency)"
                                            class="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition-colors">
                                        Approve
                                    </button>
                                    <button @click="rejectAgency(agency)"
                                            class="px-3 py-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition-colors">
                                        Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Tab -->
                <div v-if="activeTab === 'users'">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-slate-800">Recent Users</h3>
                        <Link :href="route('admin.users')" 
                              class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors">
                            View All Users
                        </Link>
                    </div>
                    <div class="space-y-4">
                        <div v-for="user in limitedUsers" :key="user.id" 
                             class="border border-slate-200 rounded-lg p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img :src="user.profile_picture_url || '/images/user-placeholder.svg'" 
                                         :alt="user.name" 
                                         class="w-10 h-10 rounded-full object-cover bg-gray-100"
                                         @error="$event.target.src = '/images/user-placeholder.svg'">
                                    <div>
                                        <h4 class="font-semibold text-slate-800">{{ user.name }}</h4>
                                        <div class="text-sm text-slate-600">{{ user.email }}</div>
                                        <div class="text-xs text-slate-500">
                                            {{ user.user_type }} • Joined {{ formatDate(user.created_at) }}
                                        </div>
                                        <div v-if="user.is_banned" class="text-xs text-red-600 mt-1">
                                            Banned: {{ user.banned_reason }}
                                        </div>
                                    </div>
                                </div>
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
                            </div>
                        </div>
                        <div v-if="users.length > 10" class="text-center py-4 text-slate-500">
                            Showing 10 of {{ users.length }} users
                        </div>
                    </div>
                </div>

                <!-- Cars Tab -->
                <div v-if="activeTab === 'cars'">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-slate-800">Recent Cars</h3>
                        <Link :href="route('cars.public')" 
                              class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors">
                            View All Cars
                        </Link>
                    </div>
                    <div class="space-y-4">
                        <div v-for="car in limitedCars" :key="car.id" 
                             class="border border-slate-200 rounded-lg p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex gap-3">
                                    <img v-if="car.images && car.images.length > 0" 
                                         :src="`/storage/${car.images[0].image_path}`" 
                                         :alt="car.make + ' ' + car.model"
                                         class="w-16 h-16 rounded-lg object-cover">
                                    <img v-else src="/images/car-placeholder.svg" 
                                         alt="Car placeholder"
                                         class="w-16 h-16 rounded-lg object-cover bg-gray-100">
                                    <div>
                                        <h4 class="font-semibold text-slate-800">{{ car.make }} {{ car.model }} ({{ car.year }})</h4>
                                        <div class="text-sm text-slate-600">{{ car.agency?.name }}</div>
                                        <div class="text-xs text-slate-500 space-y-1">
                                            <div>Category: {{ car.category }}</div>
                                            <div>Price: ${{ car.rental_price_per_day }}/day</div>
                                            <div v-if="!car.is_active" class="text-red-600">
                                                Delisted: {{ car.delisted_reason }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button v-if="car.is_active" 
                                            @click="delistCar(car)"
                                            class="px-3 py-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition-colors">
                                        Delist
                                    </button>
                                    <button v-if="!car.is_active" 
                                            @click="relistCar(car)"
                                            class="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition-colors">
                                        Relist
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-if="cars.length > 10" class="text-center py-4 text-slate-500">
                            Showing 10 of {{ cars.length }} cars
                        </div>
                    </div>
                </div>

                <!-- Reservations Tab -->
                <div v-if="activeTab === 'reservations'">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-slate-800">Recent Reservations</h3>
                        <Link :href="route('admin.reservations')" 
                              class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors">
                            View All Reservations
                        </Link>
                    </div>
                    <div class="space-y-4">
                        <div v-for="reservation in limitedReservations" :key="reservation.id" 
                             class="border border-slate-200 rounded-lg p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="font-semibold text-slate-800">
                                        {{ reservation.car?.make }} {{ reservation.car?.model }}
                                    </h4>
                                    <div class="text-sm text-slate-600 space-y-1">
                                        <div>Customer: {{ reservation.customer?.name }}</div>
                                        <div>Agency: {{ reservation.car?.agency?.name }}</div>
                                        <div>Dates: {{ formatDate(reservation.start_date) }} - {{ formatDate(reservation.end_date) }}</div>
                                        <div>Total: {{ formatCurrency(reservation.total_price) }}</div>
                                    </div>
                                </div>
                                <span :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    {
                                        'bg-green-100 text-green-800': reservation.status === 'completed',
                                        'bg-blue-100 text-blue-800': reservation.status === 'active',
                                        'bg-red-100 text-red-800': reservation.status === 'cancelled'
                                    }
                                ]">
                                    {{ reservation.status }}
                                </span>
                            </div>
                        </div>
                        <div v-if="reservations.length > 10" class="text-center py-4 text-slate-500">
                            Showing 10 of {{ reservations.length }} reservations
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <!-- Agency Approval/Rejection Modal -->
        <Modal :show="showAgencyModal" @close="showAgencyModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Agency Review</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Admin Feedback (Optional)
                    </label>
                    <textarea v-model="agencyForm.admin_feedback" 
                              class="w-full px-3 py-2 border border-slate-300 rounded-md"
                              rows="3" 
                              placeholder="Add feedback for the renter..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button @click="submitAgencyAction('approve')"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Approve
                    </button>
                    <button @click="submitAgencyAction('reject')"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Reject
                    </button>
                    <button @click="showAgencyModal = false"
                            class="px-4 py-2 bg-slate-300 text-slate-700 rounded-md hover:bg-slate-400">
                        Cancel
                    </button>
                </div>
            </div>
        </Modal>

        <!-- User Ban Modal -->
        <Modal :show="showUserModal" @close="showUserModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Ban User</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Reason for ban *
                    </label>
                    <textarea v-model="userForm.reason" 
                              class="w-full px-3 py-2 border border-slate-300 rounded-md"
                              rows="3" 
                              placeholder="Enter reason for banning this user..."
                              required></textarea>
                </div>
                <div class="flex gap-3">
                    <button @click="submitBanUser"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Ban User
                    </button>
                    <button @click="showUserModal = false"
                            class="px-4 py-2 bg-slate-300 text-slate-700 rounded-md hover:bg-slate-400">
                        Cancel
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Car Delist Modal -->
        <Modal :show="showCarModal" @close="showCarModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Delist Car</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Reason for delisting *
                    </label>
                    <textarea v-model="carForm.reason" 
                              class="w-full px-3 py-2 border border-slate-300 rounded-md"
                              rows="3" 
                              placeholder="Enter reason for delisting this car..."
                              required></textarea>
                </div>
                <div class="flex gap-3">
                    <button @click="submitDelistCar"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Delist Car
                    </button>
                    <button @click="showCarModal = false"
                            class="px-4 py-2 bg-slate-300 text-slate-700 rounded-md hover:bg-slate-400">
                        Cancel
                    </button>
                </div>
            </div>
        </Modal>
    </DashboardLayout>
</template>