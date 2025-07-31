<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
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
const newImagePreviewUrls = ref([]);
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
    const files = Array.from(e.target.files);
    newImages.value = files;
    form.images = files;
    
    // Create preview URLs for new images
    newImagePreviewUrls.value = files.map(file => URL.createObjectURL(file));
};

const removeNewImage = (index) => {
    // Clean up old URL
    URL.revokeObjectURL(newImagePreviewUrls.value[index]);
    
    // Remove from arrays
    newImages.value.splice(index, 1);
    newImagePreviewUrls.value.splice(index, 1);
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
    // Update form data before submission
    form.primary_image_id = primaryImageId.value || '';
    form.remove_image_ids = (car.value.images || [])
        .filter(img => !existingImages.value.some(ei => ei.id === img.id))
        .map(img => img.id);
    
    // Ensure features is an array
    if (!Array.isArray(form.features)) {
        form.features = [];
    }

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

const capitalize = (str) => {
    return str.charAt(0).toUpperCase() + str.slice(1);
};
</script>

<template>
    <Head title="Edit Vehicle" />
    
    <DashboardLayout role="renter">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-blue-800">Edit Vehicle</h1>
                    <p class="text-slate-600">Update your vehicle information and settings</p>
                </div>
                <Link 
                    :href="route('cars.index')" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-slate-200 text-slate-700 font-medium rounded-xl hover:bg-slate-300 transition-all duration-200"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Fleet
                </Link>
            </div>
        </template>

        <div class="max-w-4xl">
            <div v-if="loading" class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <p class="text-slate-600">Loading vehicle information...</p>
            </div>
            
            <form v-else @submit.prevent="submit" class="space-y-8">
                <!-- Vehicle Information -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-2">Vehicle Information</h3>
                        <p class="text-sm text-slate-600">Update the basic details about your vehicle</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Make</label>
                            <input 
                                v-model="form.make" 
                                type="text" 
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                placeholder="e.g. Toyota, BMW, Mercedes"
                                required 
                            />
                            <div v-if="form.errors.make" class="text-red-500 text-sm mt-1">{{ form.errors.make }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Model</label>
                            <input 
                                v-model="form.model" 
                                type="text" 
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                placeholder="e.g. Camry, X5, C-Class"
                                required 
                            />
                            <div v-if="form.errors.model" class="text-red-500 text-sm mt-1">{{ form.errors.model }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Year</label>
                            <input 
                                v-model="form.year" 
                                type="number" 
                                min="1900" 
                                :max="new Date().getFullYear() + 1" 
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                placeholder="2024"
                                required 
                            />
                            <div v-if="form.errors.year" class="text-red-500 text-sm mt-1">{{ form.errors.year }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Category</label>
                            <select 
                                v-model="form.category" 
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                required
                            >
                                <option value="" disabled>Select vehicle category</option>
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ capitalize(cat) }}</option>
                            </select>
                            <div v-if="form.errors.category" class="text-red-500 text-sm mt-1">{{ form.errors.category }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">License Plate</label>
                            <input 
                                v-model="form.license_plate" 
                                type="text" 
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                placeholder="ABC-1234"
                                required 
                            />
                            <div v-if="form.errors.license_plate" class="text-red-500 text-sm mt-1">{{ form.errors.license_plate }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Rental Price Per Day</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-500 font-medium">$</span>
                                <input 
                                    v-model="form.rental_price_per_day" 
                                    type="number" 
                                    min="0" 
                                    step="0.01" 
                                    class="w-full pl-8 pr-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                    placeholder="99.99"
                                    required 
                                />
                            </div>
                            <div v-if="form.errors.rental_price_per_day" class="text-red-500 text-sm mt-1">{{ form.errors.rental_price_per_day }}</div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Specifications -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-2">Vehicle Specifications</h3>
                        <p class="text-sm text-slate-600">Update technical details about your vehicle</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Fuel Type</label>
                            <select 
                                v-model="form.fuel_type" 
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                required
                            >
                                <option value="" disabled>Select fuel type</option>
                                <option v-for="ft in fuelTypes" :key="ft" :value="ft">{{ capitalize(ft) }}</option>
                            </select>
                            <div v-if="form.errors.fuel_type" class="text-red-500 text-sm mt-1">{{ form.errors.fuel_type }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Transmission</label>
                            <select 
                                v-model="form.transmission" 
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                required
                            >
                                <option value="" disabled>Select transmission</option>
                                <option v-for="tr in transmissions" :key="tr" :value="tr">{{ capitalize(tr) }}</option>
                            </select>
                            <div v-if="form.errors.transmission" class="text-red-500 text-sm mt-1">{{ form.errors.transmission }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Number of Seats</label>
                            <input 
                                v-model="form.seats" 
                                type="number" 
                                min="1" 
                                max="20" 
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                placeholder="5"
                                required 
                            />
                            <div v-if="form.errors.seats" class="text-red-500 text-sm mt-1">{{ form.errors.seats }}</div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Features -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-2">Vehicle Features</h3>
                        <p class="text-sm text-slate-600">Update the features available in your vehicle</p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <label v-for="feature in featureOptions" :key="feature" class="inline-flex items-center p-3 border border-slate-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 transition-all duration-200 cursor-pointer">
                            <input 
                                type="checkbox" 
                                :value="feature" 
                                v-model="form.features" 
                                class="mr-3 w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2" 
                            />
                            <span class="text-sm font-medium text-slate-700 capitalize">{{ feature }}</span>
                        </label>
                    </div>
                    <div v-if="form.errors.features" class="text-red-500 text-sm mt-2">{{ form.errors.features }}</div>
                </div>

                <!-- Vehicle Images -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-2">Vehicle Images</h3>
                        <p class="text-sm text-slate-600">Manage your vehicle photos and set the primary image</p>
                    </div>

                    <!-- Existing Images -->
                    <div v-if="existingImages.length > 0" class="mb-6">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Current Images</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div v-for="img in existingImages" :key="img.id" class="relative group">
                                <img :src="`/storage/${img.image_path}`" :alt="`Vehicle image`" class="w-full h-24 object-cover rounded-xl border border-slate-200" />
                                
                                <!-- Remove button -->
                                <button 
                                    type="button"
                                    @click="removeExistingImage(img.id)"
                                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors opacity-0 group-hover:opacity-100"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                
                                <!-- Primary button -->
                                <button 
                                    type="button" 
                                    @click="setPrimaryImage(img.id)" 
                                    :class="[
                                        'absolute bottom-2 left-2 px-2 py-1 text-xs rounded-lg font-medium transition-all duration-200',
                                        img.is_primary 
                                            ? 'bg-blue-600 text-white shadow-md' 
                                            : 'bg-white/80 text-slate-600 hover:bg-blue-100 hover:text-blue-600'
                                    ]"
                                >
                                    {{ img.is_primary ? 'Primary' : 'Set Primary' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Add New Images -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-center w-full">
                            <label for="images-upload" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all duration-200">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-4 text-slate-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                    </svg>
                                    <p class="mb-2 text-sm text-slate-500">
                                        <span class="font-semibold">Click to upload new images</span> or drag and drop
                                    </p>
                                    <p class="text-xs text-slate-400">PNG, JPG or WebP (MAX. 5MB each)</p>
                                </div>
                                <input 
                                    id="images-upload" 
                                    type="file" 
                                    multiple 
                                    accept="image/*" 
                                    @change="handleImages" 
                                    class="hidden" 
                                />
                            </label>
                        </div>

                        <!-- New Image Previews -->
                        <div v-if="newImagePreviewUrls.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div v-for="(url, index) in newImagePreviewUrls" :key="index" class="relative group">
                                <img :src="url" :alt="`New preview ${index + 1}`" class="w-full h-24 object-cover rounded-xl border border-slate-200" />
                                <button 
                                    type="button"
                                    @click="removeNewImage(index)"
                                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors opacity-0 group-hover:opacity-100"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <div class="absolute bottom-2 left-2 px-2 py-1 bg-green-500 text-white text-xs rounded-lg font-medium">
                                    New
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="form.errors.images" class="text-red-500 text-sm mt-2">{{ form.errors.images }}</div>
                </div>

                <!-- Special Options -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-2">Special Options</h3>
                        <p class="text-sm text-slate-600">Configure special settings for your vehicle</p>
                    </div>

                    <div class="flex items-center p-4 border border-slate-200 rounded-xl hover:bg-orange-50 hover:border-orange-200 transition-all duration-200">
                        <input 
                            type="checkbox" 
                            v-model="form.special_offer" 
                            id="special-offer"
                            class="w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500 focus:ring-2" 
                        />
                        <label for="special-offer" class="ml-3 cursor-pointer">
                            <div class="text-sm font-semibold text-slate-800">Mark as Special Offer</div>
                            <div class="text-xs text-slate-600">This vehicle will be highlighted with a special offer badge</div>
                        </label>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-4 pt-6">
                    <Link 
                        :href="route('cars.index')" 
                        class="px-6 py-3 bg-slate-200 text-slate-700 font-medium rounded-xl hover:bg-slate-300 transition-all duration-200"
                    >
                        Cancel
                    </Link>
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        {{ form.processing ? 'Updating Vehicle...' : 'Update Vehicle' }}
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>