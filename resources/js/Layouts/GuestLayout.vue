<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';

const globalSettings = usePage().props.globalSettings || {};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-blue-900 to-indigo-900 flex items-center justify-center p-4 relative overflow-hidden">
        <!-- Enhanced Background Pattern -->
        <div class="absolute inset-0 opacity-[0.03]">
            <div class="guest-pattern"></div>
        </div>
        
        <!-- Floating geometric shapes -->
        <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-gradient-to-r from-blue-400/20 to-cyan-400/20 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-1/4 right-1/4 w-48 h-48 bg-gradient-to-r from-cyan-400/20 to-blue-400/20 rounded-full blur-3xl animate-float-delayed"></div>
        <div class="absolute top-1/2 right-1/3 w-32 h-32 bg-gradient-to-r from-indigo-400/15 to-purple-400/15 rounded-full blur-2xl animate-float-slow"></div>
        
        <!-- Subtle grid overlay -->
        <div class="absolute inset-0 opacity-[0.02]">
            <div class="grid-pattern"></div>
        </div>
        
        <!-- Main Content Container -->
        <div class="relative w-full max-w-sm z-10">
            <!-- Enhanced Logo Section - Logo Only -->
            <div class="text-center mb-8 animate-slide-in">
                <Link href="/" class="inline-block group">
                    <div class="flex items-center justify-center">
                        <!-- Use the same logo as navbar and footer -->
                        <img 
                            src="/images/Vheego.png" 
                            alt="Vheego Logo" 
                            class="h-24 w-auto transform transition-transform duration-300 group-hover:scale-105"
                        />
                    </div>
                </Link>
            </div>

            <!-- Enhanced Content Card -->
            <div class="relative animate-scale-in">
                <!-- Card glow effect -->
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-cyan-500/20 rounded-3xl blur-xl"></div>
                
                <!-- Main card -->
                <div class="relative bg-white/10 backdrop-blur-xl rounded-3xl border border-white/20 shadow-2xl overflow-hidden">
                    <!-- Subtle top border accent -->
                    <div class="h-1 bg-gradient-to-r from-blue-500 via-cyan-500 to-blue-500"></div>
                    
                    <!-- Card content -->
                    <div class="p-6">
                        <slot />
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</template>

<style scoped>
/* Enhanced background patterns */
.guest-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M30 30c0-6.627-5.373-12-12-12s-12 5.373-12 12 5.373 12 12 12 12-5.373 12-12zm12 0c0-6.627-5.373-12-12-12s-12 5.373-12 12 5.373 12 12 12 12-5.373 12-12z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.grid-pattern {
    background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='grid' width='10' height='10' patternUnits='userSpaceOnUse'%3E%3Cpath d='M 10 0 L 0 0 0 10' fill='none' stroke='%23ffffff' stroke-width='0.5' stroke-opacity='0.1'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='100' height='100' fill='url(%23grid)' /%3E%3C/svg%3E");
}

/* Enhanced animations */
@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
        opacity: 0.1;
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
        opacity: 0.3;
    }
}

@keyframes float-delayed {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
        opacity: 0.15;
    }
    50% {
        transform: translateY(-15px) rotate(-180deg);
        opacity: 0.25;
    }
}

@keyframes float-slow {
    0%, 100% {
        transform: translateY(0) scale(1);
        opacity: 0.1;
    }
    50% {
        transform: translateY(-10px) scale(1.1);
        opacity: 0.2;
    }
}

@keyframes slideIn {
    0% {
        transform: translateY(-30px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes scaleIn {
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

/* Animation classes */
.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-float-delayed {
    animation: float-delayed 8s ease-in-out infinite;
    animation-delay: 2s;
}

.animate-float-slow {
    animation: float-slow 10s ease-in-out infinite;
    animation-delay: 4s;
}

.animate-slide-in {
    animation: slideIn 0.8s ease-out;
}

.animate-scale-in {
    animation: scaleIn 0.6s ease-out;
    animation-delay: 0.2s;
    animation-fill-mode: both;
}

.animate-fade-in {
    animation: fadeIn 0.8s ease-out;
    animation-delay: 0.4s;
    animation-fill-mode: both;
}

/* Enhanced glass morphism */
.backdrop-blur-xl {
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
}

/* Smooth transitions for all elements */
* {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}

/* Custom scrollbar for any scrollable content */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* Focus styles for accessibility */
a:focus,
button:focus {
    outline: 2px solid rgba(59, 130, 246, 0.5);
    outline-offset: 2px;
}
</style>