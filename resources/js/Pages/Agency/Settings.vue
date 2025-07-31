<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  agency: {
    type: Object,
    default: () => ({})
  },
});

const form = useForm({
  name: props.agency?.name || '',
  contact_email: props.agency?.contact_email || '',
  contact_phone: props.agency?.contact_phone || '',
  address: props.agency?.address || '',
  description: props.agency?.description || '',
  logo: null,
});

const previewUrl = ref(props.agency?.logo_url || '/images/agency-placeholder.png');

function onLogoChange(e) {
  const file = e.target.files[0];
  if (file) {
    form.logo = file;
    previewUrl.value = URL.createObjectURL(file);
  } else {
    form.logo = null;
    previewUrl.value = props.agency?.logo_url || '/images/agency-placeholder.png';
  }
}

function submitForm() {
  // Create FormData manually to ensure proper file upload
  const formData = new FormData();
  
  // Add all form fields
  formData.append('name', form.name || '');
  formData.append('contact_email', form.contact_email || '');
  formData.append('contact_phone', form.contact_phone || '');
  formData.append('address', form.address || '');
  formData.append('description', form.description || '');
  
  // Add logo if present
  if (form.logo) {
    formData.append('logo', form.logo);
  }
  
  // Use axios to submit the form with proper headers
  window.axios.post(route('agency.settings.update'), formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  })
  .then(() => {
    form.reset('logo');
    // Refresh the page to show updated data
    window.location.reload();
  })
  .catch((error) => {
    console.error('Upload error:', error);
    if (error.response?.data?.errors) {
      Object.keys(error.response.data.errors).forEach(key => {
        form.setError(key, error.response.data.errors[key][0]);
      });
    }
  });
}
</script>

<template>
  <Head title="Agency Settings" />
  
  <DashboardLayout role="renter">
    <template #header>
      <div>
        <h1 class="text-3xl font-bold text-blue-800">Agency Settings</h1>
        <p class="text-slate-600">Manage your agency information and preferences</p>
      </div>
    </template>

    <div class="max-w-4xl">
      <form @submit.prevent="submitForm" class="space-y-8">
        <!-- Agency Information -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
          <div class="mb-6">
            <h3 class="text-lg font-bold text-slate-800 mb-2">Agency Information</h3>
            <p class="text-sm text-slate-600">Update your agency details and contact information</p>
          </div>

          <!-- Logo Upload -->
          <div class="mb-8">
            <label class="block text-sm font-semibold text-slate-700 mb-4">Agency Logo</label>
            <div class="flex items-center gap-6">
              <div class="relative">
                <img
                  :src="previewUrl"
                  alt="Agency Logo"
                  class="w-24 h-24 rounded-2xl object-cover border-4 border-white shadow-2xl ring-4 ring-blue-100/50"
                />
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/80 to-indigo-500/80 rounded-2xl opacity-0 hover:opacity-100 transition-all duration-300 flex items-center justify-center cursor-pointer group backdrop-blur-sm">
                  <div class="transform group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                  </div>
                </div>
              </div>
              <div>
                <input
                  type="file"
                  accept="image/*"
                  @change="onLogoChange"
                  id="logo-upload"
                  class="hidden"
                />
                <label for="logo-upload" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200 cursor-pointer shadow-lg hover:shadow-xl">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                  </svg>
                  Update Logo
                </label>
                <p class="text-sm text-slate-500 mt-2">JPG, PNG or WebP (max. 2MB)</p>
                <div v-if="form.errors.logo" class="text-red-500 text-sm mt-1">{{ form.errors.logo }}</div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Agency Name -->
            <div class="md:col-span-2">
              <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Agency Name</label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                placeholder="Enter your agency name"
              />
              <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
            </div>

            <!-- Contact Email -->
            <div>
              <label for="contact_email" class="block text-sm font-semibold text-slate-700 mb-2">Contact Email</label>
              <input
                id="contact_email"
                v-model="form.contact_email"
                type="email"
                required
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                placeholder="agency@example.com"
              />
              <div v-if="form.errors.contact_email" class="text-red-500 text-sm mt-1">{{ form.errors.contact_email }}</div>
            </div>

            <!-- Contact Phone -->
            <div>
              <label for="contact_phone" class="block text-sm font-semibold text-slate-700 mb-2">Contact Phone</label>
              <input
                id="contact_phone"
                v-model="form.contact_phone"
                type="tel"
                required
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                placeholder="+1 (555) 123-4567"
              />
              <div v-if="form.errors.contact_phone" class="text-red-500 text-sm mt-1">{{ form.errors.contact_phone }}</div>
            </div>

            <!-- Address -->
            <div class="md:col-span-2">
              <label for="address" class="block text-sm font-semibold text-slate-700 mb-2">Business Address</label>
              <input
                id="address"
                v-model="form.address"
                type="text"
                required
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                placeholder="123 Main Street, City, State, ZIP"
              />
              <div v-if="form.errors.address" class="text-red-500 text-sm mt-1">{{ form.errors.address }}</div>
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
              <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">Agency Description</label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                required
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none"
                placeholder="Describe your agency, services, and what makes you special..."
              ></textarea>
              <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-4 pt-6">
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            {{ form.processing ? 'Saving Changes...' : 'Save Changes' }}
          </button>

          <div v-if="form.recentlySuccessful" class="flex items-center gap-2 text-green-600">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium">Settings saved successfully!</span>
          </div>
        </div>
      </form>

      <!-- Agency Status -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mt-8">
        <div class="mb-6">
          <h3 class="text-lg font-bold text-slate-800 mb-2">Agency Status</h3>
          <p class="text-sm text-slate-600">Current verification and operational status</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200/50">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              </div>
              <span class="font-semibold text-green-800">Verification Status</span>
            </div>
            <p class="text-sm text-green-700">Your agency is verified and approved for operations</p>
          </div>

          <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200/50">
            <div class="flex items-center gap-3 mb-2">
              <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 19h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
              </div>
              <span class="font-semibold text-blue-800">Fleet Status</span>
            </div>
            <p class="text-sm text-blue-700">
              {{ agency?.cars?.length || 0 }} vehicles registered in your fleet
            </p>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>