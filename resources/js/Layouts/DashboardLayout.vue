<script setup>
import { computed, ref, onMounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

const props = defineProps({
  role: String, // 'admin' or 'renter'
});

const user = usePage().props.auth.user;
const globalSettings = usePage().props.globalSettings || {};
const sidebarOpen = ref(false);
const mounted = ref(false);
const showDropdown = ref(false);

const links = computed(() => {
  if (props.role === 'admin') {
    return [
      { name: 'Dashboard', href: '/admin/dashboard', icon: 'fa-solid fa-gauge', color: 'text-blue-500' },
      { name: 'Users', href: '/admin/users', icon: 'fa-solid fa-users', color: 'text-green-500' },
      { name: 'Agencies', href: '/admin/agencies', icon: 'fa-solid fa-building', color: 'text-orange-500' },
      { name: 'Reservations', href: '/admin/reservations', icon: 'fa-solid fa-calendar-check', color: 'text-yellow-500' },
      { name: 'Analytics', href: '/admin/analytics', icon: 'fa-solid fa-chart-line', color: 'text-purple-500' },
      { name: 'Settings', href: '/admin/settings', icon: 'fa-solid fa-gear', color: 'text-slate-500' },
    ];
  } else {
    return [
      { name: 'Agency Dashboard', href: '/dashboard', icon: 'fa-solid fa-gauge', color: 'text-blue-500' },
      { name: 'Fleet Management', href: '/manage/cars', icon: 'fa-solid fa-car', color: 'text-green-500' },
      { name: 'Reservations', href: '/agency/bookings', icon: 'fa-solid fa-calendar-check', color: 'text-yellow-500' },
      { name: 'Agency Profile', href: (user?.agency && user.agency.verification_status === 'approved') ? '/agency/settings' : '/agency/status', icon: 'fa-solid fa-building', color: 'text-purple-500' },
      { name: 'Analytics', href: '/agency/analytics', icon: 'fa-solid fa-chart-line', color: 'text-indigo-500' },
    ];
  }
});

const isActiveLink = (href) => {
  const currentPath = usePage().url;
  
  // Handle exact matches first
  if (currentPath === href) {
    return true;
  }
  
  // Check if current path starts with the href (for query parameters)
  // Remove query parameters from both URLs for comparison
  const currentPathWithoutQuery = currentPath.split('?')[0];
  const hrefWithoutQuery = href.split('?')[0];
  
  return currentPathWithoutQuery === hrefWithoutQuery;
};

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value;
}

onMounted(() => {
  mounted.value = true;
});
</script>

