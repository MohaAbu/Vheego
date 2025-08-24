<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    carId: {
        type: Number,
        required: true,
    },
    isFavorited: {
        type: Boolean,
        required: true,
    },
});

const emit = defineEmits(['update']);

const loading = ref(false);
const favorited = ref(props.isFavorited);

watch(() => props.isFavorited, (val) => {
    favorited.value = val;
});

function showToast(message, type = 'success') {
    // Create a simple toast notification
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg text-white font-medium transition-all duration-300 transform translate-x-0 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        toast.style.opacity = '0';
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 3000);
}

const toggleFavorite = async () => {
    if (loading.value) return;
    loading.value = true;
    if (!favorited.value) {
        // Add to favorites
        router.post(
            route('favorites.store'),
            { car_id: props.carId },
            {
                preserveScroll: true,
                onSuccess: () => {
                    favorited.value = true;
                    showToast('Added to favorites!');
                    emit('update', true);
                },
                onError: (err) => {
                    showToast('Failed to add to favorites.', 'error');
                },
                onFinish: () => {
                    loading.value = false;
                },
            }
        );
    } else {
        // Remove from favorites
        router.delete(
            route('favorites.destroy', props.carId),
            {
                preserveScroll: true,
                onSuccess: () => {
                    favorited.value = false;
                    showToast('Removed from favorites.');
                    emit('update', false);
                },
                onError: (err) => {
                    showToast('Failed to remove from favorites.', 'error');
                },
                onFinish: () => {
                    loading.value = false;
                },
            }
        );
    }
};
</script>

<template>
    <button
        :aria-label="favorited ? 'Remove from favorites' : 'Add to favorites'"
        @click.prevent="toggleFavorite"
        :disabled="loading"
        class="focus:outline-none"
    >
        <svg v-if="favorited" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-6 h-6 text-red-500">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41 0.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6 text-gray-400 hover:text-red-500 transition-colors">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
        <span v-if="loading" class="ml-2 text-xs text-gray-500">...</span>
    </button>
</template> 