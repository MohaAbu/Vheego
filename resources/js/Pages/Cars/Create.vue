<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    make: '',
    model: '',
    year: '',
    category: '',
    license_plate: '',
    rental_price_per_day: '',
    fuel_type: '',
    transmission: '',
    seats: '',
    features: [],
    images: [],
    special_offer: false,
});

const categories = ['economy', 'luxury', 'suv', 'sedan', 'hatchback', 'convertible'];
const fuelTypes = ['gasoline', 'diesel', 'electric', 'hybrid'];
const transmissions = ['manual', 'automatic'];
const featureOptions = ['air conditioning', 'gps', 'bluetooth', 'usb', 'heated seats', 'sunroof'];

const handleImages = (e) => {
    form.images = Array.from(e.target.files);
};

const submit = () => {
    form.post(route('cars.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Add New Car" />
        <div class="py-12">
            <div class="max-w-2xl mx-auto bg-white p-8 rounded shadow">
                <h2 class="text-2xl font-bold mb-6">Add New Car</h2>
                <form @submit.prevent="submit" enctype="multipart/form-data">
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
                            <label class="block font-semibold mb-1">Car Images</label>
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
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Add Car</button>
                        <Link :href="route('cars.index')" class="bg-gray-300 text-gray-800 px-6 py-2 rounded hover:bg-gray-400">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 