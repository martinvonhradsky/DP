<template>
  <div class="mb-5">
    <label :for="field.name" class="block text-gray-700 font-bold mb-2">
      {{ field.name }}
    </label>
    <input
      :id="field.name"
      type="text"
      :placeholder="field.placeholder"
      :value="field.value"
      @input="updateValue($event.target.value)"
      @mouseover="$emit('hoverField', field.name)"
    />
  </div>
</template>

<script>
import { useTestStore } from "../../../store/testStore.js";

export default {
  props: ["field"],
  setup(props) {
    const store = useTestStore();

    function updateValue(value) {
      console.log({ field: props.field.name, value });
      store.handleFieldUpdate({ field: props.field.name, value });
    }

    return { updateValue };
  },
};
</script>
