<template>
  <div class="flex flex-col items-center">
   <navbar-vue class="flex w-full"/>
    <div class="w-full flex justify-center p-5"> 
    </div>
    <div class="container px-10 flex w-full mb-5">
      <div v-if="techniques" class="flex flex-col mr-5">
        <TechDetail
          v-for="tech in techniques"
          :key="tech.id"
          :item="tech"
          :selectedTest="selectedTest"
          :outputFileId="outputFileId(tech.id)"
          @test-selected="handleTestSelect"
        />
      </div>
      <!-- Left -->
      <div class="w-2/6 h-3/6 flex flex-col items-center justify-between" style="position: sticky; top: 15px;">
        
        <RouterLink
          :to="{ name: 'HistoryPage', params: { id: $route.params.id } }"
          class="flex items-center justify-center w-48 h-12 border-solid border border-black bg-gray-400 px-4 py-2 rounded-md shadow-md focus:shadow-md mb-10"
        >
          History
        </RouterLink>
        <div class="flex flex-col">
          <label for="aliasSelect">Select a target:</label>
          <select
            class="w-48 h-12 border-solid border border-black bg-gray-400 px-4 py-2 rounded-md shadow-md focus:shadow-md mb-10"
            v-model="selectedTarget"
            id="aliasSelect"
          >
            <option value="" disabled selected>Select a target</option>
            <option
              v-for="target in targets"
              :value="target"
              :key="target.alias"
            >
              {{ target.alias }}
            </option>
          </select>
        </div>

        <button
          class="w-48 h-12 border-solid border border-black px-4 py-2 rounded-md shadow-md focus:shadow-md mb-10 disabled:opacity-50 disabled:bg-gray-400"
          @click="executeTest()"
          :title="
            !selectedTarget || !selectedTest
              ? 'Select a target and a test first.'
              : ''
          "
          :disabled="isExecutionStarting || didTestTargetPairExecute || (selectedTest ? (selectedTest.local_execution ? !selectedTarget : false) : true) "
        >
          Execute Test
        </button>
        <div v-if="didTestTargetPairExecute" class="flex items-center mb-10">
          <input
            type="checkbox"
            id="testDetected"
            v-model="selectedTest.executions[selectedTarget.alias].detected"
            class="mr-5"
          />
          <label for="testDetected">Test Detected</label>
        </div>
        <button
          v-if="didTestTargetPairExecute"
          :disabled="selectedTest.executions[selectedTarget.alias].isResultBeingSaved"
          @click="saveTestResult"
          class="w-48 h-12 border-solid border border-black bg-gray-400 px-4 py-2 rounded-md shadow-md focus:shadow-md mb-10 disabled:opacity-50 disabled:bg-gray-400"
        >
          Save test result
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import TechDetail from "./TechDetail.vue";
import NavbarVue from './PageNavbar.vue';

import { RouterLink } from "vue-router";

export default {
  name: "ItemDetail",
  components: {
    RouterLink,
    TechDetail,
    NavbarVue
  },
  data() {
    return {
      // Fetched data
      techniques: [],
      targets: [],

      // Selections
      selectedTest: null,
      selectedTarget: null,

      isExecutionStarting: false,
    };
  },
  computed: {
    outputFileId() {
      return (techId) => {
        if (!this.selectedTest || !this.selectedTarget || this.selectedTest.technique_id !== techId) {
          return null;
        }
        return this.selectedTest.executions[this.selectedTarget.alias]?.outputFileId ?? null;
      }
    },
    didTestTargetPairExecute() {
      if (!this.selectedTest || !this.selectedTarget) {
        return false;
      } else {
        return (this.selectedTest.executions[this.selectedTarget.alias] ? true : false);
      }
    }
  },
  mounted() {
    this.fetchTechniquesAndTests();
    this.fetchTargets();
  },
  methods: {
    makeTestId(techId, testNumber) {
      return`${techId}-${testNumber}`;
    },
    executeTest() {
      this.isExecutionStarting = true;
      const testId = this.makeTestId(this.selectedTest.technique_id, this.selectedTest.test_number);
      const args = this.selectedTest.argumentsValue;
      const targetAlias = this.selectedTarget.alias;

      console.log(`Execute test: ${targetAlias} ${testId} ${args}`);      
      this.$axios
        .get(
          `run-ansible.php?action=executeTest&id=${testId}&alias=${targetAlias}&args=${args}`,
          true
        )
        .then((response) => {
          this.selectedTest.executions[targetAlias] = {
            outputFileId: response.data.output_file_id,
            detected: false,
            isResultBeingSaved: false,
          }
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.isExecutionStarting = false;
        });
    },
    fetchTests(tech) {
      this.$axios
        .get(`api.php?action=test_by_id&id=${tech.id}`)
        .then((response) => {
          if (!response || !response.data || !(response.data instanceof Array) || response.data.length == 0 || response.data[0].Error) {
            tech.tests = [];
          } else {
            tech.tests = response.data.map((t) => {
              t.argumentsValue = "";
              // Format:
              // {
              //   targetAlias: {
              //     outputFileId: string,
              //     detected: boolean,
              //     isResultBeingSaved: boolean,
              //   }
              // }
              // Note: Only one execution per target is stored.
              t.executions = {};
              return t;
            })
          }
        })
        .catch((error) => {
          tech.tests = [];
          console.log(error);
        });
    },
    fetchTechniquesAndTests() {
      const id = this.$route.params.id;
      this.$axios
        .get(`api.php?action=specific&id=${id}`)
        .then((response) => {
          console.log(response.data);
          this.techniques = Array.from(
            new Set(response.data.map((item) => item.id))
          ).map((id) => {
            const item = response.data.find((item) => item.id === id);
            return item;
          });
          for (let tech of this.techniques) {
            this.fetchTests(tech);
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    fetchTargets() {
      this.$axios
        .get("api.php?action=targets")
        .then((response) => {
          this.targets = response.data;
          // Make the first target the selected one.
          this.selectedTarget = (this.targets.length > 0 ? this.targets[0] : null);
        })
        .catch((error) => {
          console.log(error);
        });
    },
    handleTargetSelect(selectedTarget) {
      // Update the selectedTarget data property with the selected value
      this.selectedTarget = selectedTarget;
    },
    handleTestSelect(test) {
      this.selectedTest = test;
    },
    saveTestResult() {
      const execution = this.selectedTest.executions[this.selectedTarget.alias];
      execution.isResultBeingSaved = true;

      const requestData = {
        action: "history",
        testId:  this.makeTestId(this.selectedTest.technique_id, this.selectedTest.test_number),
        target: this.selectedTarget.alias,
        outputFileId: execution.outputFileId,
        detected: execution.detected,
      };
      this.$axios
        .post("api.php", JSON.stringify(requestData), {
          headers: {
            "Content-Type": "application/json",
          },
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          execution.isResultBeingSaved = false;
        })
    },
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
