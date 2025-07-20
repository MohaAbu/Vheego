<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    user: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// Determine user role for dashboard layout
const userRole = computed(() => {
    if (user.value?.user_type === 'admin') return 'admin';
    if (user.value?.user_type === 'renter') return 'renter';
    return 'customer';
});
</script>

<template>
    <Head :title="`Profile - ${$page.props.appName}`" />

    <DashboardLayout :role="userRole">
        <template #header>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-800 mb-1">My Profile</h1>
                <div class="text-slate-500 text-base">Manage your account settings and preferences</div>
            </div>
        </template>

        <!-- Profile Header Card -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
            <div class="flex items-center gap-6">
                <!-- Profile Picture -->
                <div class="relative">
                    <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                        {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
                    </div>
                    <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-green-500 rounded-full border-4 border-white flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-slate-800 mb-1">{{ user?.name }}</h2>
                    <p class="text-slate-600 mb-2">{{ user?.email }}</p>
                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                              :class="{
                                  'bg-purple-100 text-purple-800': user?.user_type === 'admin',
                                  'bg-blue-100 text-blue-800': user?.user_type === 'renter',
                                  'bg-green-100 text-green-800': user?.user_type === 'customer'
                              }">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                            </svg>
                            {{ user?.user_type?.charAt(0).toUpperCase() + user?.user_type?.slice(1) || 'User' }}
                        </span>
                        <span class="text-sm text-slate-500">
                            Member since {{ new Date(user?.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long' }) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Profile Information -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                <div class="p-6 border-b border-slate-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">Profile Information</h3>
                            <p class="text-sm text-slate-600">Update your account details and email address</p>
                        </div>
                    </div>
                </div>
        <div class="p-6">
            <div v-if="status === 'verification-link-sent'" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            A new verification link has been sent to your new email address.
                        </p>
                    </div>
                </div>
            </div>
            
            <UpdateProfileInformationForm
                :must-verify-email="mustVerifyEmail"
                :status="status"
                :user="props.user"
            />
        </div>
            </div>

            <!-- Security Settings -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                <div class="p-6 border-b border-slate-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">Security Settings</h3>
                            <p class="text-sm text-slate-600">Ensure your account stays secure</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <UpdatePasswordForm />
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-red-200">
            <div class="p-6 border-b border-red-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-red-800">Danger Zone</h3>
                        <p class="text-sm text-red-600">Irreversible and destructive actions</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <DeleteUserForm />
            </div>
        </div>
    </DashboardLayout>
</template>
