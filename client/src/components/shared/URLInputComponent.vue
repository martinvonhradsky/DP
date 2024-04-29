<template>
  <div>
    <label for="url">URL of Git Repository:</label>
    <input
      type="text"
      id="url"
      v-model="url"
      placeholder="Enter URL"
      @input="updateUrl"
    />
  </div>
</template>

<script>
export default {
  name: "URLInputComponent",
  data() {
    return {
      url: "",
    };
  },
  methods: {
    updateUrl() {
      this.$emit("input", this.url);
    },
  },
};
</script>
