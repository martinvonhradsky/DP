<template>
  <div class="mb-5 flex flex-col">
    <label
      :for="field.name"
      class="block text-gray-700 font-bold mb-2 justify-self-start max-w-max"
    >
      {{ field.name }}
    </label>
    <input
      :id="field.name"
      type="text"
      :placeholder="field.placeholder"
      :value="field.value"
      @input="emitUpdate($event.target.value)"
      @mouseover="emitHover()"
      class="w-full"
    />
  </div>
</template>

<script>
export default {
  props: ["field"],
  methods: {
    emitUpdate(value) {
      this.$emit("update-value", { name: this.field.name, value: value });
    },
    emitHover() {
      this.$emit("hover-field", this.field.name);
    },
  },
};
</script>
