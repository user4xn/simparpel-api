<template>
    <div>
        <div
            class="sidebar bg-white p-5 rounded-lg shadow-md flex flex-col"
            ref="mySidebar"
        >
            <form @submit.prevent="findThoseShip" method="POST">
                <div>
                    <label
                        for="namaKapal"
                        class="block text-sm font-medium text-gray-700"
                        >Cari Kapal</label
                    >
                    <input
                        type="text"
                        id="namaKapal"
                        name="namaKapal"
                        class="mt-1 p-2 w-full border rounded-md"
                        placeholder="Masukkan Nama Kapal"
                        minlength="3"
                        v-model="keyword"
                        required
                    />
                </div>
                <div class="mt-4">
                    <button
                        type="submit"
                        class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                    >
                        Cari Kapal
                    </button>
                </div>
            </form>

            <hr />

            <div class="flex-grow overflow-y-auto custom-scrollbar">
                <div
                    class="bg-white p-6 rounded-lg shadow-md w-96 mt-5"
                    v-if="is_loading"
                >
                    <!-- loading -->
                    <div class="flex space-x-4 items-center p-6">
                        <!-- Placeholder untuk teks -->
                        <div class="flex-1 space-y-4 py-1">
                            <div
                                class="h-4 bg-gray-300 animate-pulse rounded w-3/4"
                            ></div>
                            <div class="space-y-2">
                                <div
                                    class="h-4 bg-gray-300 animate-pulse rounded"
                                ></div>
                                <div
                                    class="h-4 bg-gray-300 animate-pulse rounded w-5/6"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-lg shadow-md mt-5"
                    v-if="!is_loading && ships_data.length > 0"
                >
                    <!-- result -->
                    <h2 class="text-xl font-semibold p-3">
                        Hasil Pencarian Kapal {{ keyword }}
                    </h2>

                    <!-- item kapal -->
                    <div
                        class="border-b border-gray-200 py-2 p-3 cursor-pointer hover:bg-gray-100"
                        v-for="(item, i) in ships_data"
                        :key="i"
                        @click="focusOnShip(item)"
                    >
                        <h3 class="text-lg font-medium">{{ item.name }}</h3>
                        <p class="text-sm text-gray-600">
                            Device ID: {{ item.device_id }}
                        </p>
                        <p class="text-sm text-gray-600">
                            Status: {{ item.status }}
                        </p>
                        <p class="text-sm text-gray-500">
                            Terakhir diupdate: {{ item.updated_at }}
                        </p>
                    </div>

                    <!-- Tambahkan lebih banyak item kapal sesuai kebutuhan -->
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            keyword: "",
            is_loading: false,
            show_error: false,
            ships_data: [],
        };
    },

    mounted() {
        console.log("setting page is mounted");
    },

    methods: {
        // submit pencarian kapal
        async findThoseShip() {
            try {
                this.is_loading = true;
                this.show_error = false;
                const response = await axios.get(
                    "/api/ship/find/" + this.keyword
                );
                if (response.data.status === "success") {
                    this.ships_data = response.data.data;
                }
            } catch (error) {
                console.error("Error fetching settings:", error);
                this.show_error = true;
            } finally {
                this.is_loading = false;
            }
        },

        // saat hasil pencarian kapal diklik
        focusOnShip(ship) {
            // lempar ke parent : MapDashboard
            this.$emit("show-ship-event", ship);
        },

        // show hide sidebar form
        toggle() {
            const element = this.$refs.mySidebar;
            element.classList.toggle("show");
        },
    },
};
</script>

<style>
.sidebar {
    /* background: rgba(0, 0, 0, 0.8); */
    position: absolute;
    top: 55px;
    right: -500px;
    width: 460px;
    height: 70vh;
    border-radius: 20px 0 0 20px;
    z-index: 99999999999999 !important;
    transition: all 1s ease;
}

.sidebar.show {
    right: 0px;
}

/* Menggaya scrollbar utama */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px; /* Lebar scrollbar */
    height: 4px; /* Tinggi scrollbar, berguna jika Anda juga menginginkan scrollbar horizontal */
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: darkgrey; /* Warna bagian scrollbar yang bisa digeser */
    border-radius: 2px; /* Melengkungkan sudut */
}

.custom-scrollbar::-webkit-scrollbar-track {
    background-color: lightgrey; /* Warna latar belakang scrollbar */
    border-radius: 2px; /* Melengkungkan sudut */
}

@media only screen and (max-width: 600px) {
  .sidebar {
    width: 96vw;
    top: 10vh;
  }
}
</style>
