<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';

const ships = ref([]);

onMounted(async () => {
  loadData();
});

const loadData = async () => {
  try {
    const response = await axios.get('/api/ship');
    if (response.data.status === 'success') {
      ships.value = response.data.data;
    }
  } catch (error) {
    console.error('Error fetching ship data:', error);
  }
}

const editName = async (shipId, currentName) => {
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
        loadData();
      }
    }
  } catch (error) {
    console.error('Error updating ship name:', error);
  }
};

</script>

<template>
    <Head title="Kapal" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Kapal</h2>
        </template>
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-800">
                        <thead class="text-xs text-white uppercase bg-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    Device ID
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Nama Kapal
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Status Kapal
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Pelabuhan Terkini
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ship in ships" :key="ship.id" class="bg-white border-b">
                                <th scope="row" class="px-6 py-4 font-medium whitespace-nowrap">
                                {{ ship.device_id }}
                                </th>
                                <td class="px-6 py-4">
                                {{ ship.name ? ship.name : '-'}}
                                </td>
                                <td class="px-6 py-4 uppercase">
                                {{ ship.on_ground === 1 ? 'OFFLINE' : (ship.status ? ship.status : 'BARU')}}
                                </td>
                                <td class="px-6 py-4">
                                {{ ship.harbour ? ship.harbour : '-' }}
                                </td>
                                <td class="px-6 py-4 text-end">
                                  <button v-if="ship.name === '' || ship.name === null" @click="editName(ship.id, 'New Name')" class="me-2 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Beri Nama
                                  </button>
                                  <a :href="route('kapal.detail', { id: ship.id })" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    Detail
                                  </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
