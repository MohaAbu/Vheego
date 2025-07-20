<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    car: Object,
});

const car = ref(props.car);
const loading = ref(false);
const categories = ['economy', 'luxury', 'suv', 'sedan', 'hatchback', 'convertible'];
const fuelTypes = ['gasoline', 'diesel', 'electric', 'hybrid'];
const transmissions = ['manual', 'automatic'];
const featureOptions = ['air conditioning', 'gps', 'bluetooth', 'usb', 'heated seats', 'sunroof'];
const existingImages = ref(car.value.images || []);
const newImages = ref([]);
const primaryImageId = ref(null);

const form = useForm({
    make: car.value.make || '',
    model: car.value.model || '',
    year: car.value.year || '',
    category: car.value.category || '',
    license_plate: car.value.license_plate || '',
    rental_price_per_day: car.value.rental_price_per_day || '',
    fuel_type: car.value.fuel_type || '',
    transmission: car.value.transmission || '',
    seats: car.value.seats || '',
    features: car.value.features ? (typeof car.value.features === 'string' ? JSON.parse(car.value.features) : car.value.features) : [],
    images: [], 
    primary_image_id: '',
    remove_image_ids: [],
    special_offer: car.value.special_offer || false,
});

const primary = existingImages.value.find(img => img.is_primary);
primaryImageId.value = primary ? primary.id : null;

const handleImages = (e) => {
    newImages.value = Array.from(e.target.files);
    form.images = newImages.value;
};

const removeExistingImage = (imageId) => {
    existingImages.value = existingImages.value.filter(img => img.id !== imageId);
    
    // Add to remove list
    if (!form.remove_image_ids.includes(imageId)) {
        form.remove_image_ids.push(imageId);
    }
    
    // If this was the primary image, reset primary
    if (primaryImageId.value === imageId) {
        primaryImageId.value = null;
    }
};

const setPrimaryImage = (imageId) => {
    primaryImageId.value = imageId;
    existingImages.value = existingImages.value.map(img => ({ ...img, is_primary: img.id === imageId }));
};

