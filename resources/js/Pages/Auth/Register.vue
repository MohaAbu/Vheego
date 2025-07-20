<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    user_type: 'customer',
    profile_picture: null,
    terms: false,
});

const previewUrl = ref('/images/user-placeholder.svg');
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Password strength indicator
const passwordStrength = computed(() => {
    const password = form.password;
    if (!password) return { strength: 0, text: '', color: '' };
    
    let score = 0;
    if (password.length >= 8) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;
    
    const levels = {
        0: { text: '', color: '', strength: 0 },
        1: { text: 'Very Weak', color: 'bg-red-500', strength: 20 },
        2: { text: 'Weak', color: 'bg-orange-500', strength: 40 },
        3: { text: 'Fair', color: 'bg-yellow-500', strength: 60 },
        4: { text: 'Good', color: 'bg-green-500', strength: 80 },
        5: { text: 'Strong', color: 'bg-green-600', strength: 100 }
    };
    
    return levels[score];
});

function onProfilePictureChange(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            return;
        }
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            return;
        }
        
        form.profile_picture = file;
        previewUrl.value = URL.createObjectURL(file);
    } else {
        form.profile_picture = null;
        previewUrl.value = '/images/user-placeholder.svg';
    }
}

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const togglePasswordConfirmationVisibility = () => {
    showPasswordConfirmation.value = !showPasswordConfirmation.value;
};

