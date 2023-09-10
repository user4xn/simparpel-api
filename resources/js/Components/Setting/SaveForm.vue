<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">Simpan Pengaturan</h2>

            <p class="mt-1 text-sm text-gray-600">
                Semua perubahan akan tersimpan saat Anda menekan tombol "Simpan
                Pengaturan" dibawah. Sementara tombol reset akan mengembalikan semua pengaturan menjadi seperti semula (default)
            </p>
        </header>

        <div class="flex gap-5">
            <PrimaryButton @click="$emit('submit-event')"
                >Simpan Pengaturan</PrimaryButton
            >

            <DangerButton  @click="$emit('reset-event')"
                >Reset Default</DangerButton
            >
        </div>

        <!-- <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Are you sure you want to delete your account?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Once your account is deleted, all of its resources and data
                    will be permanently deleted. Please enter your password to
                    confirm you would like to permanently delete your account.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        Cancel
                    </SecondaryButton>

                    <DangerButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Delete Account
                    </DangerButton>
                </div>
            </div>
        </Modal> -->
    </section>
</template>
