<template>
  <div class="mb-5">
    <input
      class="mr-5"
      :id="field.name"
      type="checkbox"
      :checked="field.value"
      @change="emitUpdate($event.target.checked)"
    />
    <label :for="field.name" class="text-gray-700 font-bold">
      {{ field.name }}
    </label>
  </div>
</template>

<script>
export default {
  props: ["field"],
  methods: {
    emitUpdate(checked) {
      this.$emit("update-value", { name: this.field.name, value: checked });
    },
  },
};
</script>
