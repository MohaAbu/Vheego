<template>
  <nav class="bg-white/95 backdrop-blur-sm border-b border-gray-200/50 px-4 py-3 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
      <!-- Logo Section -->
      <a href="/" class="flex items-center group">
        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-xl p-2 mr-3 group-hover:scale-110 transition-transform duration-300">
          <span class="text-white text-xl font-bold">V</span>
        </div>
        <span class="font-bold text-2xl bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">
          Vheego
        </span>
      </a>

      <!-- Desktop Navigation Menu -->
      <div class="hidden md:flex items-center space-x-8">
        <a 
          href="/" 
          class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200 relative group"
        >
          Home
          <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 group-hover:w-full transition-all duration-300"></span>
        </a>
        <a 
          href="/cars" 
          class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200 relative group"
        >
          Browse Cars
          <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 group-hover:w-full transition-all duration-300"></span>
        </a>
        <a 
          href="/agencies" 
          class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200 relative group"
        >
          Agencies
          <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 group-hover:w-full transition-all duration-300"></span>
        </a>
        <a 
          v-if="user && user.user_type === 'customer'"
          href="/reservations" 
          class="text-gray-700 hover:text-indigo-600 font-medium transition-colors duration-200 relative group"
        >
          <span class="inline-flex items-center">
            <svg class="w-5 h-5 mr-1 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4h12v2H4V4zm0 4h12v2H4V8zm0 4h12v2H4v-2z" /></svg>
            My Reservations
          </span>
          <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-500 group-hover:w-full transition-all duration-300"></span>
        </a>
        <a 
          v-if="user && user.user_type === 'customer'"
          href="/favorites" 
          class="text-gray-700 hover:text-pink-600 font-medium transition-colors duration-200 relative group"
        >
          <span class="inline-flex items-center">
            <svg class="w-5 h-5 mr-1 text-pink-500" fill="currentColor" viewBox="0 0 20 20"><path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" /></svg>
            My Favorites
          </span>
          <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-pink-500 group-hover:w-full transition-all duration-300"></span>
        </a>
        <a 
          href="/about" 
          class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200 relative group"
        >
          About
          <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 group-hover:w-full transition-all duration-300"></span>
        </a>
        <a 
          href="/contact" 
          class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200 relative group"
        >
          Contact
          <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 group-hover:w-full transition-all duration-300"></span>
        </a>
      </div>

      <!-- Auth Buttons & Mobile Menu -->
      <div class="flex items-center space-x-3">
        <!-- If user is logged in, show profile picture and dropdown -->
        <div v-if="user" class="relative" ref="profileDropdownRef">
          <button @click="toggleProfileDropdown" class="focus:outline-none">
            <img :src="user.profile_picture_url || '/default-avatar.png'" alt="Profile" class="w-10 h-10 rounded-full border-2 border-primary-electric object-cover shadow-sm hover:shadow-md transition" />
          </button>
          <transition name="fade">
            <div v-if="isProfileDropdownOpen" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
              <a v-if="user && user.user_type && user.user_type.toLowerCase().trim() === 'admin'" href="/dashboard" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-t-xl">Dashboard</a>
              <Link v-if="user && user.user_type && user.user_type.toLowerCase().trim() === 'admin'" href="/profile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</Link>
              <a v-else-if="user && user.user_type && user.user_type.toLowerCase().trim() === 'renter' && user.agency && user.agency.verification_status === 'approved'" href="/dashboard" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-t-xl">Dashboard</a>
              <Link v-else-if="user && user.user_type && user.user_type.toLowerCase().trim() === 'renter'" href="/profile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-t-xl">
                Profile
              </Link>
              <Link href="/logout" method="post" as="button" class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100 rounded-b-xl">
                Logout
              </Link>
            </div>
          </transition>
        </div>
        <!-- Desktop Auth Buttons (if not logged in) -->
        <div v-else class="hidden md:flex items-center space-x-3">
          <a 
            href="/login" 
            class="px-5 py-2.5 text-gray-700 hover:text-blue-600 font-medium transition-all duration-200 hover:bg-blue-50 rounded-lg"
          >
            Login
          </a>
          <a 
            href="/register" 
            class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg hover:from-blue-700 hover:to-cyan-700 font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
          >
            ✨ Sign Up
          </a>
        </div>
        <!-- Mobile Menu Button -->
        <button 
          @click="toggleMobileMenu"
          class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200"
          aria-label="Toggle mobile menu"
        >
          <div class="w-6 h-6 flex flex-col justify-center items-center">
            <span 
              class="w-5 h-0.5 bg-gray-600 transition-all duration-300"
              :class="{ 'rotate-45 translate-y-1': isMobileMenuOpen, 'mb-1': !isMobileMenuOpen }"
            ></span>
            <span 
              class="w-5 h-0.5 bg-gray-600 transition-all duration-300"
              :class="{ 'opacity-0': isMobileMenuOpen, 'mb-1': !isMobileMenuOpen }"
            ></span>
            <span 
              class="w-5 h-0.5 bg-gray-600 transition-all duration-300"
              :class="{ '-rotate-45 -translate-y-1': isMobileMenuOpen }"
            ></span>
          </div>
        </button>
      </div>
    </div>

    <!-- Mobile Menu Dropdown -->
    <transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-4"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-4"
    >
      <div 
        v-if="isMobileMenuOpen" 
        class="md:hidden mt-4 bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 overflow-hidden"
      >
        <!-- Mobile Navigation Links -->
        <div class="py-4">
          <a 
            href="/" 
            class="block px-6 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 font-medium transition-all duration-200"
          >
            Home
          </a>
          <a 
            href="/cars" 
            class="block px-6 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 font-medium transition-all duration-200"
          >
        Browse Cars
          </a>
          <a 
            href="/agencies" 
            class="block px-6 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 font-medium transition-all duration-200"
          >
            Agencies
          </a>
          <a 
            v-if="user && user.user_type === 'customer'"
            href="/reservations" 
            class="block px-6 py-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 font-medium transition-all duration-200"
          >
            <span class="inline-flex items-center">
              <svg class="w-5 h-5 mr-1 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4h12v2H4V4zm0 4h12v2H4V8zm0 4h12v2H4v-2z" /></svg>
              My Reservations
            </span>
          </a>
          <a 
            v-if="user && user.user_type === 'customer'"
            href="/favorites" 
            class="block px-6 py-3 text-gray-700 hover:text-pink-600 hover:bg-pink-50 font-medium transition-all duration-200"
          >
            <span class="inline-flex items-center">
              <svg class="w-5 h-5 mr-1 text-pink-500" fill="currentColor" viewBox="0 0 20 20"><path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" /></svg>
              My Favorites
            </span>
          </a>
          <a 
            href="/about" 
            class="block px-6 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 font-medium transition-all duration-200"
          >
            About
          </a>
          <a 
            href="/contact" 
            class="block px-6 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 font-medium transition-all duration-200"
          >
            Contact
          </a>
        </div>
        
        <!-- Mobile Auth Buttons -->
        <div class="border-t border-gray-200/50 p-4 space-y-3">
          <a 
            href="/login" 
            class="block w-full text-center px-5 py-3 text-gray-700 hover:text-blue-600 font-medium border border-gray-300 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-all duration-200"
          >
            Login
          </a>
          <a 
            href="/register" 
            class="block w-full text-center px-5 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg hover:from-blue-700 hover:to-cyan-700 font-medium transition-all duration-200 shadow-lg"
          >
            ✨ Sign Up
          </a>
        </div>
      </div>
    </transition>
  </nav>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Link } from '@inertiajs/vue3'
// Accept user as a prop
const props = defineProps({
  user: {
    type: Object,
    default: null,
  },
});

const isMobileMenuOpen = ref(false)

const isProfileDropdownOpen = ref(false)

const profileDropdownRef = ref(null)

// Toggle mobile menu
const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

// Toggle profile dropdown
const toggleProfileDropdown = () => {
  isProfileDropdownOpen.value = !isProfileDropdownOpen.value
}

// Close dropdowns when clicking outside
const handleClickOutside = (event) => {
  if (profileDropdownRef.value && !profileDropdownRef.value.contains(event.target)) {
    isProfileDropdownOpen.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Enhanced backdrop blur for modern browsers */
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Custom gradient text */
.bg-clip-text {
  background-clip: text;
  -webkit-background-clip: text;
}

/* Smooth transitions */
* {
  transition: all 0.3s ease;
}

/* Enhanced hover effects for nav links */
.group:hover .absolute {
  width: 100%;
}

/* Mobile menu animation improvements */
@media (max-width: 768px) {
  .transition-all {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
}

/* Sticky nav enhancement */
nav {
  transition: all 0.3s ease;
}

/* Optional: Add scroll effect */
.nav-scrolled {
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(12px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>    