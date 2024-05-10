<template>
  <div>
    <NavbarVue />
    <div class="flex w-full justify-center h-100vw align-center mt-20">
      <div class="w-1/5">
        <AddCustomTestForm
          :fields="fields"
          :labels="labels"
          @updateField="handleFieldUpdate"
        />
        <div class="flex justify-between">
          <button
            class="btn btn-blue bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none disabled:bg-gray-400 disabled:text-gray-700 disabled:cursor-not-allowed disabled:border-gray-700"
            type="submit"
            @click="submitCustomTest()"
            :disabled="!store.isFormValid"
          >
            Add Custom Test
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import AddCustomTestForm from "./shared/AddCustomTestForm.vue";
import NavbarVue from "./PageNavbar.vue";
import { useAddCustomTestStore } from "../store/addCustomTestStore";

export default {
  name: "AddCustomTest",
  components: {
    AddCustomTestForm,
    NavbarVue,
  },
  data() {
    return {
      store: useAddCustomTestStore(),
    };
  },
  setup() {
    const addCustomTestStore = useAddCustomTestStore();

    return {
      fields: addCustomTestStore.fields,
      labels: addCustomTestStore.labels,
      handleFieldUpdate: addCustomTestStore.handleFieldUpdate,
      submitCustomTest: addCustomTestStore.submitCustomTest,
    };
  },
  methods: {
    updateValue(fieldName, value) {
      this.addCustomTestStore.handleFieldUpdate(fieldName, value);
    },
  },
  created() {
    this.addCustomTestStore = useAddCustomTestStore();
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
../store/addCustomTestStore
