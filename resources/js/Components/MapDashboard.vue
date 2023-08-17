<template>
    <div>
      <div id="map" :style="{ width: '100%', height: '600px'}"></div>
    </div>
</template>
  
<script>
  import L from 'leaflet';
  import 'leaflet/dist/leaflet.css';
  import axios from 'axios';
  import shipMarkerIcon from '../../../public/images/ship-marker.png';
  
  export default {
    mounted() {
      this.initializeMap();
      this.fetchAreas();
      this.fetchShips();
    },
    methods: {
      initializeMap() {
        this.map = L.map('map').setView([-2.4833826, 117.8902853], 5); // Default view at Indonesia
  
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
      fetchShips() {
        axios.get(`/api/ship`)
          .then(response => {
            const data = response.data;
            if (data.status === 'success') {
              this.ships = data.data;
              this.setShipMarkers();
            }
          })
          .catch(error => {
            console.error('Error fetching ships:', error);
          });
      },
      setShipMarkers() {
        this.ships.forEach(ship => {
          const markerIcon = L.icon({
            iconUrl: shipMarkerIcon,
            iconSize: [40, 40],
          });
          const shipMarker = L.marker([parseFloat(ship.lat), parseFloat(ship.long)], { icon: markerIcon }).addTo(this.map);
          shipMarker.bindPopup(ship.name);
        });
      },
    },
    data() {
      return {
        map: null,
        areas: [],
        ships: [],
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
</style>
  