<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 transform -translate-y-2"
        enter-to-class="opacity-100 transform translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 transform translate-y-0"
        leave-to-class="opacity-0 transform -translate-y-2"
    >
        <div
            v-if="show"
            :class="[
                'fixed top-4 right-4 z-50 max-w-md w-full rounded-lg shadow-lg p-4',
                'backdrop-blur-sm border',
                typeClasses
            ]"
        >
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <component :is="iconComponent" class="h-5 w-5" />
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium">{{ message }}</p>
                </div>
                <div class="ml-4 flex-shrink-0">
                    <button
                        @click="close"
                        class="inline-flex text-white/80 hover:text-white transition-colors duration-200"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, computed, onMounted, h } from 'vue';

const props = defineProps({
    type: {
        type: String,
        required: true,
        validator: value => ['success', 'error', 'info'].includes(value)
    },
    message: {
        type: String,
        required: true
    },
    duration: {
        type: Number,
        default: 5000
    }
});

const emit = defineEmits(['close']);

const show = ref(true);

const typeClasses = computed(() => {
    const classes = {
        success: 'bg-green-500/90 border-green-400/50 text-white',
        error: 'bg-red-500/90 border-red-400/50 text-white',
        info: 'bg-blue-500/90 border-blue-400/50 text-white'
    };
    return classes[props.type];
});

const iconComponent = computed(() => {
    const icons = {
        success: () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        })),
        error: () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
        })),
        info: () => h('svg', {
            fill: 'none',
            stroke: 'currentColor',
            viewBox: '0 0 24 24'
        }, h('path', {
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': '2',
            d: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
        }))
    };
    return icons[props.type];
});

const close = () => {
    show.value = false;
    setTimeout(() => {
        emit('close');
    }, 200);
};

onMounted(() => {
    if (props.duration > 0) {
        setTimeout(() => {
            close();
        }, props.duration);
    }
});
</script>