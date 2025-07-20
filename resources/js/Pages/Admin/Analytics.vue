<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    revenueStats: Object,
    bookingStats: Object,
    topAgencies: Array,
    revenueChart: Array,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0);
};

const formatPercentage = (value, total) => {
    if (total === 0) return '0%';
    return `${Math.round((value / total) * 100)}%`;
};

const maxRevenue = computed(() => {
    return Math.max(...props.revenueChart.map(item => item.revenue));
});
</script>

<template>
    <DashboardLayout role="admin">
        <Head title="Analytics - Admin" />
        
        <template #header>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-800 mb-1">Analytics & Insights</h1>
                <div class="text-slate-500 text-base">Platform performance and revenue analytics</div>
            </div>
        </template>

        <!-- Revenue Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="bg-green-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-800">{{ formatCurrency(revenueStats.total) }}</div>
                        <div class="text-sm text-slate-500">Total Revenue</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-800">{{ formatCurrency(revenueStats.monthly) }}</div>
                        <div class="text-sm text-slate-500">This Month</div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="bg-purple-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-slate-800">{{ formatCurrency(revenueStats.weekly) }}</div>
                        <div class="text-sm text-slate-500">This Week</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-slate-800 mb-1">{{ bookingStats.total }}</div>
                    <div class="text-sm text-slate-500">Total Bookings</div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 mb-1">{{ bookingStats.completed }}</div>
                    <div class="text-sm text-slate-500">Completed</div>
                    <div class="text-xs text-green-600 mt-1">{{ formatPercentage(bookingStats.completed, bookingStats.total) }}</div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 mb-1">{{ bookingStats.active }}</div>
                    <div class="text-sm text-slate-500">Active</div>
                    <div class="text-xs text-blue-600 mt-1">{{ formatPercentage(bookingStats.active, bookingStats.total) }}</div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-red-600 mb-1">{{ bookingStats.cancelled }}</div>
                    <div class="text-sm text-slate-500">Cancelled</div>
                    <div class="text-xs text-red-600 mt-1">{{ formatPercentage(bookingStats.cancelled, bookingStats.total) }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-6">Revenue Trend (Last 6 Months)</h3>
                <div class="space-y-4">
                    <div v-for="(item, index) in revenueChart" :key="index" class="flex items-center justify-between">
                        <div class="text-sm font-medium text-slate-600">{{ item.month }}</div>
                        <div class="flex items-center gap-3 flex-1 ml-4">
                            <div class="flex-1 bg-slate-200 rounded-full h-2">
                                <div 
                                    class="bg-gradient-to-r from-blue-500 to-cyan-500 h-2 rounded-full transition-all duration-300"
                                    :style="{ width: `${maxRevenue > 0 ? (item.revenue / maxRevenue) * 100 : 0}%` }"
                                ></div>
                            </div>
                            <div class="text-sm font-bold text-slate-800 min-w-[80px] text-right">
                                {{ formatCurrency(item.revenue) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Agencies -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-6">Top Performing Agencies</h3>
                <div class="space-y-4">
                    <div v-for="(agency, index) in topAgencies" :key="agency.id" 
                         class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full text-blue-600 font-bold text-sm">
                                {{ index + 1 }}
                            </div>
                            <div>
                                <div class="font-medium text-slate-800">{{ agency.name }}</div>
                                <div class="text-sm text-slate-500">{{ agency.address }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-slate-800">{{ formatCurrency(agency.total_revenue) }}</div>
                            <div class="text-sm text-slate-500">Revenue</div>
                        </div>
                    </div>
                    <div v-if="topAgencies.length === 0" class="text-center py-8 text-slate-500">
                        No revenue data available yet.
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Insights -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Key Insights</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">
                        {{ bookingStats.total > 0 ? Math.round((bookingStats.completed / bookingStats.total) * 100) : 0 }}%
                    </div>
                    <div class="text-sm text-slate-600">Completion Rate</div>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">
                        {{ bookingStats.completed > 0 ? formatCurrency(revenueStats.total / bookingStats.completed) : '$0' }}
                    </div>
                    <div class="text-sm text-slate-600">Avg Revenue per Booking</div>
                </div>
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600">{{ topAgencies.length }}</div>
                    <div class="text-sm text-slate-600">Active Agencies</div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>