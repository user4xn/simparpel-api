<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import L from 'leaflet';
import { ref, onMounted } from 'vue';

const areas = ref([]);
const selectedArea = ref(null);
const map = ref(null);

// Default coordinates for the center of Indonesia
const defaultCoordinates = [-2.4833826, 117.8902853];

onMounted(async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/location-area');
        areas.value = response.data.data;

        // Initialize map with default coordinates
        map.value = L.map('map').setView(defaultCoordinates, 6);

        // Add OpenStreetMap tiles to the map
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map.value);
    } catch (error) {
        console.error('Error fetching data:', error);
    }
});
const showCoordinates = () => {
    if (selectedArea.value) {
        const coordinates = selectedArea.value.coordinates.map(coord => [coord.lat, coord.long]);
        map.value.eachLayer(layer => {
            if (layer instanceof L.Polygon) {
                map.value.removeLayer(layer);
            }
        });
        L.polygon(coordinates, { color: 'blue' }).addTo(map.value);
        
        // Fit the map's view to the selected area's polygon
        map.value.fitBounds(coordinates);
    }
};
</script>

<template>
    <Head title="Pelabuhan" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">List Data Pelabuhan</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-3">
                    <!-- Dropdown select element -->
                    <select v-model="selectedArea" @change="showCoordinates" class="p-2 rounded">
                        <option value="" disabled>Select an area</option>
                        <option v-for="area in areas" :value="area">{{ area.Name }}</option>
                    </select>

                    <!-- Map container -->
                    <div id="map" class="w-full h-96 mt-4"></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