<template>
  <div class="dashboard-container">
    <!-- Background Elements -->
    <div class="background-gradient"></div>
    <div class="background-mesh"></div>
    
    <!-- Sidebar -->
    <aside :class="['sidebar', sidebarOpen ? 'sidebar-open' : 'sidebar-closed', mounted ? 'sidebar-mounted' : '']">
      <!-- Logo Section -->
      <div class="sidebar-header">
        <slot name="sidebar-header">
          <Link href="/" class="logo-container">
            <div v-if="globalSettings.logo" class="logo-image">
              <img :src="`/storage/${globalSettings.logo}`" 
                   :alt="globalSettings.platform_name || 'Vheego'" 
                   class="w-12 h-12 object-contain rounded-lg" />
            </div>
            <div v-else class="logo-icon">
              <span class="logo-text">{{ (globalSettings.platform_name || 'Vheego').charAt(0) }}</span>
              <div class="logo-glow"></div>
            </div>
            <div class="logo-content">
              <span class="logo-name">{{ globalSettings.platform_name || 'Vheego' }}</span>
              <span class="logo-subtitle">Dashboard</span>
            </div>
          </Link>
        </slot>
        <button class="sidebar-close" @click="toggleSidebar">
          <span class="close-icon"></span>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="sidebar-nav">
        <div class="nav-section">
          <div class="nav-label">MAIN</div>
          <Link 
            v-for="(link, index) in links" 
            :key="link.href" 
            :href="link.href"
            :class="[
              'flex items-center gap-3 px-4 py-2 rounded-lg font-medium transition-all',
              isActiveLink(link.href) ? 'bg-blue-100 text-blue-700' : 'text-slate-600 hover:bg-slate-100',
            ]"
          >
            <i :class="[link.icon, 'text-lg', link.color]"></i>
            <span class="flex-1">{{ link.name }}</span>
          </Link>
        </div>
      </nav>

      <!-- User Profile Section with Dropdown -->
      <div class="sidebar-footer">
        <div class="user-profile relative" @click="showDropdown = !showDropdown">
          <div class="user-avatar">
            <img :src="user?.profile_picture_url || '/images/user-placeholder.svg'" 
                 alt="Profile" 
                 @error="$event.target.src = '/images/user-placeholder.svg'" />
            <div class="user-status"></div>
          </div>
          <div class="user-info">
            <div class="user-name">{{ user?.name }}</div>
            <div class="user-role">{{ props.role === 'admin' ? 'Administrator' : 'Agency Owner' }}</div>
          </div>
          <div v-if="showDropdown" class="absolute left-0 bottom-12 w-40 bg-white rounded-lg shadow-lg border border-slate-100 z-50">
            <Link href="/profile" class="block px-4 py-2 text-slate-700 hover:bg-slate-100">Profile</Link>
            <Link href="/logout" method="post" as="button" class="block w-full text-left px-4 py-2 text-red-500 hover:bg-red-100">Logout</Link>
          </div>
        </div>
      </div>
    </aside>

    <!-- Mobile Overlay -->
    <div v-if="sidebarOpen" class="mobile-overlay" @click="toggleSidebar"></div>

    <!-- Main Content Area (flex-grow) -->
    <div class="main-content flex flex-col flex-1 min-h-screen">
      <!-- Top Navigation Bar -->
      <header class="top-bar">
        <div class="top-bar-left">
          <button class="mobile-menu-btn" @click="toggleSidebar">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
          </button>
          <div class="header-content">
            <slot name="header" />
          </div>
        </div>
        <div class="top-bar-right">
          <!-- Quick Actions removed -->
          <!-- User Menu removed -->
        </div>
      </header>
      <!-- Main Content -->
      <main class="content-area flex-1">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
/* Base Dashboard Container */
.dashboard-container {
  @apply h-screen flex flex-row relative overflow-hidden;
}