const submit = () => {
    // Log form data for debugging
    console.log('Form data before submit:', form.data());
    
    // Update form data before submission
    form.primary_image_id = primaryImageId.value || '';
    form.remove_image_ids = (car.value.images || [])
        .filter(img => !existingImages.value.some(ei => ei.id === img.id))
        .map(img => img.id);
    
    // Ensure features is an array
    if (!Array.isArray(form.features)) {
        form.features = [];
    }

    console.log('Form data after updates:', form.data());

    // Create FormData manually to ensure all fields are included
    const formData = new FormData();
    
    // Add all form fields explicitly
    formData.append('make', form.make || '');
    formData.append('model', form.model || '');
    formData.append('year', form.year || '');
    formData.append('category', form.category || '');
    formData.append('license_plate', form.license_plate || '');
    formData.append('rental_price_per_day', form.rental_price_per_day || '');
    formData.append('fuel_type', form.fuel_type || '');
    formData.append('transmission', form.transmission || '');
    formData.append('seats', form.seats || '');
    formData.append('features', JSON.stringify(form.features || []));
    formData.append('primary_image_id', form.primary_image_id || '');
    formData.append('remove_image_ids', JSON.stringify(form.remove_image_ids || []));
    formData.append('special_offer', form.special_offer || '');
    formData.append('_method', 'PUT');

    // Add new images
    if (form.images && form.images.length > 0) {
        form.images.forEach((image, index) => {
            formData.append(`images[${index}]`, image);
        });
    }

    // Use router.post with FormData
    router.post(`/cars/${car.value.id}`, formData, {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('cars.index'));
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
        },
        onBefore: () => {
            console.log('Submitting form data...');
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Car" />
        <div class="py-12">
            <div class="max-w-2xl mx-auto bg-white p-8 rounded shadow">
                <h2 class="text-2xl font-bold mb-6">Edit Car</h2>
                <div v-if="loading" class="text-center py-8">Loading...</div>
                <form v-else @submit.prevent="submit" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block font-semibold mb-1">Make</label>
                            <input v-model="form.make" type="text" class="w-full border rounded px-3 py-2" required />
                            <div v-if="form.errors.make" class="text-red-600 text-xs mt-1">{{ form.errors.make }}</div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Model</label>
                            <input v-model="form.model" type="text" class="w-full border rounded px-3 py-2" required />
                            <div v-if="form.errors.model" class="text-red-600 text-xs mt-1">{{ form.errors.model }}</div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Year</label>
                            <input v-model="form.year" type="number" min="1900" :max="new Date().getFullYear() + 1" class="w-full border rounded px-3 py-2" required />
                            <div v-if="form.errors.year" class="text-red-600 text-xs mt-1">{{ form.errors.year }}</div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Category</label>
                            <select v-model="form.category" class="w-full border rounded px-3 py-2" required>
                                <option value="" disabled>Select category</option>
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat.charAt(0).toUpperCase() + cat.slice(1) }}</option>
                            </select>
                            <div v-if="form.errors.category" class="text-red-600 text-xs mt-1">{{ form.errors.category }}</div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">License Plate</label>
                            <input v-model="form.license_plate" type="text" class="w-full border rounded px-3 py-2" required />
                            <div v-if="form.errors.license_plate" class="text-red-600 text-xs mt-1">{{ form.errors.license_plate }}</div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Rental Price Per Day ($)</label>
                            <input v-model="form.rental_price_per_day" type="number" min="0" step="0.01" class="w-full border rounded px-3 py-2" required />
                            <div v-if="form.errors.rental_price_per_day" class="text-red-600 text-xs mt-1">{{ form.errors.rental_price_per_day }}</div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Fuel Type</label>
                            <select v-model="form.fuel_type" class="w-full border rounded px-3 py-2" required>
                                <option value="" disabled>Select fuel type</option>
                                <option v-for="ft in fuelTypes" :key="ft" :value="ft">{{ ft.charAt(0).toUpperCase() + ft.slice(1) }}</option>
                            </select>
                            <div v-if="form.errors.fuel_type" class="text-red-600 text-xs mt-1">{{ form.errors.fuel_type }}</div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Transmission</label>
                            <select v-model="form.transmission" class="w-full border rounded px-3 py-2" required>
                                <option value="" disabled>Select transmission</option>
                                <option v-for="tr in transmissions" :key="tr" :value="tr">{{ tr.charAt(0).toUpperCase() + tr.slice(1) }}</option>
                            </select>
                            <div v-if="form.errors.transmission" class="text-red-600 text-xs mt-1">{{ form.errors.transmission }}</div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Seats</label>
                            <input v-model="form.seats" type="number" min="1" max="20" class="w-full border rounded px-3 py-2" required />
                            <div v-if="form.errors.seats" class="text-red-600 text-xs mt-1">{{ form.errors.seats }}</div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Features</label>
                            <div class="flex flex-wrap gap-2">
                                <label v-for="feature in featureOptions" :key="feature" class="inline-flex items-center">
                                    <input type="checkbox" :value="feature" v-model="form.features" class="mr-1" />
                                    {{ feature }}
                                </label>
                            </div>
                            <div v-if="form.errors.features" class="text-red-600 text-xs mt-1">{{ form.errors.features }}</div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Existing Images</label>
                            <div class="flex flex-wrap gap-4 mb-2">
                                <div v-for="img in existingImages" :key="img.id" class="relative group">
                                    <img :src="`/storage/${img.image_path}`" class="w-24 h-16 object-cover rounded border" />
                                    <button type="button" @click="removeExistingImage(img.id)" class="absolute top-0 right-0 bg-red-600 text-white rounded-full px-1 py-0.5 text-xs opacity-80 group-hover:opacity-100">&times;</button>
                                    <button type="button" @click="setPrimaryImage(img.id)" :class="['absolute left-0 bottom-0 px-2 py-0.5 text-xs rounded-t bg-indigo-600 text-white', img.is_primary ? 'font-bold' : 'opacity-60']">Primary</button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Add New Images</label>
                            <input type="file" multiple accept="image/*" @change="handleImages" class="w-full" />
                            <div v-if="form.errors.images" class="text-red-600 text-xs mt-1">{{ form.errors.images }}</div>
                        </div>
                        <div class="mt-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="form.special_offer" class="mr-2" />
                                <span class="font-semibold">Mark as Special Offer</span>
                            </label>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-4">
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Update Car</button>
                        <Link :href="route('cars.index')" class="bg-gray-300 text-gray-800 px-6 py-2 rounded hover:bg-gray-400">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>