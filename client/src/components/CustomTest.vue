<template>
  <div>
    <div class="p-5">
      <div class="flex justify-start w-full">
        <div v-if="store.leftColumn" class="w-2/5">
          <EasyDataTable
            v-if="!isShow && !isConfirmVisible"
            class="cursor-pointer"
            :headers="techHeaders"
            :items="store.leftColumn"
            @click-row="handleTechSelect"
          />
        </div>

        <div v-if="store.selectedTech && !isConfirmVisible">
          <div v-if="!isShow" class="ps-2">
            <table class="border-collapse border min-h-max">
              <thead>
                <tr>
                  <th class="border px-4 py-2">
                    Tests - {{ store.selectedTech.technique_id }}
                  </th>
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
                    class="border px-4 py-2"
                  >
                    <div class="flex justify-between items-center">
                      {{ test.name }}

                      <div class="flex">
                        <button
                          @click="setSelectedTest(test), showModal()"
                          class="edit-button p-1 m-1 bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded focus:outline-none text-xs"
                        >
                          Edit
                        </button>
                        <button
                          @click.stop="deleteCustomTest(test)"
                          class="delete-button p-1 m-1 bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded focus:outline-none text-xs"
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
    <Modal v-model="isShow" :close="closeModal">
      <div class="modal flex flex-col z-20">
        <div class="justify-self-center">
          <h2>Edit Custom Test</h2>
          <h3>Form</h3>
        </div>
        <br />
        <div>
          <FormCustomTest
            :fields="store.fields"
            @updateField="handleFieldUpdate"
          />
          <button
            class="btn btn-blue bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none disabled:bg-gray-400 disabled:text-gray-700 disabled:cursor-not-allowed disabled:border-gray-700"
            type="submit"
            @click="
              submitCustomTest();
              closeModal();
            "
            :disabled="!store.isFormValid"
          >
            Edit Test
          </button>
        </div>
      </div>
    </Modal>

    <!-- Confirmation modal -->
    <div v-if="isConfirmVisible" class="confirm-modal">
      <div class="confirm-content">
        <p>Are you sure to delete test: {{ testToDelete.name }}?</p>
        <div class="confirm-buttons">
          <button @click="confirmDelete" class="edit-button p-1 m-1 bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded focus:outline-none text-xs">Yes</button>
          <button @click="cancelDelete" class="edit-button p-1 m-1 bg-red-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded focus:outline-none text-xs">No</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import FormCustomTest from "./shared/FormCustomTest.vue";
import { useTestStore } from "../store/testStore";
import EasyDataTable from "vue3-easy-data-table";

const store = useTestStore();
const isShow = ref(false);
const isConfirmVisible = ref(false);
let testToDelete = null;

const techHeaders = [
  { text: "TECH ID", value: "technique_id" },
  { text: "TECH NAME", value: "name" },
];


onMounted(() => {
  store.fetchIDs();
  store.fetchTests();
});

function showModal() {
  isShow.value = true;
}

function closeModal() {
  isShow.value = false;
}

function handleTechSelect(tech) {
  store.handleTechSelect(tech);
}

function setSelectedTest(test) {
  store.setSelectedTest(test);
}

function deleteCustomTest(test) {
  testToDelete = test;
  isConfirmVisible.value = true;
}

function confirmDelete() {
  store.deleteCustomTest(testToDelete);
  testToDelete = null;
  isConfirmVisible.value = false;
}

function cancelDelete() {
  testToDelete = null;
  isConfirmVisible.value = false;
}

function submitCustomTest() {
  store.submitCustomTest();
  closeModal();
}

onMounted(() => {
  store.fetchIDs();
  store.fetchTests();
});
</script>

<style scoped>
.confirm-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
}

.confirm-content {
  background-color: white;
  padding: 20px;
  border-radius: 5px;
}

.confirm-buttons {
  display: flex;
  justify-content: space-between;
  margin-top: 10px;
}

.confirm-buttons button {
  padding: 5px 10px;
  border-radius: 3px;
  cursor: pointer;
}

.confirm-buttons button:hover {
  background-color: #f0f0f0;
}


.modal {
  width: 500px;
  padding: 30px;
  box-sizing: border-box;
  background-color: #fff;
  font-size: 20px;
  text-align: center;
  z-index: 1000;
}
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
