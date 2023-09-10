<script setup>
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import InputForm from "./InputForm.vue";
import SelectForm from "./SelectForm.vue";
import SaveForm from "./SaveForm.vue";
</script>

<template>
    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <section>
            <header>
                <h2 class="text-lg font-medium text-gray-900">
                    Device Setting
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Ubah pengaturan yang ada ada di device pengguna.
                </p>
            </header>

            <form class="mt-6 space-y-6">
                <!-- start looping component berdasarkan setting -->
                <div v-for="(item, index) in settings" :key="index">
                    <InputForm
                        v-if="item.type == 'text' || item.type == 'number'"
                        :name="item.name"
                        :description="item.description"
                        :value="item.value"
                        :type="item.type"
                        @get-data-event="getDataEventHandler"
                    />

                    <SelectForm
                        v-if="item.type == 'select'"
                        :name="item.name"
                        :description="item.description"
                        :value="item.value"
                        :type="item.type"
                        :options="item.additional_data"
                        @get-data-event="getDataEventHandler"
                    />
                </div>
                <!-- end looping -->

                <div
                    v-if="settings.length < 1"
                    class="bg-blue-500 text-white px-4 py-2 rounded border border-blue-700"
                >
                    Loading Setting . . . .
                </div>
            </form>
        </section>
    </div>

    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
        <SaveForm
            @submit-event="confirmSendForm"
            @reset-event="confirmResetDefault"
        />
    </div>
</template>

<!-------------------------->

<script>
export default {
    data() {
        return {
            settings: [],
            settings_form: {},
        };
    },

    mounted() {
        console.log("setting page is mounted");
        this.fetchSetting();
    },

    methods: {
        async fetchSetting() {
            try {
                const response = await axios.get(`/api/setting/fetch`);
                if (response.data.status === "success") {
                    this.settings = response.data.data;

                    var temp_data = {};
                    this.settings.forEach((item, index) => {
                        temp_data[item.name] = item.value;
                    });

                    this.settings_form = temp_data;
                    // console.log("-- form data", temp_data);
                }
            } catch (error) {
                console.error("Error fetching settings:", error);
            }
        },

        getDataEventHandler(input_name, input_value) {
            // console.log(
            //     "--- get data from child input ",
            //     input_name,
            //     input_value
            // );

            var temp_data = this.settings_form;
            temp_data[input_name] = input_value;
            this.settings_form = temp_data;

            // console.log("-- form data", this.settings_form);
        },

        /**** submit form ****/

        confirmSendForm() {
            Swal.fire({
                title: "Yakin menyimpan perubahan?",
                showCancelButton: true,
                confirmButtonText: "Simpan",
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submitSettingForm();
                }
            });
        },

        submitSettingForm() {
            // this.settings_form = {refresh_interval: '30', distance_offset: '20'}
            console.log("-- send form data", this.settings_form);

            axios
                .post("/api/setting/update", this.settings_form)
                .then((response) => {
                    if (response.status === 200) {
                        Swal.fire(
                            "Sukses",
                            "Pengaturan berhasil disimpan",
                            "success"
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error creating new area:", error);
                    Swal.fire(
                        "Error",
                        "Error saat mengirim data area",
                        "error"
                    );
                })
                .finally(() => {});
        },

        /**** reset default ****/

        confirmResetDefault() {
            Swal.fire({
                title: "Yakin menyimpan me-reset pengaturan menjadi seperti semula (DEFAULT)?",
                showCancelButton: true,
                confirmButtonText: "Simpan",
            }).then((result) => {
                if (result.isConfirmed) {
                    this.resetSettingForm();
                }
            });
        },

        resetSettingForm() {
            axios
                .delete("/api/setting/reset")
                .then((response) => {
                    if (response.status === 200) {
                        Swal.fire(
                            "Sukses",
                            "Pengaturan berhasil direset ke default",
                            "success"
                        );

                        // reload input form
                        this.settings = [];
                        this.fetchSetting();
                    }
                })
                .catch((error) => {
                    console.error("Error creating new area:", error);
                    Swal.fire(
                        "Error",
                        "Error saat mengirim data area",
                        "error"
                    );
                })
                .finally(() => {});
        },
    },
};
</script>
