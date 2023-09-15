<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import MapShipDetail from "@/Components/MapShipDetail.vue";
import MapShipDetailHistory from "@/Components/MapShipDetailHistory.vue";
import { IconChevronLeft, IconAlertTriangle } from "@tabler/icons-vue";
import Datepicker from "@vuepic/vue-datepicker";
import {DateTime} from "luxon"
import "@vuepic/vue-datepicker/dist/main.css";
import Swal from "sweetalert2";

const { id } = usePage().props;
const ship = ref({});
const isLoaded = ref(false);

const shipHistory = ref({});
const showHistory = ref(false);
const showHistoryError = ref(false);
const availableDate = ref([]);
const date = ref(new Date());
const componentKey = ref(0);

onMounted(async () => {
    fetchShipDetails(id);
    isLoaded.value = true;
    showHistory.value = false;

    getAvailableHistory(id);
});

const fetchShipDetails = async (shipId) => {
    try {
        const response = await axios.get(`/api/ship/${shipId}/status`);
        if (response.data.status === "success") {
            ship.value = response.data.data;
        }
    } catch (error) {
        console.error("Error fetching ship details:", error);
    }
};

const editName = async (shipId) => {
    try {
        const { value: newName } = await Swal.fire({
            title: "Edit Nama Kapal",
            input: "text",
            inputValue: "",
            inputPlaceholder: "Masuakan Nama",
            showCancelButton: true,
            confirmButtonText: "Simpan",
        });

        if (newName) {
            const response = await axios.put(`/api/ship/${shipId}/name`, {
                ship_name: newName,
            });
            if (response.data.status === "success") {
                Swal.fire("Sukses", "Berhasil edit nama kapal", "success");
                fetchShipDetails(id);
            }
        }
    } catch (error) {
        console.error("Error updating ship name:", error);
    }
};

const viewHistory = async (shipId, selectedDate) => {
    try {
        selectedDate = formatDate(selectedDate);
        console.log(selectedDate);
        const response = await axios.get(
            `/api/ship/${shipId}/history/${selectedDate}`
        );
        if (response.data.status === "success") {
            shipHistory.value = response.data.data;
            showHistory.value = true;

            if (response.data.data.history.length) {
                showHistoryError.value = false;
            } else {
                showHistoryError.value = true;
            }
        }

        componentKey.value += 1;
    } catch (error) {
        console.error("Error fetching ship details:", error);
    }
};

const getAvailableHistory = async (shipId) => {
    try {
        const response = await axios.get(
            `/api/ship/${shipId}/history/available`
        );
        if (response.data.status === "success") {
            availableDate.value = response.data.data.dates;

            // console.log(response.data.data.dates)
        }
    } catch (error) {
        console.error("Error fetching ship details:", error);
    }
};

const formatDate = (input) => {
    // Membuat objek Date dari string input
    const date = new Date(input);

    // Mengambil tahun, bulan, dan tanggal
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, "0"); // Bulan dimulai dari 0
    const dd = String(date.getDate()).padStart(2, "0");

    return `${yyyy}-${mm}-${dd}`;
};

const handleDate = (modelData) => {
    date.value = modelData;
    viewHistory(id, date.value);
};

</script>

