<template>
    <div class="relative">
      <select @change="handleAreaChange" class="w-full border-0">
        <option value="">Pilih Pelabuhan</option>
        <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.name }}</option>
      </select>
      <div id="map" :style="{ width: '100%', height: '600px', cursor: drawingMode ? 'crosshair' : 'default' }"></div>
      <div class="grid grid-cols-3 gap-4 p-3 absolute z-[1000] left-2 bottom-2">
          <button v-if="!drawingMode" @click="startDrawingLayer" class="py-2 px-4 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Tambah Pelabuhan
          </button>
          <button v-if="drawingMode" @click="sendNewArea" class="py-2 px-4 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-gray-800 focus:ring-offset-2 transition ease-in-out duration-150">
            Simpan
          </button>
          <button v-if="drawingMode" @click="cancelDrawing" class="py-2 px-4 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-gray-800 focus:ring-offset-2 transition ease-in-out duration-150">
            Batal
          </button>
      </div>
    </div>
</template>
  
<script>
  import L from 'leaflet';
  import 'leaflet/dist/leaflet.css';
  import axios from 'axios';
  import Swal from 'sweetalert2';
  import harbourMarker from '../../../public/images/harbour-marker.png';
  
  export default {
    mounted() {
      this.initializeMap();
      this.fetchAreas();
    },
    methods: {
      initializeMap() {
        this.map = L.map('map').setView([-2.4833826, 117.8902853], 5); // Default view at Indonesia
  
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(this.map);

        this.map.setMinZoom(5);
      },
      fetchAreas() {
        axios.get(`/api/harbour`)
          .then(response => {
            const data = response.data;
            if (data.status === 'success') {
                this.areas = data.data;

                this.areas.forEach(area => {
                    const markerIcon = L.icon({
                        iconUrl: harbourMarker,
                        iconSize: [32, 32],
                    });
                    const coordinates = area.coordinates.map(coord => [parseFloat(coord.lat), parseFloat(coord.long)]);
                    const polygon = L.polygon(coordinates).addTo(this.map);
                    const center = polygon.getBounds().getCenter();
                    const marker = L.marker(center,  { icon: markerIcon }).addTo(this.map);
                    marker.bindTooltip(area.name);
                });
            }
          })
          .catch(error => {
            console.error('Error fetching areas:', error);
          });
      },
      handleAreaChange(event) {
        const selectedId = parseInt(event.target.value);
        const selectedArea = this.areas.find(area => area.id === selectedId);
        if (selectedArea) {
          const coordinates = selectedArea.coordinates.map(coord => [parseFloat(coord.lat), parseFloat(coord.long)]);
          const polygon = L.polygon(coordinates).addTo(this.map);
          this.map.fitBounds(polygon.getBounds());

          this.map.fitBounds(polygon.getBounds()).setZoom(16);
        }
      },
      startDrawingLayer() {
        this.drawingMode = true;

        console.log('drawing mode', this.drawingMode);
        this.polygonLayer = L.polygon([], { color: 'blue' }).addTo(this.map);

        this.map.on('click', (e) => {
            if (this.drawingMode) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            this.polygonLayer.addLatLng([lat, lng]);
            }
        });
      },
      cancelDrawing() {
        this.drawingMode = false;

        console.log('drawing mode', this.drawingMode);
        this.map.off('click');
        this.map.removeLayer(this.polygonLayer);
      },
      sendNewArea() {
        this.drawingMode = false;
        this.drawingMode = false;
        const coordinates = this.polygonLayer.getLatLngs()[0];
        if (coordinates.length < 3) {
            Swal.fire('Error', 'Polygon harus memiliki setidaknya 3 titik koordinat.', 'error');
            this.map.removeLayer(this.polygonLayer);
            return;
        }

        Swal.fire({
            title: 'Masukan Nama Pelabuhan:',
            input: 'text',
            inputAttributes: {
            autocapitalize: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: (name) => {
            if (!name) {
                Swal.showValidationMessage('Harap masukan nama pelabuhan');
            }
            return name;
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then(result => {
            if (result.isConfirmed) {
            const areaName = result.value;

            axios.post('/api/harbour/upsert', {
                area_id: null,
                name: areaName,
                coordinates: coordinates.map(coord => ({
                lat: coord.lat.toFixed(6),
                long: coord.lng.toFixed(6)
                }))
            }).then(response => {
                if (response.status === 200) {
                Swal.fire('Sukses', 'Pelabuhan berhasil ditambahkan', 'success');
                this.fetchAreas();
                }
            }).catch(error => {
                console.error('Error creating new area:', error);
                Swal.fire('Error', 'Error saat mengirim data area', 'error');
                this.map.removeLayer(this.polygonLayer);
            }).finally(() => {
                this.map.off('click');
                this.map.removeLayer(this.polygonLayer);
            });
            }
        });
      }
    },
    data() {
      return {
        map: null,
        areas: [],
        drawingMode: false,
        polygonLayer: null,
        areaName: ''
      };
    }
  };
</script>
  
<style>
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
  