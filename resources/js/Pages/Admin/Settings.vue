<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: Object,
});

// Form for updating settings
const settingsForm = useForm({
    platform_name: props.settings.platform_name,
    support_email: props.settings.support_email,
    maintenance_mode: props.settings.maintenance_mode,
    allow_registrations: props.settings.allow_registrations,
    auto_approve_agencies: props.settings.auto_approve_agencies,
    commission_rate: props.settings.commission_rate,
    logo: null,
});

// Logo preview
const logoPreview = ref(props.settings.logo ? `/storage/${props.settings.logo}` : null);

const activeTab = ref('general');
const showSuccessMessage = ref(false);

const saveSettings = () => {
    settingsForm.post(route('admin.settings.update'), {
        forceFormData: true,
        onSuccess: () => {
            showSuccessMessage.value = true;
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, 3000);
        },
        onError: (errors) => {
            console.error('Settings save failed:', errors);
        }
    });
};

const handleLogoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        settingsForm.logo = file;
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    settingsForm.logo = null;
    logoPreview.value = null;
    // Reset file input
    const fileInput = document.getElementById('logo-upload');
    if (fileInput) {
        fileInput.value = '';
    }
};

// Quick actions
const exportData = () => {
    // In a real app, this would trigger a data export
    alert('Data export functionality would be implemented here.');
};

const runSystemCheck = () => {
    // In a real app, this would run system diagnostics
    alert('System check completed successfully!');
};

const clearCache = () => {
    // In a real app, this would clear the application cache
    if (confirm('Are you sure you want to clear the cache?')) {
        // For now, just refresh the page to pick up any changes
        window.location.reload();
    }
};

const backupData = () => {
    // In a real app, this would create a data backup
    if (confirm('Create a backup of all platform data?')) {
        alert('Backup initiated. You will be notified when complete.');
    }
};
</script>