<template>
    <Head title="Detail Kapal" />

    <AuthenticatedLayout>
        <template #header>
            <div class="md:inline-flex items-center w-full justify-between">
                <div class="inline-flex items-center">
                    <a
                        :href="route('kapal')"
                        class="me-3 inline-flex items-center px-3 py-1 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <IconChevronLeft lass="mr-1 w-4 h-4 " />
                    </a>
                    <h2
                        class="font-semibold text-xl text-gray-800 leading-tight"
                    >
                        Detail Kapal
                        <span v-if="isLoaded && ship.ship_detail">
                            ({{
                                ship.ship_detail.name
                                    ? ship.ship_detail.name
                                    : "?"
                            }})
                        </span>
                    </h2>
                </div>
            </div>
        </template>
        <div
            class="pt-12"
            v-if="
                isLoaded &&
                ship.ship_detail &&
                ship.location_log?.[0]?.is_mocked === 1
            "
        >
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-red-500 text-white font-semibold uppercase overflow-hidden shadow-sm sm:rounded-lg p-4 lg:inline-flex items-center w-full justify-between"
                >
                    <IconAlertTriangle /> Peringatan!! - kapal terdeteksi
                    menggunakan fake gps - {{ ship.location_log[0].created_at }}
                </div>
            </div>
        </div>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- start history -->
                    <div class="flex mt-4 mb-4 justify-end gap-4 mr-4">
                        <div class="flex justify-between items-center">
                            <label
                                for="date"
                                class="block text-md font-medium text-gray-700"
                                >Pilih Tanggal Riwayat</label
                            >
                        </div>
                        <div class="w-64">
                            <datepicker
                                v-model="date"
                                format="yyyy-MM-dd"
                                auto-apply
                                :clearable="false"
                                :highlight="availableDate"
                                @update:model-value="handleDate"
                                :max-date="new Date()"
                                :min-date="DateTime.now().minus({month: 1})"
                            ></datepicker>
                        </div>
                        <div class="flex justify-between items-center">
                            <button
                                @click="viewHistory(ship.ship_detail.id, date)"
                                class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Tampilkan History
                            </button>
                        </div>
                    </div>
                    <!-- end history -->

                    <!-- start alert history -->
                    <div class="mb-2 mt-2 ml-2 mr-2">
                        <div
                            class="bg-red-500 text-white px-4 py-3 rounded relative"
                            role="alert"
                            v-if="showHistoryError"
                        >
                            <span class="block sm:inline"
                                >Tidak ada history pelayaran di tanggal
                                {{ formatDate(date) }}</span
                            >
                        </div>

                        <!--  -->

                        <div
                            class="bg-green-600 text-white px-4 py-3 rounded relative"
                            role="alert"
                            v-if="
                                showHistory &&
                                shipHistory.history.length > 0 &&
                                !showHistoryError
                            "
                        >
                            <span class="block sm:inline"
                                >Terdapat
                                {{ shipHistory.history.length }} history
                                pelayaran di tanggal
                                {{ formatDate(date) }}.</span
                            >
                        </div>
                    </div>
                    <!-- start alert history -->

                    <!-- start peta -->

                    <MapShipDetail
                        v-if="
                            isLoaded &&
                            ship.ship_detail &&
                            (!showHistory || showHistoryError)
                        "
                        :shipDetail="ship.ship_detail"
                        :logParking="ship.parking_log[0]"
                        :locationLog="ship.location_log[1]"
                    />

                    <MapShipDetailHistory
                        v-if="
                            isLoaded &&
                            ship.ship_detail &&
                            showHistory &&
                            !showHistoryError
                        "
                        :shipDetail="ship.ship_detail"
                        :logParking="ship.parking_log[0]"
                        :locationLog="ship.location_log[1]"
                        :historyLog="shipHistory"
                        :key="componentKey"
                    />

                    <!-- end peta -->

                    <div
                        v-if="isLoaded && ship.ship_detail"
                        class="grid md:grid-cols-3 gap-2 sm:gird-cols-1"
                    >
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h2 class="text-xl font-semibold mb-4">
                                Detail Kapal
                            </h2>
                            <div class="text-lg font-medium">
                                {{
                                    ship.ship_detail.name === "" ||
                                    ship.ship_detail.name === null
                                        ? "(Unnamed Ship)"
                                        : ship.ship_detail.name
                                }}
                            </div>
                            <p class="text-gray-500 mt-2">
                                Device ID: {{ ship.ship_detail.device_id }}
                            </p>
                            <p
                                class="text-gray-500 mt-2"
                                v-if="ship.ship_detail.on_ground === 1"
                            >
                                Status:
                                <span class="font-bold uppercase">offline</span>
                            </p>
                            <p class="text-gray-500 mt-2" v-else>
                                Status:
                                <span
                                    class="font-bold uppercase"
                                    :class="{
                                        'text-green-400':
                                            ship.ship_detail.status ===
                                            'checkin',
                                        'text-red-400':
                                            ship.ship_detail.status ===
                                            'checkout',
                                        'text-yellow-500':
                                            ship.ship_detail.status ===
                                            'out of scope',
                                        'text-blue-500':
                                            ship.ship_detail.status === null,
                                    }"
                                    >{{
                                        ship.ship_detail.status
                                            ? ship.ship_detail.status
                                            : "BARU"
                                    }}</span
                                >
                            </p>
                            <p class="text-gray-500 mt-2">
                                Pelabuhan Terkini:
                                {{
                                    ship.ship_detail.harbour_detail
                                        ? ship.ship_detail.harbour_detail.name
                                        : "-"
                                }}
                            </p>
                            <p class="text-gray-500 mt-2">
                                Update Lokasi Terakhir:
                                {{ ship.location_log?.[0]?.created_at }}
                            </p>
                            <button
                                @click="
                                    editName(ship.ship_detail.id, 'New Name')
                                "
                                class="mt-6 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                {{
                                    ship.ship_detail.name === "" ||
                                    ship.ship_detail.name === null
                                        ? "Beri Nama"
                                        : "Ubah Nama"
                                }}
                            </button>
                        </div>
                        <div
                            class="p-6 bg-white border-b border-gray-200 col-span-2"
                        >
                            <div
                                class="bg-gray-800 rounded-md p-3 text-gray-300 max-h-full"
                            >
                                <h2 class="text-xl font-semibold mb-4">
                                    Log Labuh Kapal
                                </h2>
                                <ul class="max-h-48 overflow-y-scroll">
                                    <span v-if="ship.parking_log.length === 0">
                                        -
                                    </span>
                                    <li
                                        v-for="log in ship.parking_log"
                                        :key="log.id"
                                        class="flex items-center justify-between"
                                    >
                                        <div>
                                            [{{ log.created_at }}]
                                            <span
                                                class="font-bold uppercase"
                                                :class="{
                                                    'text-green-400':
                                                        log.status ===
                                                        'checkin',
                                                    'text-red-400':
                                                        log.status ===
                                                        'checkout',
                                                }"
                                                >{{ log.status }}</span
                                            >
                                        </div>
                                        <div>
                                            {{
                                                log.harbour_name
                                                    ? log.harbour_name
                                                    : "(Undefined Harbour " +
                                                      log.harbour_id +
                                                      ")"
                                            }}
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 bg-white border-b border-gray-200" v-else>
                        Loading ship details...
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.overflow-y-scroll::-webkit-scrollbar {
    width: 0.5em;
}

.overflow-y-scroll::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
}
</style>
