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

        <!-- <TextInput
            id="setting_name"
            ref="settingNameInput"
            :type="type"
            v-model="data_value"
            class="mt-1 block w-full"
            @keyup="onChanged"
            @change="onChanged"
        /> -->

        <select
            v-model="data_value"
            @change="onChanged"
            class="block w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500 transition-colors"
        >
            <option
                v-for="option in parsed_options"
                :key="option.value"
                :value="option.value"
            >
                {{ option.text }}
            </option>
        </select>
    </div>
</template>

<script>
export default {
    props: ["name", "description", "value", "type", "options"],

    data() {
        return {
            data_value: "",
            parsed_options: [],
        };
    },

    // watch: {
    //     value(newValue) {
    //         this.selected = newValue;
    //     },
    // },

    mounted() {
        console.log(
            "InputForm component ready",
            this.name,
            this.description,
            this.value,
            this.options
        );

        this.data_value = this.value;

        const correctedJsonString = this.options.replace(/'/g, '"');
        this.parsed_options = JSON.parse(correctedJsonString);
    },

    methods: {
        onChanged(e) {
            // console.log("-- trigger value from ", this.name, this.data_value);
            this.$emit("get-data-event", this.name, this.data_value);
        },
    },
};
</script>
