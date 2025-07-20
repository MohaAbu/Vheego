<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verify Email - Vheego" />

        <!-- Enhanced Page Header -->
        <div class="text-center mb-6 animate-slide-in">
            <h1 class="text-2xl font-bold text-white mb-2 font-heading">Verify Your Email</h1>
            <p class="text-blue-100/80 text-sm">
                We've sent a verification link to your email address
            </p>
        </div>

        <!-- Main Content -->
        <div class="space-y-6 animate-scale-in">
            <!-- Instructions -->
            <div class="p-4 bg-blue-500/20 border border-blue-400/30 rounded-2xl text-blue-100 text-sm backdrop-blur-sm">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-3 mt-0.5 text-blue-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-medium mb-1">Check your email</p>
                        <p class="text-blue-200/80">
                            Please click the verification link we sent to your email address. If you don't see it, check your spam folder.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            <div v-if="verificationLinkSent" class="p-4 bg-green-500/20 border border-green-400/30 rounded-2xl text-green-100 text-sm font-medium backdrop-blur-sm animate-fade-in-scale">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-4">
                <!-- Resend Button -->
                <form @submit.prevent="submit">
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
                            Sending...
                        </span>
                        <span v-else class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Resend Verification Email
                        </span>
                    </PrimaryButton>
                </form>

                <!-- Logout Link -->
                <div class="text-center">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="inline-flex items-center text-blue-200/80 hover:text-blue-100 transition-colors duration-200 font-medium relative group"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Log Out
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-300 transition-all duration-200 group-hover:w-full"></span>
                    </Link>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
