<script setup>
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import axios from "axios";
import shipMarkerIcon from "../../../public/images/ship-marker.png";
import fishermanMarkerIcon from "../../../public/images/fisherman-marker.png";
import harbourMarkerIcon from "../../../public/images/harbour-marker.png";
import { ref } from "vue";
import Sidebar from "@/Components/Dashboard/Sidebar.vue";

var allMarkers = ref([]);
</script>

<template>
    <div style="position: relative; background: red">
        <Sidebar ref="findShipSidebar" @show-ship-event="ShowThisShip" />
        <div id="map" :style="{ width: '100%', height: '98vh' }"></div>

        <button @click="resetMapZoom()" v-if="showResetZoom" class="btn-reset">
            Reset Zoom
        </button>
        <button class="btn-find" @click="showSidebarFindShip">
            Temukan Kapal
        </button>
    </div>
</template>

<script>
export default {
    data() {
        return {
            map: null,
            areas: [],
            ships: [],
            polygonLayer: null,
            areaName: "",
            customIconSize: [16, 24],
            allIconMarkers: [],
            allHarbourMarkers: [],
            showResetZoom: false,
        };
    },

    mounted() {
        this.initializeMap();
        this.fetchAreas();
        this.fetchShips();
        this.mapOnZoom();
    },
    methods: {
        initializeMap() {
            // this.map = L.map('map').setView([-2.4833826, 117.8902853], 5);// Default view at Indonesia

            this.map = L.map("map").setView([-6.844464, 109.129036], 8);

            L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
                maxZoom: 30,
                maxNativeZoom: 18,
                attribution:
                    '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            }).addTo(this.map);
        },

        mapOnZoom() {
            var trigger_marker_change = (zoom_level) => {
                this.showResetZoom = zoom_level > 15 ? true : false;

                if (zoom_level > 17) {
                    var new_size = [28, 42];
                } else if (zoom_level > 11) {
                    var new_size = [20, 30];
                } else if (zoom_level < 8) {
                    var new_size = [10, 15];
                } else {
                    var new_size = [16, 24];
                }

                this.allIconMarkers.map((marker, index) => {
                    marker.setIcon(
                        L.icon({
                            iconUrl: marker.options.icon.options.iconUrl,
                            iconSize: new_size,
                        })
                    );
                });
            };

            var trigger_harbour_change = (zoom_level) => {
                if (zoom_level > 17) {
                    var new_size = [15, 15];
                } else if (zoom_level > 11) {
                    var new_size = [24, 24];
                } else if (zoom_level < 8) {
                    var new_size = [50, 50];
                } else {
                    var new_size = [30, 30];
                }

                this.allHarbourMarkers.map((marker, index) => {
                    console.log(marker);
                    marker.setIcon(
                        L.icon({
                            iconUrl: marker.options.icon.options.iconUrl,
                            iconSize: new_size,
                        })
                    );

                    L.DomUtil.addClass(marker._icon, "is-harbour");
                });
            };

            this.map.on("zoomend", function (peta) {
                var zoom_level = peta.target._zoom;
                trigger_marker_change(zoom_level);
                trigger_harbour_change(zoom_level);

                console.log("zoom level", zoom_level);
            });
        },

        fetchAreas() {
            var harbourMarkers = [];
            var pindah = (lat, long) => {
                this.reFocusShipMarker(lat, long);
            };

            axios
                .get(`/api/harbour`)
                .then((response) => {
                    const data = response.data;
                    if (data.status === "success") {
                        this.areas = data.data;

                        this.areas.forEach((area) => {
                            const markerIcon = L.icon({
                                iconUrl: harbourMarkerIcon,
                                iconSize: [50, 50],
                            });

                            const coordinates = area.coordinates.map(
                                (coord) => [
                                    parseFloat(coord.lat),
                                    parseFloat(coord.long),
                                ]
                            );

                            var harbourMarker = {};
                            harbourMarker = L.marker(coordinates[0], {
                                icon: markerIcon,
                            }).addTo(this.map);
                            harbourMarker.bindPopup(area.name);

                            harbourMarker.on("click", function (e) {
                                pindah(e.latlng.lat, e.latlng.lng);
                            });

                            L.DomUtil.addClass(
                                harbourMarker._icon,
                                "is-harbour"
                            );

                            harbourMarkers.push(harbourMarker);
                        });

                        this.allHarbourMarkers = harbourMarkers;
                    }
                })
                .catch((error) => {
                    console.error("Error fetching areas:", error);
                });
        },

        fetchShips() {
            axios
                .get(`/api/ship`)
                .then((response) => {
                    const data = response.data;
                    if (data.status === "success") {
                        this.ships = data.data;
                        this.setShipMarkers();
                    }
                })
                .catch((error) => {
                    console.error("Error fetching ships:", error);
                });
        },
        setShipMarkers() {
            var shipMarkers = [];

            var pindah = (lat, long) => {
                this.reFocusShipMarker(lat, long, 30);
            };

            this.ships.forEach(async (ship) => {
                const markerIcon = L.icon({
                    iconUrl: shipMarkerIcon,
                    iconSize: this.customIconSize,
                });

                const fisherMarkerIcon = L.icon({
                    iconUrl: fishermanMarkerIcon,
                    iconSize: this.customIconSize,
                });

                const status = ship.on_ground === 1 ? "OFFLINE" : ship.status;
                var shipMarker = {};

                if (ship.on_ground !== 1) {
                    const shipCoordinates = [
                        parseFloat(ship.lat),
                        parseFloat(ship.long),
                    ];

                    shipMarker = L.marker(shipCoordinates, {
                        icon: markerIcon,
                    }).addTo(this.map);
                    shipMarker.bindPopup(
                        ship.name
                            ? ship.name + " (" + status.toUpperCase() + ")"
                            : ship.device_id + " (" + status.toUpperCase() + ")"
                    );
                    shipMarker.on("click", function (e) {
                        pindah(e.latlng.lat, e.latlng.lng);
                    });
                } else {
                    const shipCoordinates = [
                        parseFloat(ship.lat),
                        parseFloat(ship.long),
                    ];

                    shipMarker = L.marker(shipCoordinates, {
                        icon: fisherMarkerIcon,
                    }).addTo(this.map);
                    shipMarker.bindPopup(
                        ship.name
                            ? "Device : " +
                                  ship.name +
                                  " (" +
                                  status.toUpperCase() +
                                  ")"
                            : "Device : " +
                                  ship.device_id +
                                  " (" +
                                  status.toUpperCase() +
                                  ")"
                    );
                }

                shipMarker._leaflet_id = "SHIP_" + ship.id;
                shipMarkers.push(shipMarker);
            });

            this.allIconMarkers = shipMarkers;
        },

        reFocusShipMarker(lat, long, zoom = 15) {
            this.map.setView([lat, long], zoom);
        },

        resetMapZoom() {
            this.map.setZoom(15);
        },

        showSidebarFindShip() {
            this.$refs.findShipSidebar.toggle();
        },

        ShowThisShip(ship) {
            console.log("searching ship with id....", ship.id);
            var target_ship = "SHIP_" + ship.id;

            // loop marker
            this.allIconMarkers.map((marker, index) => {
                if (marker._leaflet_id == target_ship) {
                    // dapat marker kapal dan zoom kesana
                    this.reFocusShipMarker(marker._latlng.lat, marker._latlng.lng, 30)
                    marker.openPopup()
                }
            });
        },
    },
};
</script>

<style>
html,
body {
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

.is-harbour {
    z-index: 9999999 !important;
}

.btn-reset {
    position: absolute;
    top: 11px;
    left: 50px;
    z-index: 999999 !important;
    background: tomato;
    color: #fff;
    padding: 5px 20px;
    border-radius: 10px;
}

.btn-find {
    position: absolute;
    top: 11px;
    right: 50px;
    z-index: 999999 !important;
    background: steelblue;
    color: #fff;
    padding: 5px 20px;
    border-radius: 10px;
}
</style>
