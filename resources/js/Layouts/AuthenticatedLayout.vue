<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import NavBar from '@/Components/NavBar.vue';
import Footer from '@/Components/Footer.vue';

const user = usePage().props.auth.user;
</script>

<template>
    <div>
        <div class="min-h-screen bg-slate-50">
            <NavBar :user="user" />
            <!-- Enhanced Page Heading -->
            <header class="bg-white shadow-sm border-b border-gray-200" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between">
                        <slot name="header" />
                    </div>
                </div>
            </header>
            <!-- Page Content -->
            <main class="py-6">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
            <Footer />
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for dropdowns */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Smooth transitions */
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>