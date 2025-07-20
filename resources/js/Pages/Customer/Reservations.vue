<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const reservations = ref([]);
const loading = ref(true);
const showReviewModal = ref(false);
const reviewReservation = ref(null);
const reviewRating = ref(0);
const reviewComment = ref('');
const reviewSubmitting = ref(false);
const reviewError = ref('');

const fetchReservations = async () => {
    loading.value = true;
    try {
        const response = await fetch('/reservations');
        if (response.ok) {
            reservations.value = await response.json();
        } else {
            reservations.value = [];
        }
    } catch (e) {
        reservations.value = [];
    }
    loading.value = false;
};

onMounted(fetchReservations);

function openReviewModal(reservation) {
    reviewReservation.value = reservation;
    reviewRating.value = 0;
    reviewComment.value = '';
    reviewError.value = '';
    showReviewModal.value = true;
}

function closeReviewModal() {
    showReviewModal.value = false;
    reviewReservation.value = null;
    reviewRating.value = 0;
    reviewComment.value = '';
    reviewError.value = '';
}

async function submitReview() {
    if (!reviewRating.value) {
        reviewError.value = 'Please select a rating.';
        return;
    }
    reviewSubmitting.value = true;
    reviewError.value = '';
    try {
        const response = await fetch('/reviews', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                reservation_id: reviewReservation.value.id,
                rating: reviewRating.value,
                comment: reviewComment.value,
            }),
        });
        if (response.ok) {
            // Mark reservation as reviewed in UI
            reviewReservation.value.reviewed = true;
            closeReviewModal();
        } else {
            const data = await response.json();
            reviewError.value = data.message || 'Failed to submit review.';
        }
    } catch (e) {
        reviewError.value = 'Failed to submit review.';
    }
    reviewSubmitting.value = false;
}
</script>

<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-6">My Reservations</h2>
            <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>
            <div v-else>
                <div v-if="reservations.length" class="space-y-6">
                    <div v-for="reservation in reservations" :key="reservation.id" class="bg-white rounded shadow p-4 flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="font-bold text-lg mb-1">{{ reservation.car.make }} {{ reservation.car.model }} ({{ reservation.car.year }})</div>
                            <div class="text-sm text-gray-600 mb-1">{{ reservation.car.license_plate }}</div>
                            <div class="text-sm text-gray-600 mb-1">{{ reservation.start_date }} → {{ reservation.end_date }}</div>
                            <div class="text-sm text-gray-600 mb-1">Status: <span :class="{
                                'text-green-600': reservation.status === 'completed',
                                'text-yellow-600': reservation.status === 'active',
                                'text-gray-500': reservation.status === 'cancelled',
                            }">{{ reservation.status }}</span></div>
                        </div>
                        <div class="mt-4 md:mt-0 flex flex-col items-end gap-2">
                            <template v-if="reservation.status === 'completed'">
                                <template v-if="reservation.reviewed">
                                    <span class="text-green-600 font-semibold">Reviewed</span>
                                </template>
                                <template v-else>
                                    <button @click="openReviewModal(reservation)" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Leave a Review</button>
                                </template>
                            </template>
                            <Link :href="route('cars.show', reservation.car.id)" class="text-blue-600 hover:underline text-xs">View Car</Link>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-16">
                    <div class="text-2xl text-gray-400 mb-4">You have no reservations yet.</div>
                    <Link :href="route('cars.index')" class="bg-indigo-600 text-white px-6 py-3 rounded hover:bg-indigo-700 text-lg">
                        Browse Cars
                    </Link>
                </div>
            </div>
        </div>

        <!-- Review Modal -->
        <div v-if="showReviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md relative">
                <button @click="closeReviewModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">&times;</button>
                <h3 class="text-xl font-bold mb-4">Leave a Review</h3>
                <div class="mb-4 flex items-center gap-2">
                    <span v-for="star in 5" :key="star" @click="reviewRating = star" class="cursor-pointer">
                        <svg v-if="reviewRating >= star" class="w-7 h-7 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/></svg>
                        <svg v-else class="w-7 h-7 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/></svg>
                    </span>
                </div>
                <textarea v-model="reviewComment" rows="4" class="w-full border rounded p-2 mb-4" placeholder="Write your review (optional)"></textarea>
                <div v-if="reviewError" class="text-red-600 mb-2">{{ reviewError }}</div>
                <button @click="submitReview" :disabled="reviewSubmitting" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 w-full">
                    <span v-if="reviewSubmitting">Submitting...</span>
                    <span v-else>Submit Review</span>
                </button>
            </div>
        </div>
    </div>
</template> 