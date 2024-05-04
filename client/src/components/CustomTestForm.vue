<template>
  <div class="flex justify-around">
    <div class="w-1/5 min-w-1/5">
      <FormCustomTest :fields="store.fields" @updateField="handleFieldUpdate" />
      <div>
        <button
          v-if="store.selectedTest !== null"
          class="btn btn-blue bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none disabled:bg-gray-400 disabled:text-gray-700 disabled:cursor-not-allowed disabled:border-gray-700"
          type="submit"
          @click="submitCustomTest(test)"
          :disabled="!store.isFormValid"
        >
          Edit Test
        </button>
      </div>
    </div>

    <div class="pt-32 ms-2 w-3/5 min-w-3/5">
      <div class="flex justify-start">
        <div v-if="store.leftColumn">
          <div class="table-container">
            <table class="border-collapse border border-gray-400 h-max-content">
              <div>
                <h4
                  class="border border-gray-400 px-4 py-2"
                  v-if="leftColumn !== null"
                >
                  Technic ID
                </h4>
              </div>
              <tbody>
                <tr
                  v-for="tech in store.leftColumn"
                  :key="tech"
                  @click="handleTechSelect(tech)"
                  :class="{ 'bg-blue-200': store.selectedTech === tech }"
                >
                  <td
                    class="border border-gray-400 px-4 py-2"
                    style="width: 200px"
                  >
                    {{ tech.technique_id }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-if="store.selectedTech">
          <div class="ps-2">
            <table
              class="table-container2 border-collapse border border-gray-400 min-w-80 max-w-80"
            >
              <thead>
                <tr>
                  <th class="border border-gray-400 px-4 py-2 w-48">Test</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="test in store.customTests"
                  :key="test"
                  :class="{ 'bg-blue-200': store.selectedTest === test }"
                >
                  <td
                    v-if="
                      store.selectedTech &&
                      store.selectedTech.technique_id === test.technique_id
                    "
                    class="border border-gray-400 px-4 py-2"
                  >
                    <div class="flex">
                      {{ test.name }}

                      <div class="flex">
                        <button
                          @click="setSelectedTest(test)"
                          class="edit-button p-2"
                        >
                          Edit
                        </button>
                        <button
                          @click.stop="deleteCustomTest(test)"
                          class="delete-button p-2"
                        >
                          Delete
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
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
  data() {
    return {
      store: useTestStore(),
    };
  },
  setup() {
    const store = useTestStore();

    return {
      fields: store.fields,
      labels: store.labels,
      handleFieldUpdate: store.handleFieldUpdate,
      submitCustomTest: store.submitCustomTest,
    };
  },
  methods: {
    updateValue(fieldName, value) {
      this.store.handleFieldUpdate(fieldName, value);
    },
    handleTechSelect(tech) {
      console.log("store", this.store.fields);
      this.store.handleTechSelect(tech);
    },
    handleTestSelect(test) {
      this.store.handleTestSelect(test);
    },
    setSelectedTest(test) {
      this.store.setSelectedTest(test);
    },
    deleteCustomTest(test) {
      this.store.deleteCustomTest(test);
    },
  },
  created() {
    this.store = useTestStore();
  },
};
</script>

<style scoped>
.table-container {
  overflow-y: auto; /* Enable vertical scrolling */
  max-height: 48vh; /* Set maximum height */
}

.table-container2 {
  overflow-y: auto; /* Enable vertical scrolling */
  max-height: 80vh; /* Set maximum height */
}

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
