<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted,} from 'vue';
import { usePage } from '@inertiajs/vue3';
import MapShipDetail from '@/Components/MapShipDetail.vue';
import { IconChevronLeft, IconAlertTriangle} from '@tabler/icons-vue';
import Swal from 'sweetalert2';

const { id } = usePage().props;
const ship = ref({});
const isLoaded = ref(false);

onMounted(async () => {
  fetchShipDetails(id);
  isLoaded.value = true;
});

const fetchShipDetails = async (shipId) => {
    try {
        const response = await axios.get(`/api/ship/${shipId}/status`);
        if (response.data.status === 'success') {
            ship.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching ship details:', error);
    }
};

const editName = async (shipId) => {
  try {
    const { value: newName } = await Swal.fire({
      title: 'Edit Nama Kapal',
      input: 'text',
      inputValue: '',
      inputPlaceholder: 'Masuakan Nama',
      showCancelButton: true,
      confirmButtonText: 'Simpan',
    });

    if (newName) {
      const response = await axios.put(`/api/ship/${shipId}/name`, {
        ship_name: newName,
      });
      if (response.data.status === 'success') {
        Swal.fire('Sukses', 'Berhasil edit nama kapal', 'success');
        fetchShipDetails(id);
      }
    }
  } catch (error) {
    console.error('Error updating ship name:', error);
  }
};
</script>

<template>
    <Head title="Detail Kapal" />

    <AuthenticatedLayout>
      <template #header>
        <div class="md:inline-flex items-center w-full justify-between">
            <div class="inline-flex items-center">
              <a :href="route('kapal')" class="me-3 inline-flex items-center px-3 py-1 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                  <IconChevronLeft lass="mr-1 w-4 h-4 "/>
              </a>
              <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Kapal <span v-if="isLoaded && ship.ship_detail"> ({{ ship.ship_detail.name ? ship.ship_detail.name : '?' }}) </span></h2>
            </div>
        </div>
      </template>
      <div class="pt-12" v-if="isLoaded && ship.ship_detail && ship.location_log[0].is_mocked === 1 ">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-red-500 text-white font-semibold uppercase overflow-hidden shadow-sm sm:rounded-lg p-4 lg:inline-flex items-center w-full justify-between">
            <IconAlertTriangle/> Peringatan!! - kapal terdeteksi menggunakan fake gps - {{ ship.location_log[0].created_at }}
          </div>
        </div>
      </div>
      <div class="py-12">
          <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <MapShipDetail v-if="isLoaded && ship.ship_detail" :shipDetail="ship.ship_detail" :logParking="ship.parking_log[0]" :locationLog="ship.location_log[1]"/>
            <div v-if="isLoaded && ship.ship_detail" class="grid md:grid-cols-3 gap-2 sm:gird-cols-1">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-xl font-semibold mb-4">Detail Kapal</h2>
                    <div class="text-lg font-medium">{{ ship.ship_detail.name === '' || ship.ship_detail.name === null ? '(Unnamed Ship)' : ship.ship_detail.name }}</div>
                    <p class="text-gray-500 mt-2">Device ID: {{ ship.ship_detail.device_id }}</p>
                    <p class="text-gray-500 mt-2" v-if="ship.ship_detail.on_ground === 1">Status: <span class="font-bold uppercase">offline</span></p>
                    <p class="text-gray-500 mt-2" v-else>Status: <span class="font-bold uppercase" :class="{'text-green-400': ship.ship_detail.status === 'checkin', 'text-red-400': ship.ship_detail.status === 'checkout', 'text-yellow-500': ship.ship_detail.status === 'out of scope',  'text-blue-500': ship.ship_detail.status === null }">{{ ship.ship_detail.status ? ship.ship_detail.status : 'BARU' }}</span></p>
                    <p class="text-gray-500 mt-2">Pelabuhan Terkini: {{ ship.ship_detail.harbour_detail ? ship.ship_detail.harbour_detail.name : '-' }}</p>
                    <p class="text-gray-500 mt-2">Update Lokasi Terakhir: {{ ship.location_log[0].created_at }}</p>
                    <button @click="editName(ship.ship_detail.id, 'New Name')" class="mt-6 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ ship.ship_detail.name === '' || ship.ship_detail.name === null ? 'Beri Nama' : 'Ubah Nama' }}
                    </button>
                </div>
                <div class="p-6 bg-white border-b border-gray-200 col-span-2">
                    <div class="bg-gray-800 rounded-md p-3 text-gray-300 max-h-full">
                        <h2 class="text-xl font-semibold mb-4">Log Labuh Kapal</h2>
                        <ul class="max-h-48 overflow-y-scroll">
                            <span v-if="ship.parking_log.length === 0"> - </span>
                            <li v-for="log in ship.parking_log" :key="log.id" class="flex items-center justify-between">
                                <div>
                                    [{{ log.created_at }}] 
                                    <span class="font-bold uppercase" :class="{'text-green-400': log.status === 'checkin', 'text-red-400': log.status === 'checkout'}">{{ log.status }}</span>
                                </div>
                                <div>
                                    {{ log.harbour_name ? log.harbour_name : '(Undefined Harbour '+log.harbour_id+')' }}
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
  