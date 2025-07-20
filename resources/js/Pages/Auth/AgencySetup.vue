<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    agency: Object,
});

const form = useForm({
    name: props.agency?.name || '',
    contact_email: props.agency?.contact_email || '',
    contact_phone: props.agency?.contact_phone || '',
    address: props.agency?.address || '',
    description: props.agency?.description || '',
    logo: null,
});

const previewUrl = ref(props.agency?.logo_url || '/images/agency-placeholder.svg');

function onLogoChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.logo = file;
        previewUrl.value = URL.createObjectURL(file);
    } else {
        form.logo = null;
        previewUrl.value = props.agency?.logo_url || '/images/agency-placeholder.svg';
    }
}

const submit = () => {
    form.post(route('agency.store'), {
        forceFormData: true
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Complete Your Agency Profile" />

        <!-- Page Header -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-white mb-2">Complete Your Agency Profile</h1>
            <p class="text-blue-200">Set up your car rental agency and start listing vehicles</p>
        </div>

        <!-- Agency Profile Form -->
        <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-6">
            <!-- Agency Logo Upload -->
            <div class="flex flex-col items-center space-y-4">
                <div class="relative">
                    <img
                        :src="previewUrl"
                        alt="Agency Logo"
                        class="w-24 h-24 rounded-2xl object-cover border-4 border-white/20 shadow-lg"
                    />
                    <div class="absolute -bottom-2 -right-2 bg-blue-500 rounded-full p-2 shadow-lg">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
                <div class="relative">
                    <input
                        type="file"
                        accept="image/*"
                        @change="onLogoChange"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        id="logo-upload"
                    />
                    <label 
                        for="logo-upload"
                        class="inline-flex items-center px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-blue-200 text-sm font-medium hover:bg-white/20 transition-all duration-200 cursor-pointer backdrop-blur-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Upload Agency Logo
                    </label>
                </div>
                <InputError class="text-red-300 text-center" :message="form.errors.logo" />
            </div>

            <!-- Agency Name Field -->
            <div class="space-y-2">
                <InputLabel for="name" value="Agency Name" class="text-white font-medium" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <TextInput
                        id="name"
                        type="text"
                        class="w-full pl-12 pr-4 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent backdrop-blur-sm"
                        v-model="form.name"
                        required
                        placeholder="Enter your agency name"
                    />
                </div>
                <InputError class="mt-2 text-red-300" :message="form.errors.name" />
            </div>

            <!-- Contact Email Field -->
            <div class="space-y-2">
                <InputLabel for="contact_email" value="Contact Email" class="text-white font-medium" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <TextInput
                        id="contact_email"
                        type="email"
                        class="w-full pl-12 pr-4 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent backdrop-blur-sm"
                        v-model="form.contact_email"
                        required
                        placeholder="business@yourcompany.com"
                    />
                </div>
                <InputError class="mt-2 text-red-300" :message="form.errors.contact_email" />
            </div>

            <!-- Contact Phone Field -->
            <div class="space-y-2">
                <InputLabel for="contact_phone" value="Contact Phone" class="text-white font-medium" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    <TextInput
                        id="contact_phone"
                        type="tel"
                        class="w-full pl-12 pr-4 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent backdrop-blur-sm"
                        v-model="form.contact_phone"
                        required
                        placeholder="+1 (555) 123-4567"
                    />
                </div>
                <InputError class="mt-2 text-red-300" :message="form.errors.contact_phone" />
            </div>

            <!-- Address Field -->
            <div class="space-y-2">
                <InputLabel for="address" value="Business Address" class="text-white font-medium" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <TextInput
                        id="address"
                        type="text"
                        class="w-full pl-12 pr-4 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent backdrop-blur-sm"
                        v-model="form.address"
                        required
                        placeholder="123 Main Street, City, State 12345"
                    />
                </div>
                <InputError class="mt-2 text-red-300" :message="form.errors.address" />
            </div>

            <!-- Description Field -->
            <div class="space-y-2">
                <InputLabel for="description" value="Agency Description" class="text-white font-medium" />
                <div class="relative">
                    <div class="absolute top-4 left-0 pl-4 flex items-start pointer-events-none">
                        <svg class="h-5 w-5 text-blue-300 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </div>
                    <textarea
                        id="description"
                        v-model="form.description"
                        required
                        rows="4"
                        class="w-full pl-12 pr-4 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent backdrop-blur-sm resize-none"
                        placeholder="Tell customers about your agency, services, and what makes you special..."
                    ></textarea>
                </div>
                <InputError class="mt-2 text-red-300" :message="form.errors.description" />
            </div>

            <!-- Submit Button -->
            <div class="space-y-4 pt-4">
                <PrimaryButton
                    class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Setting up your agency...
                    </span>
                    <span v-else>🚗 Complete Agency Setup</span>
                </PrimaryButton>
            </div>
        </form>

        <!-- Help Text -->
        <div class="mt-8 text-center">
            <p class="text-blue-300 text-sm">
                Need help? Contact our support team at 
                <a href="mailto:support@vheego.com" class="text-blue-200 hover:text-white transition-colors underline">
                    support@vheego.com
                </a>
            </p>
        </div>
    </GuestLayout>
</template>

<style scoped>
/* Custom input styles */
input[type="email"]:focus,
input[type="text"]:focus,
input[type="tel"]:focus,
textarea:focus {
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

/* Loading animation */
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Glass morphism enhancements */
.backdrop-blur-sm {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

/* Smooth transitions */
* {
    transition: all 0.3s ease;
}

/* Textarea resize disabled for consistent design */
textarea {
    resize: none;
}
</style>