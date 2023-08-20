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
  import harbourMarkerIcon from '../../../public/images/harbour-marker.png';
  
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
                    const markerIcon = L.icon({
                        iconUrl: harbourMarkerIcon,
                        iconSize: [32, 32],
                    });

                    const coordinates = area.coordinates.map(coord => [parseFloat(coord.lat), parseFloat(coord.long)]);
                    const harbourMarker = L.marker(coordinates[0],  { icon: markerIcon }).addTo(this.map);
                    harbourMarker.bindPopup(area.name);
                });
            }
          })
          .catch(error => {
            console.error('Error fetching areas:', error);
          });
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
        this.ships.forEach(async ship => {
          const markerIcon = L.icon({
            iconUrl: shipMarkerIcon,
            iconSize: [40, 40],
          });
          
          const shipCoordinates = [parseFloat(ship.lat), parseFloat(ship.long)];

          const shipMarker = L.marker(shipCoordinates, { icon: markerIcon }).addTo(this.map);
          shipMarker.bindPopup(ship.on_ground+'#',ship.name ? ship.name : 'Kapal Tidak Dikenal');
        });
      }
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
  .leaflet-control-attribution.leaflet-control {
    display: none;
  }
</style>
  