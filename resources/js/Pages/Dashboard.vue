<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import MapDashboard from '@/Components/MapDashboard.vue';
import { IconShip, IconShipOff, IconTower } from '@tabler/icons-vue';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <MapDashboard/>
                </div>
                <div class="grid md:grid-cols-3 sm:grid-cols-1 gap-3 mt-3">
                    <div class="bg-gray-800 p-4 rounded-md inline-flex items-center">
                        <div class="w-1/3 h-full flex items-center justify-center">
                            <IconTower :size="60" class="text-white" :stroke-width="1"/>
                        </div>
                        <div class="w-2/3 flex flex-col px-2 text-white text-end">
                            <div class="text-5xl font-black antialiased">2</div>
                            <div class="font-semibold uppercase">Jumlah Pelabuhan</div>
                        </div>
                    </div>
                    <div class="bg-indigo-500 p-4 rounded-md inline-flex items-center">
                        <div class="w-1/3 h-full flex items-center justify-center">
                            <IconShip :size="60" class="text-white" :stroke-width="1"/>
                        </div>
                        <div class="w-2/3 flex flex-col px-2 text-white text-end">
                            <div class="text-5xl font-black antialiased">5</div>
                            <div class="font-semibold uppercase">Jumlah Kapal</div>
                        </div>
                    </div>
                    <div class="bg-red-500 p-4 rounded-md inline-flex items-center">
                        <div class="w-1/3 h-full flex items-center justify-center">
                            <IconShipOff :size="60" class="text-white" :stroke-width="1"/>
                        </div>
                        <div class="w-2/3 flex flex-col px-2 text-white text-end">
                            <div class="text-5xl font-black antialiased">2</div>
                            <div class="font-semibold uppercase">Kapal Tidak Dikenal</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
