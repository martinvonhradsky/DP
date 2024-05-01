<template>
  <div class="mb-5">
    <input
      :id="field.name"
      type="checkbox"
      :checked="field.value"
      @change="updateValue($event.target.checked)"
    />
    <label :for="field.name" class="text-gray-700 font-bold">
      {{ field.name }}
    </label>
  </div>
</template>

<script>
import { useTestStore } from "../../../store/testStore.js";

export default {
  props: ["field"],
  setup(props) {
    const store = useTestStore();

    function updateValue(value) {
      store.handleFieldUpdate({ field: props.field.name, value });
    }

    return { updateValue };
  },
};
</script>
