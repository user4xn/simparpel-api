<template>
  <div class="relative">
    <button class="bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 px-3 py-2 absolute z-[1000] right-2 top-2 rounded-md font-semibold text-white" @click="reFocusShipMarker">
        Re-Center
    </button>
    <div id="map" :style="{ width: '100%', height: '400px' }"></div>
  </div>
</template>

<script>
  import L from 'leaflet';
  import 'leaflet/dist/leaflet.css';
  import axios from 'axios';
  import shipMarkerIcon from '../../../public/images/ship-marker.png';

  export default {
    props: {
        shipDetail: Object, // Define the prop to accept ship details
        logParking: Object // Define the prop to accept ship details
    },

    mounted() {
        this.initializeMap();
        this.fetchAreas();
        this.initializeShipMarker();
    },
    methods: {
        initializeMap() {
        if(this.shipDetail.on_ground !== 1) {
          this.map = L.map('map').setView([this.shipDetail.lat, this.shipDetail.long], 15);
        } else {
          this.map = L.map('map').setView([this.logParking.lat, this.logParking.long], 15);
        }

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(this.map);
        },
        fetchAreas() {
        axios.get(`/api/harbour`)
            .then(response => {
            const data = response.data;
            if (data.status === 'success') {
                this.areas = data.data;

                this.areas.forEach(area => {
                    const coordinates = area.coordinates.map(coord => [parseFloat(coord.lat), parseFloat(coord.long)]);
                    const polygon = L.polygon(coordinates).addTo(this.map);
                });
            }
            })
            .catch(error => {
            console.error('Error fetching areas:', error);
            });
        },
        initializeShipMarker() {
            const markerIcon = L.icon({
                iconUrl: shipMarkerIcon,
                iconSize: [40, 40],
            });

            if(this.shipDetail.on_ground !== 1) {
              const shipMarker = L.marker([this.shipDetail.lat, this.shipDetail.long], { icon: markerIcon }).addTo(this.map);
              shipMarker.bindPopup(this.shipDetail.name);
            } else {
              const shipMarker = L.marker([this.logParking.lat, this.logParking.long], { icon: markerIcon }).addTo(this.map);
              shipMarker.bindPopup(this.shipDetail.name);
            }
        },
        reFocusShipMarker() {
          if(this.shipDetail.on_ground !== 1) {
            this.map.setView([this.shipDetail.lat, this.shipDetail.long], 16);
          } else {
            this.map.setView([this.logParking.lat, this.logParking.long], 16);
          }
        }
    },
    data() {
        return {
        map: null,
        areas: [],
        polygonLayer: null,
        areaName: ''
        };
    }
  };
</script>

<style scooped>
  html, body {
    height: 100%;
    margin: 0;
  }
  .leaflet-container {
    height: 400px;
    width: 100%;
    max-width: 100%;
    max-height: 100%;
  }
  .leaflet-control-attribution.leaflet-control {
    display: none;
  }
</style>
  