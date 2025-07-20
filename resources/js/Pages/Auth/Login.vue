<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const globalSettings = usePage().props.globalSettings || {};

const registrationEnabled = computed(() => {
    return globalSettings.allow_registrations !== false;
});

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="`Sign In - ${$page.props.appName}`" />

        <!-- Enhanced Page Header -->
        <div class="text-center mb-6 animate-slide-in">
            <h1 class="text-2xl font-bold text-white mb-2 font-heading">Welcome Back</h1>
            <p class="text-blue-100/80 text-base">Sign in to access your Vheego account</p>
        </div>

        <!-- Enhanced Status Message -->
        <div v-if="status" class="mb-6 p-4 bg-green-500/20 border border-green-400/30 rounded-2xl text-green-100 text-sm font-medium backdrop-blur-sm animate-fade-in-scale">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ status }}
            </div>
        </div>

        <!-- Enhanced Ban Alert -->
        <div v-if="form.errors.email && form.errors.email.toLowerCase().includes('banned')" class="mb-6 p-4 bg-red-500/20 border border-red-400/30 rounded-2xl text-red-100 font-semibold backdrop-blur-sm animate-fade-in-scale">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                {{ form.errors.email }}
            </div>
        </div>

        <!-- Enhanced Login Form -->
        <form @submit.prevent="submit" class="space-y-5 animate-scale-in">
            <!-- Email Field with Enhanced Styling -->
            <div class="space-y-2">
                <InputLabel for="email" value="Email Address" class="text-white font-medium text-sm" />
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-blue-300">
                        <svg class="h-5 w-5 text-blue-300/70 group-focus-within:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <!-- Enhanced input with better focus states -->
                    <TextInput
                        id="email"
                        type="email"
                        class="w-full pl-12 pr-4 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-200/60 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-400/50 backdrop-blur-sm transition-all duration-200 hover:bg-white/15"
                        :class="{ 'border-red-400 focus:ring-red-400/50 focus:border-red-400': form.errors.email }"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Enter your email"
                    />
                </div>
                <InputError class="mt-2 text-red-300 text-sm" :message="form.errors.email" />
            </div>

            <!-- Password Field with Enhanced Styling -->
            <div class="space-y-2">
                <InputLabel for="password" value="Password" class="text-white font-medium text-sm" />
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-blue-300">
                        <svg class="h-5 w-5 text-blue-300/70 group-focus-within:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="w-full pl-12 pr-12 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-200/60 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-400/50 backdrop-blur-sm transition-all duration-200 hover:bg-white/15"
                        :class="{ 'border-red-400 focus:ring-red-400/50 focus:border-red-400': form.errors.password }"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    />
                    <!-- Password Toggle Button -->
                    <button 
                        type="button"
                        @click="togglePassword"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-blue-300/70 hover:text-blue-300 transition-colors duration-200 focus:outline-none"
                    >
                        <!-- Eye Open Icon -->
                        <svg v-if="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <!-- Eye Closed Icon -->
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464M14.12 14.12l1.415 1.415M14.12 14.12L15.535 15.535M9.878 9.878L2.05 2.05l1.415-1.414 16.97 16.97-1.415 1.414-3.172-3.172" />
                        </svg>
                    </button>
                </div>
                <InputError class="mt-2 text-red-300 text-sm" :message="form.errors.password" />
            </div>

            <!-- Enhanced Remember Me & Forgot Password -->
            <div class="flex items-center justify-between pt-2">
                <label class="flex items-center group cursor-pointer">
                    <Checkbox 
                        name="remember" 
                        v-model:checked="form.remember"
                        class="rounded-md border-white/30 bg-white/10 text-blue-500 focus:ring-blue-400 focus:ring-offset-0 focus:ring-2 transition-all duration-200"
                    />
                    <span class="ml-3 text-sm text-blue-100/80 group-hover:text-blue-100 transition-colors duration-200">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-blue-200/80 hover:text-blue-100 transition-colors duration-200 font-medium relative group"
                >
                    Forgot password?
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-300 transition-all duration-200 group-hover:w-full"></span>
                </Link>
            </div>

            <!-- Enhanced Login Button -->
            <div class="space-y-4 pt-2">
                <PrimaryButton
                    class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 active:from-blue-800 active:to-cyan-800 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:ring-offset-2 focus:ring-offset-transparent"
                    :class="{ 'opacity-60 cursor-not-allowed pointer-events-none': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Signing you in...
                    </span>
                    <span v-else class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Sign In to Vheego
                    </span>
                </PrimaryButton>
            </div>
        </form>

        <!-- Enhanced Divider -->
        <div class="mt-6 mb-4">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-white/20"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white/10 text-blue-100/70 rounded-lg backdrop-blur-sm border border-white/10">
                        New to Vheego?
                    </span>
                </div>
            </div>
        </div>

        <!-- Enhanced Sign Up Link -->
        <div v-if="registrationEnabled" class="text-center animate-fade-in">
            <Link
                :href="route('register')"
                class="group inline-flex items-center justify-center w-full py-4 px-6 border border-white/30 rounded-xl text-blue-100 bg-white/5 hover:bg-white/10 hover:text-white hover:border-white/40 transition-all duration-200 font-medium backdrop-blur-sm transform hover:scale-[1.02] active:scale-[0.98]"
            >
                <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Create New Account
            </Link>
        </div>
        
        <!-- Registration Disabled Message -->
        <div v-else class="text-center animate-fade-in">
            <div class="inline-flex items-center justify-center w-full py-4 px-6 border border-red-300/30 rounded-xl text-red-200 bg-red-500/10 backdrop-blur-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                Registration Currently Disabled
            </div>
        </div>

        <!-- Trust Indicators -->
        <div class="mt-6 text-center space-y-2 animate-fade-in">
            <div class="flex items-center justify-center space-x-4 text-xs text-blue-200/60">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Secure Login</span>
                </div>
                <div class="w-1 h-1 bg-blue-300/40 rounded-full"></div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Privacy Protected</span>
                </div>
                <div class="w-1 h-1 bg-blue-300/40 rounded-full"></div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Fast Access</span>
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

@keyframes fadeInScale {
    0% {
        transform: scale(0.9);
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

.animate-fade-in-scale {
    animation: fadeInScale 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
input:focus {
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

/* Custom underline animation */
.group:hover .group-hover\:w-full {
    width: 100%;
}

/* Better disabled state */
button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

/* Enhanced input states */
input:hover:not(:focus) {
    background-color: rgba(255, 255, 255, 0.15);
}

/* Refined checkbox styles */
input[type="checkbox"]:checked {
    background-color: #3b82f6;
    border-color: #3b82f6;
}

/* Improved button active state */
button:active:not(:disabled) {
    transform: scale(0.98);
}
</style>