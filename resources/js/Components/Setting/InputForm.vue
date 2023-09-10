<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
</script>

<template>
    <div>
        <InputLabel for="setting_name" :value="name" />

        <p class="text-xs text-gray-500 mt-1 mb-3">
            {{ description }}
        </p>

        <TextInput
            id="setting_name"
            ref="settingNameInput"
            :type="type"
            v-model="data_value"
            class="mt-1 block w-full"
            @keyup="onChanged"
            @change="onChanged"
        />
    </div>
</template>

<script>
export default {
    props: ["name", "description", "value", "type"],

    data() {
        return {
            data_value: "0",
        };
    },

    mounted() {
        console.log(
            "InputForm component ready",
            this.name,
            this.description,
            this.value
        );
        this.data_value = this.value;
    },

    methods: {
        onChanged(e) {
            // console.log("-- trigger value from ", this.name, this.data_value);
            this.$emit("get-data-event", this.name, this.data_value);
        },
    },
};
</script>
