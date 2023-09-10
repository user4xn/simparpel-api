<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import SettingMaster from "@/Components/Setting/SettingMaster.vue";
</script>

<template>
    <Head title="Setting" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Pengaturan Aplikasi
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <SettingMaster />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
