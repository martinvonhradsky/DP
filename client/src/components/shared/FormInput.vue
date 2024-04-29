<template>
  <div>
    <div v-for="(field, fieldName) in fields" :key="fieldName" class="mb-5">
      <label class="block text-gray-700 font-bold mb-2" :for="fieldName">
        {{ labels[fieldName] }}
      </label>
      <input
        v-if="fieldName !== 'local' && fieldName !== 'args'"
        class="w-full h-fit border border-gray-300 rounded-md py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        :id="fieldName"
        type="text"
        :placeholder="field.placeholder"
        :value="field.value"
        @input="$emit('updateField', { fieldName, value: $event.target.value })"
      />
      <input
        v-else
        class="mr-2 leading-tight"
        :id="fieldName"
        type="checkbox"
        v-model="field.value"
      />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    fields: Object,
    labels: Object,
  },
  emits: ["updateField"],
};
</script>
