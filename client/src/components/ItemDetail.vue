<template>
  <div class="flex flex-col items-center">
   <navbar-vue class="flex w-full"/>
    <div class="w-full flex justify-center p-5"> 
    </div>
    <div class="container px-10 flex w-full mb-5">
      <div v-if="items" class="flex flex-col mr-5">
        <TechDetail
          v-for="item in items"
          :key="item.id"
          :item="item"
          :execute-output="item.executeOutput"
          @test-selected="handleTestSelect"
          @test-arguments="handleArguments"
        />
      </div>
      <!-- Left -->
      <div class="w-2/6 h-3/6 flex flex-col items-center justify-between">
        
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
            @click="fetchTargets"
            @change="handleTargetSelect(selectedTarget)"
          >
            <option value="" disabled selected>Select a target</option>
            <option
              v-for="target in targets"
              :value="target.alias"
              :key="target.id"
            >
              {{ target.alias }}
            </option>
          </select>
        </div>
        
        <TargetModal
          :show="showCustomTestModal"
          @toggleModal="toggleCustomTestModal"
        >
          
          <template #body>
            <CustomTestForm />
          </template>
        </TargetModal>
        <button
          class="w-48 h-12 border-solid border border-black px-4 py-2 rounded-md shadow-md focus:shadow-md mb-10"
          @click="executeTest(testId, selectedTarget, testArguments)"
          :title="
            !selectedTarget || !selectedTest
              ? 'Select a target and a test first.'
              : missingTooltip
          "
          :disabled="selectedTest ? (selectedTest.local_execution ? !selectedTarget : false) : true "
          
        >
          <span v-if="!isLoading">Execute Test</span>
          <div v-else>
            <div
              class="flex w-full justify-center items-center spinner inline-block"
            ></div>
          </div>
        </button>
        <button
          v-if="testExecuted"
          @click="saveTestResult"
          class="w-48 h-12 border-solid border border-black bg-gray-400 px-4 py-2 rounded-md shadow-md focus:shadow-md mb-10"
        >
          Save test result
        </button>
        <div v-if="testExecuted" class="flex items-center mb-10">
          <input
            type="checkbox"
            id="testDetected"
            v-model="testDetected"
            class="mr-5"
          />
          <label for="testDetected">Test Detected</label>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CustomTestForm from "./CustomTestForm.vue";
import TechDetail from "./TechDetail.vue";
import NavbarVue from './PageNavbar.vue';

import { RouterLink } from "vue-router";

export default {
  name: "ItemDetail",
  components: {
    CustomTestForm,
    RouterLink,
    TechDetail,
    NavbarVue
  },
  data() {
    return {
      items: [],
      isLoading: false,
      executeOutput: null,
      missingTooltip: "Default Tooltip",
      selectedTab: 0, // Default to the first tab
      selectedTarget: null,
      selectedTest: null,
      setupOutput: "",
      showCustomTestModal: false,
      tabs: ["Add", "Edit", "Delete"], // Add more tab titles as needed
      targets: [],
      testExecuted: false,
      testDetected: false,
      testId: null,
      testArguments: 'picavoda',
    };
  },
  computed: {
    filteredTabs() {
      if (this.selectedTarget) {
        return this.tabs;
      } else {
        return this.tabs.filter((tab) => tab !== "Edit" && tab !== "Delete");
      }
    },
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    createTestId(technique_id, test_number) {
      this.testId = `${technique_id}-${test_number}`;
    },
    executeTest(testId, alias, args) {
      console.log("ITEM DETAIL executeTest(testId): "+testId);      
      console.log("ITEM DETAIL executeTest(args): "+args);
      this.isLoading = true;
      this.createTestId(
        this.selectedTest.technique_id,
        this.selectedTest.test_number
      );
      this.$axios
        .get(
          `run-ansible.php?action=executeTest&id=${testId}&alias=${alias}&args=${args}`,
          true
        )
        .then((response) => {
          const selectedItem = this.items.find(
            (item) => item.id === this.selectedTest.technique_id
          );
          if (selectedItem) {
            selectedItem.executeOutput = response.data; // Update executeOutput for the selected item
            this.executeOutput = selectedItem.executeOutput;
            this.testExecuted = true;
            this.updateExecuteOutput();
          }
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.isLoading = false;
          this.selectedTarget = null;
        });
    },
    fetchData() {
      const id = this.$route.params.id;
      this.$axios
        .get(`api.php?action=specific&id=${id}`)
        .then((response) => {
          this.item = response.data[0];
          this.items = Array.from(
            new Set(response.data.map((item) => item.id))
          ).map((id) => {
            const item = response.data.find((item) => item.id === id);
            item.executeOutput = null; // Initialize executeOutput for each item
            return item;
          });
        })
        .catch((error) => {
          console.log(error);
        });
    },
    fetchTargetDetails(alias) {
      if (!this.selectedTarget) {
        return;
      }
      const apiUrl = `api.php?action=target_detail&alias=${alias}`;
      this.$axios
        .get(apiUrl)
        .then((response) => {
          this.targetDetails = response.data;
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
        })
        .catch((error) => {
          console.log(error);
        });
    },
    handleTargetSelect(selectedTarget) {
      // Update the selectedTarget data property with the selected value
      this.selectedTarget = selectedTarget;
      if (this.selectedTest) {
        this.testExecuted = false;
        this.createTestId(
          this.selectedTest.technique_id,
          this.selectedTest.test_number
        );
      }
    },
    handleTestSelect(test) {
      this.selectedTest = test;
      this.createTestId(
          this.selectedTest.technique_id,
          this.selectedTest.test_number
        );
      this.testExecuted = false;
    },
    handleArguments(args){
      this.testArguments = args;
    },
    saveTestResult() {
      const selectedItem = this.items.find(
                (item) => item.id === this.selectedTest.technique_id
              );
      const requestData = {
        action: "history",
        execution: selectedItem.executeOutput,
        detected: this.testDetected,
      };
      this.$axios
        .post("api.php", JSON.stringify(requestData), {
          headers: {
            "Content-Type": "application/json",
          },
        })
        .then(() => {
          this.testExecuted = false;
        })
        .catch((error) => {
          console.log(error);
        });
    },
    toggleCustomTestModal() {
      this.showCustomTestModal = !this.showCustomTestModal;
    },
    updateExecuteOutput() {
      if (this.testExecuted) {
        const intervalId = setInterval(() => {
          this.$axios
            .get("api.php?action=result")
            .then((response) => {
              const selectedItem = this.items.find(
                (item) => item.id === this.selectedTest.technique_id
              );
              if (selectedItem) {
                selectedItem.executeOutput = response.data.output;
              }
              if (response.data.end) {
                clearInterval(intervalId); // Stop the interval when response.data.end is true
              }
            })
            .catch((error) => {
              console.log(error);
            });
        }, 2000);
      }
    },
  },
  watch: {
    selectedTest(newSelectedTest) {
      if (newSelectedTest && this.selectedTarget) {
        this.createTestId(
          newSelectedTest.technique_id,
          newSelectedTest.test_number,
        );
      }
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