/* Background Elements */
.background-gradient {
  @apply fixed inset-0 z-0;
  background: linear-gradient(135deg, #f1f5f9 0%, #e0e7ef 100%);
}

.background-mesh {
  @apply fixed inset-0 z-0 opacity-20;
  background-image: 
    radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
    radial-gradient(circle at 75% 75%, rgba(16, 185, 129, 0.08) 0%, transparent 50%),
    radial-gradient(circle at 50% 50%, rgba(139, 92, 246, 0.08) 0%, transparent 50%);
  background-size: 100% 100%, 100% 100%, 100% 100%;
  animation: meshFloat 20s ease-in-out infinite;
}

@keyframes meshFloat {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  33% { transform: translateY(-10px) rotate(1deg); }
  66% { transform: translateY(10px) rotate(-1deg); }
}

/* Sidebar Styles */
.sidebar {
  @apply fixed md:static z-40 left-0 top-0 w-64 flex flex-col bg-white border-r border-slate-200 shadow-lg transition-all duration-300 h-screen;
}

.sidebar-closed {
  @apply -translate-x-full md:translate-x-0;
}

.sidebar-open {
  @apply translate-x-0;
}

/* Logo Section */
.sidebar-header {
  @apply flex items-center justify-between p-4 border-b border-slate-100 bg-white;
}

.logo-container {
  @apply flex items-center gap-4 transition-all duration-300 hover:scale-105;
}

.logo-icon {
  @apply relative w-12 h-12 rounded-2xl flex items-center justify-center overflow-hidden;
  background: linear-gradient(135deg, #3b82f6 0%, #38bdf8 100%);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
}

.logo-text {
  @apply text-white text-xl font-black relative z-10;
}

.logo-glow {
  @apply absolute inset-0 rounded-2xl bg-gradient-to-br from-blue-400 to-cyan-400 opacity-0 transition-opacity duration-300;
}

.logo-container:hover .logo-glow {
  @apply opacity-20;
}

.logo-content {
  @apply flex flex-col;
}

.logo-name {
  @apply text-slate-800 text-xl font-bold tracking-tight;
}

.logo-subtitle {
  @apply text-slate-400 text-sm font-medium;
}

.sidebar-close {
  @apply md:hidden w-8 h-8 rounded-lg bg-slate-200 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-400 transition-all duration-200;
}

.close-icon {
  @apply w-4 h-4;
  background: linear-gradient(45deg, transparent 40%, currentColor 40%, currentColor 60%, transparent 60%),
             linear-gradient(-45deg, transparent 40%, currentColor 40%, currentColor 60%, transparent 60%);
}

/* Navigation */
.sidebar-nav {
  @apply flex-1 py-6 px-2 overflow-y-auto bg-white;
}

.nav-section {
  @apply space-y-2;
}

.nav-label {
  @apply text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 px-2;
}

/* Sidebar Link Styles */
.sidebar-nav .flex.items-center {
  @apply transition-all duration-200;
}

.sidebar-nav .flex.items-center.bg-blue-100 {
  @apply text-blue-700 font-semibold shadow-sm;
}

.sidebar-nav .flex.items-center.text-slate-600:hover {
  @apply bg-blue-50 text-blue-600;
}

.sidebar-nav .flex.items-center.text-slate-600 {
  @apply font-medium;
}

/* Sidebar Footer */
.sidebar-footer {
  @apply p-4 border-t border-slate-100 bg-white;
}

.user-profile {
  @apply flex items-center gap-3 p-2 rounded-lg bg-slate-100 cursor-pointer;
}

.user-avatar {
  @apply relative;
}

.user-avatar img {
  @apply w-9 h-9 rounded-full object-cover border-2 border-blue-400 shadow;
}

.user-status {
  @apply absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 rounded-full border-2 border-white;
  animation: pulse 2s infinite;
}

.user-info {
  @apply flex-1;
}

.user-name {
  @apply text-slate-800 font-semibold text-sm;
}

.user-role {
  @apply text-slate-400 text-xs;
}

/* Mobile Overlay */
.mobile-overlay {
  @apply fixed inset-0 bg-black/20 z-30 md:hidden;
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Main Content */
.main-content {
  @apply flex-1 flex flex-col p-8 relative bg-white h-screen overflow-auto;
}

/* Top Bar */
.top-bar {
  @apply flex items-center justify-between px-6 py-4 relative bg-white border-b border-slate-100 shadow-sm;
}

.top-bar-left {
  @apply flex items-center gap-4 flex-1;
}

.mobile-menu-btn {
  @apply md:hidden flex flex-col justify-center items-center w-8 h-8 rounded-lg bg-slate-200 gap-1 transition-all duration-300 hover:bg-slate-300;
}

.hamburger-line {
  @apply w-4 h-0.5 bg-slate-400 rounded-full transition-all duration-300;
}

.mobile-menu-btn:hover .hamburger-line {
  @apply bg-blue-500;
}

.header-content {
  @apply flex-1;
}

.top-bar-right {
  @apply flex items-center gap-6;
}

/* Quick Actions */
.quick-actions {
  @apply flex items-center gap-2;
}

.action-btn {
  @apply relative w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all duration-300 hover:scale-110;
}

.action-icon {
  @apply text-lg;
}

.notification-badge {
  @apply absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-xs font-bold text-white;
  animation: pulse 2s infinite;
}

/* User Menu */
.user-menu {
  @apply flex items-center gap-3;
}

.user-avatar-small img {
  @apply w-8 h-8 rounded-full object-cover border-2 border-blue-400 shadow-lg;
}

.logout-btn {
  @apply flex items-center gap-2 px-4 py-2 rounded-xl bg-red-500/10 text-red-400 font-medium transition-all duration-300 hover:bg-red-500/20 hover:text-red-300 hover:scale-105;
}

.logout-icon {
  @apply text-sm;
}

/* Content Area */
.content-area {
  @apply flex-1 relative bg-white;
}

/* Responsive Design */
@media (max-width: 768px) {
  .sidebar {
    @apply w-60;
  }
  
  .main-content {
    @apply ml-0;
  }
  
  .top-bar {
    @apply px-4;
  }
  
  .content-area {
    @apply p-4;
  }
  
  .quick-actions {
    @apply hidden;
  }
}

/* Performance Optimizations */
.sidebar,
.top-bar,
.content-area {
  will-change: transform;
}

/* Smooth Scrolling */
.sidebar-nav {
  scrollbar-width: thin;
  scrollbar-color: rgba(148, 163, 184, 0.15) transparent;
}

.sidebar-nav::-webkit-scrollbar {
  width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.15);
  border-radius: 2px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
  background: rgba(148, 163, 184, 0.25);
}

/* Remove dark mode media query */
</style>