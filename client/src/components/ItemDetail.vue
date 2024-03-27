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
          @test-selected="handleTestSelect($event)"
        />
      </div>
      <!-- Left -->
      <div class="w-2/6 h-3/6 flex flex-col items-center justify-between">
        <RouterLink
          :to="{ name: 'Home' }"
          class="flex items-center justify-center w-48 h-12 border-solid border border-black bg-gray-400 px-4 py-2 rounded-md shadow-md focus:shadow-md mb-10"
        >
          HOME
        </RouterLink>
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
        <h2
          class="flex cursor-pointer items-center justify-center w-48 h-12 border-solid border border-black bg-gray-400 px-4 py-2 rounded-md shadow-md focus:shadow-md mb-10"
          @click="toggleTargetModal"
        >
          Manage Target
        </h2>
        <div>
          <TargetModal :show="showTargetModal" @toggleModal="toggleTargetModal">
            <template #header>
              <div>
                <div class="flex flex-col">
                  <h2 class="font-bold">Target Management</h2>
                  <h2>
                    {{
                      selectedTarget
                        ? `Target: ${selectedTarget}`
                        : "Target not selected"
                    }}
                  </h2>
                </div>
                <div class="flex justify-center">
                  <div class="w-full max-w-2xl">
                    <div class="tabs">
                      <button
                        v-for="(tab, index) in filteredTabs"
                        :key="index"
                        @click="
                          selectedTab = index;
                          fetchTargetDetails(this.selectedTarget);
                        "
                        :class="{
                          'bg-gray-800 text-white': selectedTab === index,
                          'bg-gray-200 text-gray-800': selectedTab !== index,
                        }"
                        class="flex-1 px-4 py-2 text-sm font-medium focus:outline-none"
                      >
                        {{ tab }}
                      </button>
                    </div>
                    <div
                      v-for="(tab, index) in tabs"
                      :key="index"
                      v-show="selectedTab === index"
                      class="pt-2"
                    >
                      <!-- Content for the tab -->
                    </div>
                  </div>
                </div>
              </div>
            </template>
            <template #body>
              <div v-show="selectedTab === 0" class="flex flex-col">
                <span v-if="setupOutput !== ''" class="font-bold">Output:</span>
                <p
                  v-if="setupOutput !== ''"
                  class="w-full h-fit border border-gray-300 rounded-md py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                >
                  {{ setupOutput }}
                </p>
                <TargetForm :selected-target="selectedTarget" />
              </div>
              <div v-if="selectedTarget" v-show="selectedTab === 1">
                <TargetFormEdit :selected-target="targetDetails" />
              </div>
              <div v-if="selectedTarget" v-show="selectedTab === 2">
                <TargetFormDelete :selected-target="selectedTarget" />
              </div>
            </template>
          </TargetModal>
        </div>
        <button
          class="w-48 h-12 border-solid border border-black px-4 py-2 rounded-md shadow-md focus:shadow-md mb-10"
          @click="toggleCustomTestModal"
        >
          <span>Add Custom Test</span>
        </button>
        <TargetModal
          :show="showCustomTestModal"
          @toggleModal="toggleCustomTestModal"
        >
          <template #header>
            <div>
              <div class="flex flex-col">
                <h2 class="font-bold">Add Custom Test</h2>
              </div>
            </div>
          </template>
          <template #body>
            <CustomTestForm />
          </template>
        </TargetModal>
        <button
          class="w-48 h-12 border-solid border border-black px-4 py-2 rounded-md shadow-md focus:shadow-md mb-10"
          @click="executeTest(testId, selectedTarget)"
          :title="
            !selectedTarget || !selectedTest
              ? 'Select a target and a test first.'
              : missingTooltip
          "
          :disabled="!selectedTarget || !selectedTest"
          :class="{ 'cursor-not-allowed': !selectedTarget || !selectedTest }"
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
import TargetModal from "./TargetModal.vue";
import TargetForm from "./TargetForm.vue";
import TargetFormEdit from "./TargetFormEdit.vue";
import TargetFormDelete from "./TargetFormDelete.vue";
import NavbarVue from './PageNavbar.vue';

import { RouterLink } from "vue-router";

export default {
  name: "ItemDetail",
  components: {
    CustomTestForm,
    RouterLink,
    TechDetail,
    TargetModal,
    TargetForm,
    TargetFormEdit,
    TargetFormDelete,
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
      showTargetModal: false,
      showCustomTestModal: false,
      tabs: ["Add", "Edit", "Delete"], // Add more tab titles as needed
      targetDetails: [],
      targets: [],
      testExecuted: false,
      testDetected: false,
      testId: null,
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
    executeTest(testId, alias) {
      this.isLoading = true;
      this.createTestId(
        this.selectedTest.technique_id,
        this.selectedTest.test_number
      );
      this.$axios
        .get(
          `run-ansible.php?action=executeTest&id=${testId}&alias=${alias}`,
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
    handleTestSelect(selectedTest) {
      this.selectedTest = selectedTest;
      this.testExecuted = false;
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
    toggleTargetModal() {
      this.showTargetModal = !this.showTargetModal;
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
              console.log(this.selectedTest);
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
          newSelectedTest.test_number
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
