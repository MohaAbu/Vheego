<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const { user, mustVerifyEmail, status } = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    user: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: user.name,
    email: user.email,
    profile_picture: null,
});

const previewUrl = ref(user.profile_picture_url || '/images/user-placeholder.svg');

function onProfilePictureChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.profile_picture = file;
        previewUrl.value = URL.createObjectURL(file);
    } else {
        form.profile_picture = null;
        previewUrl.value = user.profile_picture_url || '/images/user-placeholder.svg';
    }
}

function submitForm() {
    const formData = new FormData();
    formData.append('name', form.name || '');
    formData.append('email', form.email || '');
    formData.append('_method', 'PATCH');
    if (form.profile_picture) {
        formData.append('profile_picture', form.profile_picture);
    }
    router.post(route('profile.update'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            form.recentlySuccessful = true;
            
            // Check if email was changed by comparing with the original user email
            if (form.email !== user.email) {
                form.status = 'verification-link-sent';
            }
            
            setTimeout(() => {
                form.recentlySuccessful = false;
            }, 2000);
            window.location.reload(); // Force reload to update NavBar profile picture
        },
        onError: (errors) => {
            form.errors = errors;
            if (errors.email) {
                // Focus the email input if there's an error
                document.getElementById('email').focus();
            }
        },
        onStart: () => {
            form.processing = true;
        },
        onFinish: () => {
            form.processing = false;
        },
    });
}
</script>

<template>
    <section>
        <!-- Profile Picture Upload -->
        <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6 mb-6">
            <div class="relative">
                <img
                    :src="previewUrl"
                    alt="Profile Picture"
                    class="w-20 h-20 rounded-xl object-cover border-2 border-slate-200 shadow-sm"
                />
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/80 to-indigo-500/80 rounded-xl opacity-0 hover:opacity-100 transition-all duration-300 flex items-center justify-center cursor-pointer group backdrop-blur-sm">
                    <div class="transform group-hover:scale-110 transition-transform duration-200">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="text-center sm:text-left">
                <input
                    type="file"
                    accept="image/*"
                    @change="onProfilePictureChange"
                    id="profile-picture-upload"
                    class="hidden"
                />
                <label for="profile-picture-upload" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200 cursor-pointer shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Upload Photo
                </label>
                <p class="text-sm text-slate-500 mt-2">JPG, PNG or GIF (max. 2MB)</p>
            </div>
        </div>
        <InputError class="mb-4" :message="form.errors.profile_picture" />

        <form
            @submit.prevent="submitForm"
            class="space-y-6"
            enctype="multipart/form-data"
        >
            <div>
                <InputLabel for="name" value="Full Name" class="text-slate-700 font-medium mb-2" />
                <TextInput
                    id="name"
                    type="text"
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Enter your full name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email Address" class="text-slate-700 font-medium mb-2" />
                <TextInput
                    id="email"
                    type="email"
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="Enter your email address"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm text-yellow-800 font-medium mb-1">Email verification required</p>
                        <p class="text-sm text-yellow-700">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="font-medium text-yellow-800 underline hover:text-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>
                        <div
                            v-show="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded px-3 py-1"
                        >
                            A new verification link has been sent to your email address.
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-4 pt-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
                >
                    <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </button>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 transform translate-x-2"
                    enter-to-class="opacity-100 transform translate-x-0"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-from-class="opacity-100 transform translate-x-0"
                    leave-to-class="opacity-0 transform translate-x-2"
                >
                    <div
                        v-if="form.recentlySuccessful"
                        class="flex items-center gap-2 text-green-600"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium">Changes saved successfully!</span>
                    </div>
                </Transition>
            </div>
        </form>
    </section>
</template>