<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password - Vheego" />

        <!-- Enhanced Page Header -->
        <div class="text-center mb-6 animate-slide-in">
            <h1 class="text-2xl font-bold text-white mb-2 font-heading">Reset Password</h1>
            <p class="text-blue-100/80 text-sm">
                Forgot your password? No problem. We'll send you a reset link.
            </p>
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

        <!-- Enhanced Form -->
        <form @submit.prevent="submit" class="space-y-5 animate-scale-in">
            <!-- Email Field -->
            <div class="space-y-2">
                <InputLabel for="email" value="Email Address" class="text-white font-medium text-sm" />
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-blue-300">
                        <svg class="h-5 w-5 text-blue-300/70 group-focus-within:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <input
                        id="email"
                        type="email"
                        class="w-full pl-12 pr-4 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-blue-200/60 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-400/50 backdrop-blur-sm transition-all duration-200 hover:bg-white/15"
                        :class="{ 'border-red-400 focus:ring-red-400/50 focus:border-red-400': form.errors.email }"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Enter your email address"
                    />
                </div>
                <InputError class="mt-2 text-red-300 text-sm" :message="form.errors.email" />
            </div>

            <!-- Submit Button -->
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
                        Sending Reset Link...
                    </span>
                    <span v-else class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Send Reset Link
                    </span>
                </PrimaryButton>
            </div>
        </form>

        <!-- Back to Login Link -->
        <div class="text-center mt-6 animate-fade-in">
            <Link
                :href="route('login')"
                class="inline-flex items-center justify-center text-blue-200/80 hover:text-blue-100 transition-colors duration-200 font-medium relative group"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Login
                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-300 transition-all duration-200 group-hover:w-full"></span>
            </Link>
        </div>
    </GuestLayout>
</template>
