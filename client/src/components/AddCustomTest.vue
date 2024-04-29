<template>
  <div>
    <URLInputComponent v-model="url" />
    <button @click="submitUrl">Submit URL</button>
  </div>
</template>

<script>
import { useTestStore } from "../store/testStore.js";
import URLInputComponent from "./shared/URLInputComponent.vue";

export default {
  components: {
    URLInputComponent,
  },
  setup() {
    const store = useTestStore();
    return {
      url: store.url,
      submitUrl() {
        console.log("URL Submitted: ", store.url);
        // further actions
      },
    };
  },
};
</script>
