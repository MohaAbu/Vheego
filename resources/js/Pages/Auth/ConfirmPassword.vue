<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    password: '',
});

const showPassword = ref(false);

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password - Vheego" />

        <!-- Enhanced Page Header -->
        <div class="text-center mb-6 animate-slide-in">
            <h1 class="text-2xl font-bold text-white mb-2 font-heading">Confirm Password</h1>
            <p class="text-blue-100/80 text-sm">
                This is a secure area. Please confirm your password to continue.
            </p>
        </div>

        <!-- Enhanced Form -->
        <form @submit.prevent="submit" class="space-y-5 animate-scale-in">
            <!-- Password Field -->
            <div class="space-y-2">
                <InputLabel for="password" value="Current Password" class="text-white font-medium text-sm" />
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
                        autofocus
                        placeholder="Enter your current password"
                    />
                    <!-- Password Toggle Button -->
                    <button 
                        type="button"
                        @click="togglePassword"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-blue-300/70 hover:text-blue-300 transition-colors duration-200 focus:outline-none"
                    >
                        <svg v-if="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464M14.12 14.12l1.415 1.415M14.12 14.12L15.535 15.535M9.878 9.878L2.05 2.05l1.415-1.414 16.97 16.97-1.415 1.414-3.172-3.172" />
                        </svg>
                    </button>
                </div>
                <InputError class="mt-2 text-red-300 text-sm" :message="form.errors.password" />
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
                        Confirming...
                    </span>
                    <span v-else class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Confirm Password
                    </span>
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