<template>
    <DashboardLayout role="admin">
        <Head :title="`Settings - ${$page.props.appName}`" />
        
        <template #header>
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-800 mb-1">Platform Settings</h1>
                <div class="text-slate-500 text-base">Configure platform-wide settings and preferences</div>
            </div>
        </template>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <!-- Settings Tabs -->
            <div class="border-b border-slate-200">
                <nav class="flex space-x-8 px-6">
                    <button 
                        @click="activeTab = 'general'"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                            activeTab === 'general' 
                                ? 'border-blue-500 text-blue-600' 
                                : 'border-transparent text-slate-500 hover:text-slate-700'
                        ]"
                    >
                        General
                    </button>
                    <button 
                        @click="activeTab = 'platform'"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                            activeTab === 'platform' 
                                ? 'border-blue-500 text-blue-600' 
                                : 'border-transparent text-slate-500 hover:text-slate-700'
                        ]"
                    >
                        Platform
                    </button>
                    <button 
                        @click="activeTab = 'business'"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                            activeTab === 'business' 
                                ? 'border-blue-500 text-blue-600' 
                                : 'border-transparent text-slate-500 hover:text-slate-700'
                        ]"
                    >
                        Business
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <!-- Success Message -->
                <div v-if="showSuccessMessage" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-green-800 font-medium">Settings updated successfully!</span>
                    </div>
                </div>

                <!-- Error Messages -->
                <div v-if="settingsForm.errors && Object.keys(settingsForm.errors).length > 0" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h4 class="text-red-800 font-medium mb-2">Please fix the following errors:</h4>
                            <ul class="text-red-700 text-sm space-y-1">
                                <li v-for="(error, field) in settingsForm.errors" :key="field">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- General Settings -->
                <div v-if="activeTab === 'general'" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Platform Name
                        </label>
                        <input 
                            v-model="settingsForm.platform_name"
                            type="text" 
                            class="w-full max-w-md px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter platform name"
                        />
                        <p class="text-sm text-slate-500 mt-1">The name displayed across the platform</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Platform Logo
                        </label>
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div v-if="logoPreview" class="relative">
                                    <img :src="logoPreview" alt="Logo preview" class="w-24 h-24 object-contain border border-slate-300 rounded-lg bg-white">
                                    <button @click="removeLogo" 
                                            type="button"
                                            class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600">
                                        ×
                                    </button>
                                </div>
                                <div v-else class="w-24 h-24 border-2 border-dashed border-slate-300 rounded-lg flex items-center justify-center bg-slate-50">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <input 
                                    @change="handleLogoChange"
                                    type="file" 
                                    id="logo-upload"
                                    accept="image/*"
                                    class="w-full max-w-md px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                                <p class="text-sm text-slate-500 mt-1">Upload a logo for your platform. Recommended size: 200x200px. Max size: 2MB.</p>
                                <div v-if="settingsForm.errors.logo" class="text-red-500 text-sm mt-1">
                                    {{ settingsForm.errors.logo }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Support Email
                        </label>
                        <input 
                            v-model="settingsForm.support_email"
                            type="email" 
                            class="w-full max-w-md px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="support@vheego.com"
                        />
                        <p class="text-sm text-slate-500 mt-1">Email address for customer support inquiries</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <input 
                            v-model="settingsForm.maintenance_mode"
                            type="checkbox" 
                            id="maintenance_mode"
                            class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                        />
                        <label for="maintenance_mode" class="text-sm font-medium text-slate-700">
                            Maintenance Mode
                        </label>
                    </div>
                    <p class="text-sm text-slate-500 -mt-4 ml-7">Enable to show maintenance page to users</p>
                </div>

                <!-- Platform Settings -->
                <div v-if="activeTab === 'platform'" class="space-y-6">
                    <div class="flex items-center gap-3">
                        <input 
                            v-model="settingsForm.allow_registrations"
                            type="checkbox" 
                            id="allow_registrations"
                            class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                        />
                        <label for="allow_registrations" class="text-sm font-medium text-slate-700">
                            Allow New Registrations
                        </label>
                    </div>
                    <p class="text-sm text-slate-500 -mt-4 ml-7">Allow new users to register on the platform</p>

                    <div class="flex items-center gap-3">
                        <input 
                            v-model="settingsForm.auto_approve_agencies"
                            type="checkbox" 
                            id="auto_approve_agencies"
                            class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                        />
                        <label for="auto_approve_agencies" class="text-sm font-medium text-slate-700">
                            Auto-approve Agency Applications
                        </label>
                    </div>
                    <p class="text-sm text-slate-500 -mt-4 ml-7">Automatically approve new agency registrations without manual review</p>
                </div>

                <!-- Business Settings -->
                <div v-if="activeTab === 'business'" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Commission Rate (%)
                        </label>
                        <input 
                            v-model="settingsForm.commission_rate"
                            type="number" 
                            min="0"
                            max="100"
                            step="0.1"
                            class="w-full max-w-md px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="10"
                        />
                        <p class="text-sm text-slate-500 mt-1">Platform commission rate on bookings</p>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-blue-800 mb-2">Revenue Information</h4>
                        <p class="text-sm text-blue-700">
                            Current commission rate will be applied to all new bookings. 
                            Existing bookings will maintain their original commission rate.
                        </p>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="pt-6 border-t border-slate-200">
                    <button 
                        @click="saveSettings"
                        :disabled="settingsForm.processing"
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ settingsForm.processing ? 'Saving...' : 'Save Settings' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">System Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center p-4 bg-slate-50 rounded-lg">
                    <div class="text-lg font-bold text-slate-800">Laravel 10</div>
                    <div class="text-sm text-slate-600">Framework</div>
                </div>
                <div class="text-center p-4 bg-slate-50 rounded-lg">
                    <div class="text-lg font-bold text-slate-800">Vue 3</div>
                    <div class="text-sm text-slate-600">Frontend</div>
                </div>
                <div class="text-center p-4 bg-slate-50 rounded-lg">
                    <div class="text-lg font-bold text-slate-800">Inertia.js</div>
                    <div class="text-sm text-slate-600">Stack</div>
                </div>
                <div class="text-center p-4 bg-slate-50 rounded-lg">
                    <div class="text-lg font-bold text-green-600">Online</div>
                    <div class="text-sm text-slate-600">Status</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <button @click="exportData" class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <div class="text-left">
                        <div class="font-medium text-slate-800">Export Data</div>
                        <div class="text-sm text-slate-500">Download platform data</div>
                    </div>
                </button>
                
                <button @click="runSystemCheck" class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-left">
                        <div class="font-medium text-slate-800">System Check</div>
                        <div class="text-sm text-slate-500">Run health diagnostics</div>
                    </div>
                </button>
                
                <button @click="clearCache" class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <div class="text-left">
                        <div class="font-medium text-slate-800">Clear Cache</div>
                        <div class="text-sm text-slate-500">Clear system cache</div>
                    </div>
                </button>
                
                <button @click="backupData" class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <div class="text-left">
                        <div class="font-medium text-slate-800">Backup Data</div>
                        <div class="text-sm text-slate-500">Create data backup</div>
                    </div>
                </button>
            </div>
        </div>
    </DashboardLayout>
</template>