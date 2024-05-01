<template>
  <div class="flex">
    <div class="flex-1">
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
    <div style="display: flex; flex-direction: row">
      <div style="flex: 1; overflow: auto">
        <table
          class="border-collapse border border-gray-400 w-full"
          style="
            table-layout: fixed;
            width: 200-px;
            height: 100px;
            overflow-y: scroll;
          "
        >
          <thead>
            <tr>
              <th class="border border-gray-400 px-4 py-2" style="width: 200px">
                Technic ID
              </th>
            </tr>
          </thead>
          <div style="height: 80vh; width: inherit; overflow-y: scroll">
            <tbody>
              <tr
                v-for="tech in store.leftColumn"
                :key="tech"
                @click="handleTechSelect(tech)"
                :class="{ 'bg-blue-200': selectedTech === tech }"
              >
                <td
                  class="w-fuborder border-gray-400 px-4 py-2"
                  style="width: 200px"
                >
                  {{ tech.technique_id }}
                </td>
              </tr>
            </tbody>
          </div>
        </table>
      </div>

      <div style="flex: 1; overflow: auto">
        <table
          class="border-collapse border border-gray-400 w-full"
          style="table-layout: fixed"
        >
          <thead>
            <tr>
              <th class="border border-gray-400 px-4 py-2">Test</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="test in customTests"
              :key="test"
              @click="handleTestSelect(test)"
              :class="{ 'bg-blue-200': selectedTest === test }"
            >
              <td
                v-if="
                  selectedTech &&
                  selectedTech.technique_id === test.technique_id
                "
                class="border border-gray-400 px-4 py-2"
              >
                {{ test.name }}
              </td>
            </tr>
          </tbody>
        </table>
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
  mounted() {
    this.store.fetchTests();
    this.store.fetchIDs();
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
