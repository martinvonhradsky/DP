<template>
  <div>
    <TextInputComponent
      v-for="(input, index) in textInputs"
      :key="index"
      :field="input"
      @update-value="handleUpdate"
    />
    <CheckboxInputComponent
      v-for="(input, index) in checkboxInputs"
      :key="index"
      :field="input"
      @update-value="handleUpdate"
    />
  </div>
</template>

<script>
import { computed } from "vue";
import { useTestStore } from "../../store/testStore.js";
import TextInputComponent from "../shared/inputs/TextInputComponent.vue";
import CheckboxInputComponent from "../shared/inputs/CheckboxInputComponent.vue";

export default {
  components: {
    TextInputComponent,
    CheckboxInputComponent,
  },
  setup() {
    const store = useTestStore();

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

    function handleUpdate({ name, value }) {
      store.handleFieldUpdate({ field: name, value });
    }

    store.fetchIDs();
    store.fetchTests();

    return {
      textInputs,
      checkboxInputs,
      handleUpdate,
    };
  },
};
</script>
