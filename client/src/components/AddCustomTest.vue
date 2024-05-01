<template>
  <div class="flex w-full justify-center h-100vw align-center">
    <div class="w-1/5">
      <FormCustomTest
        :fields="fields"
        :labels="labels"
        @updateField="handleFieldUpdate"
      />
      <div class="flex justify-between">
        <button class="btn btn-blue" type="submit" @click="submitCustomTest">
          Add Custom Test
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import FormCustomTest from "./shared/FormCustomTest.vue";
import { useTestStore } from "../store/testStore";

export default {
  name: "CustomTestForm",
  components: {
    FormCustomTest,
  },
  props: {
    selectedTarget: {
      type: String,
      required: true,
    },
  },
  setup() {
    const testStore = useTestStore();

    return {
      fields: testStore.fields,
      labels: testStore.labels,
      handleFieldUpdate: testStore.handleFieldUpdate,
      submitCustomTest: testStore.submitCustomTest,
    };
  },

  methods: {
    updateValue(fieldName, value) {
      this.testStore.handleFieldUpdate(fieldName, value);
    },
  },
  created() {
    this.testStore = useTestStore();
  },
};
</script>

<style scoped>
.spinner {
  border-top: 2px solid #666;
  border-right: 2px solid #666;
  border-bottom: 2px solid #666;
  border-left: 2px solid transparent;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
