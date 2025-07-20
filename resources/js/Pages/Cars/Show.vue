<script setup>
import FavoriteButton from '@/Components/FavoriteButton.vue';
import { ref, onMounted } from 'vue';
const props = defineProps({
  car: Object
});

const reviews = ref([]);
const loadingReviews = ref(true);
const averageRating = ref(0);

const fetchReviews = async () => {
  loadingReviews.value = true;
  try {
    const response = await fetch(`/cars/${props.car.id}/reviews`);
    if (response.ok) {
      const data = await response.json();
      reviews.value = data;
      if (data.length) {
        averageRating.value = (
          data.reduce((sum, r) => sum + r.rating, 0) / data.length
        ).toFixed(1);
      } else {
        averageRating.value = 0;
      }
    } else {
      reviews.value = [];
      averageRating.value = 0;
    }
  } catch (e) {
    reviews.value = [];
    averageRating.value = 0;
  }
  loadingReviews.value = false;
};

onMounted(() => {
  if (props.car && props.car.id) fetchReviews();
});

function renderStars(rating) {
  return Array.from({ length: 5 }, (_, i) => i < rating);
}
</script>

<template>
  <div class="py-12">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded shadow">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">Car Details</h2>
        <FavoriteButton :car-id="car.id" :is-favorited="car.is_favorited" v-if="car" />
      </div>
      <div v-if="car">
        <div class="mb-2"><strong>Make:</strong> {{ car.make }}</div>
        <div class="mb-2"><strong>Model:</strong> {{ car.model }}</div>
        <div class="mb-2"><strong>Year:</strong> {{ car.year }}</div>
        <div class="mb-2"><strong>Category:</strong> {{ car.category }}</div>
        <div class="mb-2"><strong>License Plate:</strong> {{ car.license_plate }}</div>
        <div class="mb-2"><strong>Rental Price Per Day:</strong> ${{ car.rental_price_per_day }}</div>
        <div class="mb-2"><strong>Fuel Type:</strong> {{ car.fuel_type }}</div>
        <div class="mb-2"><strong>Transmission:</strong> {{ car.transmission }}</div>
        <div class="mb-2"><strong>Seats:</strong> {{ car.seats }}</div>
        <div class="mb-2"><strong>Features:</strong> <span v-if="Array.isArray(car.features)">{{ car.features.join(', ') }}</span><span v-else>{{ car.features }}</span></div>
        <div class="mb-2"><strong>Images:</strong>
          <div class="flex flex-wrap gap-2 mt-2">
            <img v-for="img in car.images" :key="img.id" :src="`/storage/${img.image_path}`" class="w-24 h-16 object-cover rounded border" />
          </div>
        </div>
      </div>
      <div v-else>
        <p>No car data found.</p>
      </div>
    </div>

    <!-- Reviews Section -->
    <div class="max-w-2xl mx-auto bg-white p-8 rounded shadow mt-8">
      <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
        Customer Reviews
        <span v-if="averageRating > 0" class="flex items-center gap-1 text-yellow-500 text-lg">
          <span v-for="(filled, i) in renderStars(Math.round(averageRating))" :key="i">
            <svg v-if="filled" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/></svg>
            <svg v-else class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/></svg>
          </span>
          <span class="ml-1 text-gray-700 font-semibold">{{ averageRating }}</span>
        </span>
        <span v-else class="text-gray-400 text-base">No reviews yet</span>
      </h3>
      <div v-if="loadingReviews" class="text-gray-500">Loading reviews...</div>
      <div v-else-if="reviews.length === 0" class="text-gray-400">No reviews for this car yet.</div>
      <div v-else class="space-y-6">
        <div v-for="review in reviews" :key="review.id" class="border-b pb-4">
          <div class="flex items-center gap-2 mb-1">
            <span class="font-semibold">{{ review.customer?.name || 'Customer' }}</span>
            <span class="flex items-center gap-1 text-yellow-500">
              <span v-for="(filled, i) in renderStars(review.rating)" :key="i">
                <svg v-if="filled" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/></svg>
                <svg v-else class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.178c.969 0 1.371 1.24.588 1.81l-3.385 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.386-2.46a1 1 0 00-1.175 0l-3.386 2.46c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.385-2.46c-.783-.57-.38-1.81.588-1.81h4.178a1 1 0 00.95-.69l1.286-3.967z"/></svg>
              </span>
            </span>
            <span class="text-xs text-gray-400 ml-2">{{ new Date(review.created_at).toLocaleDateString() }}</span>
          </div>
          <div class="text-gray-700">{{ review.comment }}</div>
        </div>
      </div>
    </div>
  </div>
</template> 