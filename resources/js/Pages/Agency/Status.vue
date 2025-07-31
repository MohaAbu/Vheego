<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  agency: {
    type: Object,
    default: () => ({})
  }
});

const getStatusBadge = (status) => {
  const badges = {
    pending: {
      class: 'bg-amber-100 text-amber-800',
      icon: '⏳',
      text: 'Under Review'
    },
    approved: {
      class: 'bg-green-100 text-green-800',
      icon: '✅',
      text: 'Approved'
    },
    rejected: {
      class: 'bg-red-100 text-red-800',
      icon: '❌',
      text: 'Rejected'
    }
  };
  return badges[status] || badges.pending;
};
</script>

<template>
  <Head title="Agency Verification Status" />
  
  <DashboardLayout role="renter">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-blue-800">Agency Verification Status</h1>
          <p class="text-slate-600">Track your agency application progress</p>
        </div>
        <div v-if="agency?.verification_status" class="flex items-center gap-2 px-4 py-2 rounded-lg" :class="getStatusBadge(agency.verification_status).class">
          <span class="text-lg">{{ getStatusBadge(agency.verification_status).icon }}</span>
          <span class="text-sm font-semibold">{{ getStatusBadge(agency.verification_status).text }}</span>
        </div>
      </div>
    </template>

    <div class="max-w-4xl mx-auto">
      <!-- Main Status Card -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
        <div v-if="agency && agency.verification_status">
          <div class="mb-6">
            <h3 class="text-lg font-bold text-slate-800 mb-2">Application Status</h3>
            <p class="text-sm text-slate-600">Current status of your agency verification</p>
          </div>

          <div class="space-y-6">
            <!-- Status Message -->
            <div class="p-4 rounded-lg border-l-4" 
                 :class="agency.verification_status === 'pending' ? 'bg-amber-50 border-amber-400' :
                         agency.verification_status === 'approved' ? 'bg-green-50 border-green-400' :
                         'bg-red-50 border-red-400'">
              <div class="flex items-start gap-3">
                <span class="text-2xl mt-1">{{ getStatusBadge(agency.verification_status).icon }}</span>
                <div>
                  <h4 class="font-semibold text-slate-800 mb-1">
                    {{ agency.verification_status === 'pending' ? 'Application Under Review' : 
                       agency.verification_status === 'approved' ? 'Agency Approved!' : 
                       'Application Rejected' }}
                  </h4>
                  <p class="text-slate-700 text-sm">
                    {{ agency.verification_status === 'pending' ? 'Your agency application is currently being reviewed by our team. We\'ll notify you via email once the review is complete.' : 
                       agency.verification_status === 'approved' ? 'Congratulations! Your agency has been approved and you can now start listing your vehicles.' : 
                       'Unfortunately, your agency application was not approved. Please review the feedback below and resubmit your application.' }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
              <Link
                v-if="agency.verification_status === 'approved'"
                href="/manage/cars"
                class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 19h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Start Managing Fleet
              </Link>
              
              <Link
                v-if="agency.verification_status === 'rejected'"
                href="/agency/setup"
                class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold rounded-xl hover:from-green-700 hover:to-green-800 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Resubmit Application
              </Link>

              <Link
                href="/agency/settings"
                class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-slate-300 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-all duration-200"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Agency Settings
              </Link>
            </div>
          </div>
        </div>

        <!-- No Agency State -->
        <div v-else class="text-center py-12">
          <div class="w-16 h-16 bg-blue-100 rounded-xl mx-auto flex items-center justify-center text-2xl text-blue-600 mb-4">
            🏢
          </div>
          <h3 class="text-xl font-bold text-slate-800 mb-2">No Agency Application</h3>
          <p class="text-slate-600 mb-6">You haven't submitted an agency application yet. Get started by creating your agency profile.</p>
          <Link
            href="/agency/setup"
            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Agency Profile
          </Link>
        </div>
      </div>

      <!-- Agency Information -->
      <div v-if="agency && agency.name" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
        <div class="mb-6">
          <h3 class="text-lg font-bold text-slate-800 mb-2">Submitted Information</h3>
          <p class="text-sm text-slate-600">Details you provided in your application</p>
        </div>

        <!-- Agency Logo and Basic Info -->
        <div class="flex items-start gap-6 mb-8 pb-6 border-b border-slate-200">
          <div class="flex-shrink-0">
            <div class="w-24 h-24 rounded-xl overflow-hidden border-2 border-slate-200 bg-slate-50 relative">
              <img 
                v-if="agency.logo_url || agency.logo" 
                :src="agency.logo_url || `/storage/agency_logos/${agency.logo}`" 
                :alt="agency.name + ' Logo'"
                class="w-full h-full object-cover"
                @error="handleImageError"
              />
              <div 
                v-show="!hasValidLogo" 
                class="absolute inset-0 flex items-center justify-center text-slate-400"
              >
                <div class="text-center">
                  <svg class="w-8 h-8 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M7 19h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                  </svg>
                  <div class="text-xs font-medium">No Logo</div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex-1">
            <h4 class="text-xl font-bold text-slate-800 mb-2">{{ agency.name }}</h4>
            <p class="text-slate-600 text-sm mb-3">{{ agency.description }}</p>
            <div class="flex items-center gap-4 text-sm text-slate-500">
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Submitted {{ new Date(agency.submitted_at).toLocaleDateString() }}
              </span>
              <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ agency.address }}
              </span>
            </div>
          </div>
        </div>

        <!-- Detailed Information Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Contact Email</label>
            <p class="text-slate-800 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              {{ agency.contact_email }}
            </p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Contact Phone</label>
            <p class="text-slate-800 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
              {{ agency.contact_phone }}
            </p>
          </div>
        </div>
      </div>

      <!-- Admin Feedback -->
      <div v-if="agency?.admin_feedback" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="mb-4">
          <h3 class="text-lg font-bold text-slate-800 mb-2">
            <svg class="w-5 h-5 text-blue-600 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            Admin Feedback
          </h3>
          <p class="text-sm text-slate-600">Message from our review team</p>
        </div>
        
        <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
          <p class="text-slate-700">{{ agency.admin_feedback }}</p>
          <p v-if="agency.reviewed_at" class="text-sm text-slate-500 mt-3">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Reviewed on {{ new Date(agency.reviewed_at).toLocaleDateString() }}
          </p>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template> 