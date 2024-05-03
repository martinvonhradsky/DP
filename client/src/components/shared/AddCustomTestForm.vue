<template>
  <div>
    <TextInputComponent
      v-for="(input, index) in textInputs"
      :key="index"
      :field="input"
      @updateField="handleUpdateField"
    />
    <CheckboxInputComponent
      v-for="(input, index) in checkboxInputs"
      :key="index"
      :field="input"
      @updateField="handleUpdateField"
    />
  </div>
</template>

<script>
import { computed } from "vue";
import { useAddCustomTestStore } from "../../store/addCustomTestStore.js";
import TextInputComponent from "../shared/inputs/TextInputComponent.vue";
import CheckboxInputComponent from "../shared/inputs/CheckboxInputComponent.vue";

export default {
  components: {
    TextInputComponent,
    CheckboxInputComponent,
  },
  setup() {
    const store = useAddCustomTestStore();

    const textInputs = computed(() => {
      return Object.keys(store.fields)
        .filter((key) => key !== "local_execution" && key !== "args")
        .map((key) => ({
          name: key,
          value: store.fields[key].value,
          type: "text",
          placeholder: `Enter ${key}`,
        }));
    });

    const checkboxInputs = computed(() => {
      return ["local_execution", "args"].map((key) => ({
        name: key,
        value: store.fields[key].value,
        type: "checkbox",
      }));
    });

    const handleUpdateField = (fieldName, value) => {
      store.handleFieldUpdate(fieldName, value);
    };

    return { textInputs, checkboxInputs, handleUpdateField };
  },
};
</script>