const submit = () => {
    form.post(route('register'), {
        forceFormData: true,
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="`Join ${$page.props.appName} - Create Your Account`" />

        <!-- Enhanced Page Header -->
        <div class="text-center mb-8 animate-slide-in">
            <h1 class="text-3xl font-bold text-white mb-3 font-heading">Join Vheego</h1>
            <p class="text-blue-100/80 text-lg">Create your account and unlock premium car rentals</p>
        </div>

        <!-- Enhanced Registration Form -->
        <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-6 animate-scale-in">
            <!-- Enhanced Profile Picture Upload -->
            <div class="flex flex-col items-center space-y-4">
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full blur-sm opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                    <img
                        :src="previewUrl"
                        alt="Profile Picture"
                        class="relative w-24 h-24 rounded-full object-cover border-4 border-white/20 shadow-lg group-hover:scale-105 transition-transform duration-300"
                    />
                    <div class="absolute -bottom-1 -right-1 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-full p-2 shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                </div>
                <div class="relative">
                    <input
                        type="file"
                        accept="image/*"
                        @change="onProfilePictureChange"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        id="profile-upload"
                    />
                    <label 
                        for="profile-upload"
                        class="inline-flex items-center px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-blue-100 text-sm font-medium hover:bg-white/20 hover:text-white hover:border-white/30 transition-all duration-200 cursor-pointer backdrop-blur-sm transform hover:scale-105"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Upload Photo
                    </label>
                </div>
                <p class="text-xs text-blue-200/60 text-center">Optional • Max 2MB • JPG, PNG, GIF</p>
                <InputError class="text-red-300 text-center text-sm" :message="form.errors.profile_picture" />
            </div>

            <!-- Enhanced Name Field -->
            <div class="space-y-2">
                <InputLabel for="name" value="Full Name" class="text-white font-medium text-sm" />
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-blue-300/70 group-focus-within:text-blue-300 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <TextInput
                        id="name"
                        type="text"
                        class="w-full pl-12 pr-4 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-200/60 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-400/50 backdrop-blur-sm transition-all duration-200 hover:bg-white/15"
                        :class="{ 'border-red-400 focus:ring-red-400/50': form.errors.name }"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Enter your full name"
                    />
                </div>
                <InputError class="mt-2 text-red-300 text-sm" :message="form.errors.name" />
            </div>

            <!-- Enhanced Email Field -->
            <div class="space-y-2">
                <InputLabel for="email" value="Email Address" class="text-white font-medium text-sm" />
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-blue-300/70 group-focus-within:text-blue-300 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <TextInput
                        id="email"
                        type="email"
                        class="w-full pl-12 pr-4 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-200/60 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-400/50 backdrop-blur-sm transition-all duration-200 hover:bg-white/15"
                        :class="{ 'border-red-400 focus:ring-red-400/50': form.errors.email }"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="Enter your email address"
                    />
                </div>
                <InputError class="mt-2 text-red-300 text-sm" :message="form.errors.email" />
            </div>

            <!-- Enhanced User Type Selection -->
            <div class="space-y-3">
                <InputLabel for="user_type" value="Account Type" class="text-white font-medium text-sm" />
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative cursor-pointer group">
                        <input
                            type="radio"
                            name="user_type"
                            value="customer"
                            v-model="form.user_type"
                            class="sr-only"
                        />
                        <div class="p-4 border border-white/20 rounded-xl bg-white/10 backdrop-blur-sm transition-all duration-200 group-hover:bg-white/15"
                             :class="form.user_type === 'customer' 
                                 ? 'border-blue-400 bg-blue-500/20 ring-2 ring-blue-400/50 shadow-lg' 
                                 : 'hover:border-white/30'"
                        >
                            <div class="flex items-center justify-center mb-3">
                                <div class="p-2 rounded-lg bg-blue-500/20">
                                    <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-white font-semibold">Customer</div>
                                <div class="text-blue-200/70 text-xs mt-1">Rent amazing cars</div>
                            </div>
                        </div>
                    </label>
                    
                    <label class="relative cursor-pointer group">
                        <input
                            type="radio"
                            name="user_type"
                            value="renter"
                            v-model="form.user_type"
                            class="sr-only"
                        />
                        <div class="p-4 border border-white/20 rounded-xl bg-white/10 backdrop-blur-sm transition-all duration-200 group-hover:bg-white/15"
                             :class="form.user_type === 'renter' 
                                 ? 'border-blue-400 bg-blue-500/20 ring-2 ring-blue-400/50 shadow-lg' 
                                 : 'hover:border-white/30'"
                        >
                            <div class="flex items-center justify-center mb-3">
                                <div class="p-2 rounded-lg bg-green-500/20">
                                    <svg class="w-6 h-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-white font-semibold">Renter</div>
                                <div class="text-blue-200/70 text-xs mt-1">List your cars</div>
                            </div>
                        </div>
                    </label>
                </div>
                <InputError class="mt-2 text-red-300 text-sm" :message="form.errors.user_type" />
            </div>

            <!-- Enhanced Password Field with Strength Indicator -->
            <div class="space-y-2">
                <InputLabel for="password" value="Password" class="text-white font-medium text-sm" />
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-blue-300/70 group-focus-within:text-blue-300 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="w-full pl-12 pr-12 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-200/60 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-400/50 backdrop-blur-sm transition-all duration-200 hover:bg-white/15"
                        :class="{ 'border-red-400 focus:ring-red-400/50': form.errors.password }"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="Create a strong password"
                    />
                    <button
                        type="button"
                        @click="togglePasswordVisibility"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-blue-300/70 hover:text-blue-300 transition-colors duration-200"
                    >
                        <svg v-if="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                
                <!-- Password Strength Indicator -->
                <div v-if="form.password" class="mt-2">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-blue-200/80">Password Strength</span>
                        <span class="text-xs" :class="{
                            'text-red-300': passwordStrength.strength <= 40,
                            'text-orange-300': passwordStrength.strength > 40 && passwordStrength.strength <= 60,
                            'text-green-300': passwordStrength.strength > 60
                        }">{{ passwordStrength.text }}</span>
                    </div>
                    <div class="w-full bg-white/20 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-300" 
                             :class="passwordStrength.color"
                             :style="{ width: passwordStrength.strength + '%' }"></div>
                    </div>
                </div>
                
                <InputError class="mt-2 text-red-300 text-sm" :message="form.errors.password" />
            </div>

            <!-- Enhanced Confirm Password Field -->
            <div class="space-y-2">
                <InputLabel for="password_confirmation" value="Confirm Password" class="text-white font-medium text-sm" />
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-blue-300/70 group-focus-within:text-blue-300 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <TextInput
                        id="password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        class="w-full pl-12 pr-12 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-200/60 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-400/50 backdrop-blur-sm transition-all duration-200 hover:bg-white/15"
                        :class="{ 'border-red-400 focus:ring-red-400/50': form.errors.password_confirmation }"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Confirm your password"
                    />
                    <button
                        type="button"
                        @click="togglePasswordConfirmationVisibility"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-blue-300/70 hover:text-blue-300 transition-colors duration-200"
                    >
                        <svg v-if="showPasswordConfirmation" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <InputError class="mt-2 text-red-300 text-sm" :message="form.errors.password_confirmation" />
            </div>

            <!-- Terms and Conditions -->
            <div class="space-y-3">
                <label class="flex items-start space-x-3 group cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="form.terms"
                        class="mt-1 rounded border-white/30 bg-white/10 text-blue-500 focus:ring-blue-400 focus:ring-offset-0 focus:ring-2 transition-all duration-200"
                        required
                    />
                    <div class="text-sm text-blue-200/80 group-hover:text-blue-200 transition-colors duration-200">
                        I agree to the 
                        <a href="#" class="text-blue-300 hover:text-blue-100 font-medium underline decoration-blue-300/50 hover:decoration-blue-100 transition-all duration-200">Terms of Service</a> 
                        and 
                        <a href="#" class="text-blue-300 hover:text-blue-100 font-medium underline decoration-blue-300/50 hover:decoration-blue-100 transition-all duration-200">Privacy Policy</a>
                    </div>
                </label>
                <InputError class="text-red-300 text-sm" :message="form.errors.terms" />
            </div>

            <!-- Enhanced Register Button -->
            <div class="space-y-4 pt-2">
                <PrimaryButton
                    class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 active:from-blue-800 active:to-cyan-800 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:ring-offset-2 focus:ring-offset-transparent"
                    :class="{ 'opacity-60 cursor-not-allowed pointer-events-none': form.processing || !form.terms }"
                    :disabled="form.processing || !form.terms"
                >
                    <span v-if="form.processing" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creating your account...
                    </span>
                    <span v-else class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Create Vheego Account
                    </span>
                </PrimaryButton>
            </div>
        </form>

        <!-- Enhanced Divider -->
        <div class="mt-8 mb-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-white/20"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white/10 text-blue-100/70 rounded-lg backdrop-blur-sm border border-white/10">
                        Already have an account?
                    </span>
                </div>
            </div>
        </div>

        <!-- Enhanced Sign In Link -->
        <div class="text-center animate-fade-in">
            <Link
                :href="route('login')"
                class="group inline-flex items-center justify-center w-full py-4 px-6 border border-white/30 rounded-xl text-blue-100 bg-white/5 hover:bg-white/10 hover:text-white hover:border-white/40 transition-all duration-200 font-medium backdrop-blur-sm transform hover:scale-[1.02] active:scale-[0.98]"
            >
                <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Sign In Instead
            </Link>
        </div>

        <!-- Trust Indicators -->
        <div class="mt-8 text-center space-y-3 animate-fade-in">
            <div class="flex items-center justify-center space-x-4 text-xs text-blue-200/60">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Secure Signup</span>
                </div>
                <div class="w-1 h-1 bg-blue-300/40 rounded-full"></div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Data Protected</span>
                </div>
                <div class="w-1 h-1 bg-blue-300/40 rounded-full"></div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Instant Access</span>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
/* Enhanced animation keyframes */
@keyframes slideIn {
    0% {
        transform: translateY(-20px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes scaleIn {
    0% {
        transform: scale(0.95);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

@keyframes fadeIn {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

/* Animation classes with refined timing */
.animate-slide-in {
    animation: slideIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-scale-in {
    animation: scaleIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    animation-delay: 0.1s;
    animation-fill-mode: both;
}

.animate-fade-in {
    animation: fadeIn 0.8s ease-out;
    animation-delay: 0.3s;
    animation-fill-mode: both;
}

/* Enhanced loading animation */
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

/* Enhanced glass morphism */
.backdrop-blur-sm {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

/* Smooth transitions with refined timing */
* {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Enhanced focus styles */
input:focus,
select:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.4);
}

/* Improved hover effects */
.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
}

.group:hover .group-hover\:text-blue-100 {
    color: #dbeafe;
}

.group:hover .group-hover\:scale-105 {
    transform: scale(1.05);
}

/* Better disabled state */
button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

/* Enhanced input states */
input:hover:not(:focus),
select:hover:not(:focus) {
    background-color: rgba(255, 255, 255, 0.15);
}

/* Refined radio button styles */
input[type="radio"]:checked + div {
    border-color: #3b82f6;
    background-color: rgba(59, 130, 246, 0.2);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

/* Enhanced checkbox styles */
input[type="checkbox"]:checked {
    background-color: #3b82f6;
    border-color: #3b82f6;
}

/* Improved button active state */
button:active:not(:disabled) {
    transform: scale(0.98);
}

/* Custom password strength colors */
.bg-red-500 {
    background-color: #ef4444;
}

.bg-orange-500 {
    background-color: #f59e0b;
}

.bg-yellow-500 {
    background-color: #eab308;
}

.bg-green-500 {
    background-color: #10b981;
}

.bg-green-600 {
    background-color: #059669;
}

/* Profile picture hover effect */
.group:hover img {
    transform: scale(1.05);
}

/* Enhanced underline animation */
a:hover {
    text-decoration-color: currentColor;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .grid-cols-2 {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .w-24 {
        width: 5rem;
        height: 5rem;
    }
}
</style>