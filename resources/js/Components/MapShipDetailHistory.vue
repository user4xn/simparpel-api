<template>
    <div class="relative">
        <button
            class="bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 px-3 py-2 absolute z-[1000] right-2 top-2 rounded-md font-semibold text-white"
            @click="reFocusShipMarker"
        >
            Re-Center
        </button>
        <div id="map" :style="{ width: '100%', height: '400px' }"></div>
    </div>
</template>

<script>
import L from "leaflet";
import "leaflet-polylinedecorator";
import "leaflet-rotatedmarker";
import "leaflet/dist/leaflet.css";
// import GeometryUtil from "leaflet-geometryutil";
import axios from "axios";
import shipMarkerIcon from "../../../public/images/ship-marker.png";
import fishermanMarkerIcon from "../../../public/images/fisherman-marker.png";
import * as turf from '@turf/turf'

export default {
    props: {
        shipDetail: Object, // Define the prop to accept ship details
        locationLog: Object, // Define the prop to accept ship details
        logParking: Object, // Define the prop to accept ship details
        historyLog: Object, // Define the prop to accept ship details
    },

    data() {
        return {
            latest_place: {},
        };
    },

    mounted() {
        // console.log(this.shipDetail)
        // console.log(this.locationLog)
        // console.log(this.logParking)
        // console.log("HISTORY", this.historyLog.history);

        var last_place = this.getLastObjectItem(this.historyLog.history);
        this.last_place = last_place;

        this.initializeMap();
        this.fetchAreas();
        this.initializeShipMarker();
    },
    methods: {
        getLastObjectItem(inputObj) {
            const keys = Object.keys(inputObj);
            const lastKey = keys[keys.length - 1];
            return inputObj[lastKey];
        },

        initializeMap() {
            this.map = L.map("map").setView(
                [this.last_place.lat, this.last_place.long],
                15
            );

            L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
                maxZoom: 19,
                attribution:
                    '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            }).addTo(this.map);
        },
        fetchAreas() {
            axios
                .get(`/api/harbour`)
                .then((response) => {
                    const data = response.data;
                    if (data.status === "success") {
                        this.areas = data.data;

                        this.areas.forEach((area) => {
                            const coordinates = area.coordinates.map(
                                (coord) => [
                                    parseFloat(coord.lat),
                                    parseFloat(coord.long),
                                ]
                            );
                            const polygon = L.polygon(coordinates).addTo(
                                this.map
                            );
                        });
                    }
                })
                .catch((error) => {
                    console.error("Error fetching areas:", error);
                });
        },
        initializeShipMarker() {
            const latlngs = [];

            const markerIcon = L.icon({
                iconUrl: shipMarkerIcon,
                iconSize: [20, 40],
            });

            this.historyLog.history.forEach((val, index) => {
                latlngs.push([val.lat, val.long]);
            });

            console.log("ARRAY LATLONG : ", latlngs);
            // const polyline = L.polyline(latlngs, { color: "blue" }).addTo(
            //     this.map
            // );

            const polyline = L.polyline(latlngs, { color: "green" });
            var decorator = L.polylineDecorator(polyline, {
                patterns: [
                    {
                        offset: 0,
                        repeat: 15,
                        symbol: L.Symbol.arrowHead({
                            pixelSize: 10,
                            polygon: false,
                            pathOptions: { stroke: true, color: "green" },
                        }),
                    },
                ],
            }).addTo(this.map);

            /** ambil rotasi **/
            var rotasi = this.calculateBearing();
            // rotasi += 20
            console.log("test rotasi : ", rotasi);

            const shipMarker = L.marker(
                [this.last_place.lat, this.last_place.long],
                {
                    icon: markerIcon,
                    rotationAngle: rotasi,
                    rotationOrigin: "center center",
                }
            ).addTo(this.map);
            shipMarker.bindPopup(
                this.shipDetail.name ? this.shipDetail.name : "Unnamed Ship"
            );

            setTimeout(() => {
                this.reFocusShipMarker();
            }, 700);
        },
        reFocusShipMarker() {
            this.map.setView([this.last_place.lat, this.last_place.long], 13);
        },
        calculateBearing() {
            // cek apa ada lebih dari 1 koordinat
            if (this.historyLog.history.length <= 1) {
                // jika cuma 1, set ke 0
                return 0;
            } else {
                // jika lebih dari 1, ambil 2 koordinat terakhir
                var array_coordinate = this.historyLog.history;
                // array_coordinate.reverse()
                const slicedArray = array_coordinate.slice(-2);

                // console.log("koordinate", slicedArray)
                console.log(
                    "test koordinat 0",
                    slicedArray[0].lat,
                    slicedArray[0].long
                );
                console.log(
                    "test koordinat 1",
                    slicedArray[1].lat,
                    slicedArray[1].long
                );
                // return GeometryUtil.bearing(new L.LatLng(slicedArray[0].lat, slicedArray[0].long), new L.LatLng(slicedArray[1].lat, slicedArray[1].long))

                const point1 = turf.point([slicedArray[0].lat, slicedArray[0].long]);
                const point2 = turf.point([slicedArray[1].lat, slicedArray[1].long]);
                const bear = turf.bearing(point1, point2);

                return bear-45
            }
        },
    },
    data() {
        return {
            map: null,
            areas: [],
            polygonLayer: null,
            areaName: "",
        };
    },
};
</script>

<style scooped>
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
</style>
