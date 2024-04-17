<template>
  <div class="flex">
    <!-- Left side - Input fields for selected test -->
    <div class="flex-1">
      <div v-for="(field, fieldName) in fields" :key="fieldName" class="mb-5">
        <label class="block text-gray-700 font-bold mb-2" :for="fieldName">
          {{ labels[fieldName] }}
        </label>
        <input
          v-if="fieldName !== 'local' && fieldName !== 'args'"
          class="w-full h-fit border border-gray-300 rounded-md py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          :id="fieldName"
          type="text"
          v-model="field.value"
          :title="field.tooltip"
          :placeholder="field.placeholder"
        />
        <input
          v-else
          class="mr-2 leading-tight"
          :id="fieldName"
          type="checkbox"
          v-model="field.value"
        />
      </div>
      <div class="flex justify-between">
        <button class="btn btn-red" @click="deleteCustomTest">Delete</button>
        <button class="btn btn-green" @click="editCustomTest">Edit</button>
      </div>
    </div>
    <!-- Right side - Custom tests table -->
    <div style="display: flex; flex-direction: row;">
  <!-- First table for the left column -->
  <div style="flex: 1; overflow: auto;">
    <table class="border-collapse border border-gray-400 w-full" style="table-layout: fixed;">
      <thead>
        <tr>
          <th class="border border-gray-400 px-4 py-2">Technic ID</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(tech) in leftColumn" :key="tech" @click="handleTechSelect(tech)" :class="{ 'bg-blue-200': selectedTech === tech }">
          <td class="border border-gray-400 px-4 py-2">{{ tech.technique_id }}</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Second table for the right column -->
  <div style="flex: 1; overflow: auto;">
    <table class="border-collapse border border-gray-400 w-full" style="table-layout: fixed;">
      <thead>
        <tr>
          <th class="border border-gray-400 px-4 py-2">Test</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(test) in customTests" :key="test" @click="handleTestSelect(test)" :class="{'bg-blue-200': selectedTest === test}">
          <td v-if="selectedTech && selectedTech.technique_id === test.technique_id"   class="border border-gray-400 px-4 py-2">
      
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
export default {
  name: "TargetForm",
  props: {
    selectedTarget: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      labels: {
        url: "URL of Git repository", 
        id: "ID of Technic",
        name: "Test Name to be displayed in application",
        desc: "Description",
        filename: "Name of entrypoint - file to be executed",
        executable: "Executable Path",
        local: "Execute Test locally on Remote Target Device",
        args: "Additional Arguments"
      },
      fields: {
        url: { value: "", tooltip: "Enter the URL of the target", placeholder: "Enter URL" },
        id: { value: "", tooltip: "Enter the ID of the target", placeholder: "Enter ID" },
        name: { value: "", tooltip: "Enter the name of the test", placeholder: "Enter test name" },
        desc: { value: "", tooltip: "Enter a description for the test", placeholder: "Enter description" },
        filename: { value: "", tooltip: "Enter the filename of the test", placeholder: "Enter filename" },
        executable: { value: "", tooltip: "Enter the path to the executable", placeholder: "Enter executable path" },
        local: { value: false, tooltip: "Check if the test is to be executed locally" },
        args: { value: "", tooltip: "Enter any additional arguments for the test", placeholder: "Enter additional arguments" }
      },
      customTests: null,
      selectedTest: null,
      selectedTech: null,
      leftColumn: null,
      isLoading: false,
    };
  },
  computed: {
    isFormValid() {
      console.log("isFormValid");
      const nonCheckboxFields = Object.values(this.fields).filter(
        (field) => typeof field.value !== "boolean"
      );
      return nonCheckboxFields.every((field) => field.value);
    },
  },
  mounted() {
    this.fetchTests();
    this.fetchIDs();
  },
  methods: {
    async submitCustomTest() {
      console.log("clicked");
      if (!this.isFormValid) return;

      const requestData = JSON.stringify({
        action: "test",
        ...Object.fromEntries(
          Object.entries(this.fields).map(([key, field]) => [key, field.value])
        ),
      });

      const axiosConfig = {
        headers: {
          "Content-Type": "application/json",
        },
      };

      try {
        const response = await this.$axios.post(
          "api.php",
          requestData,
          axiosConfig
        );
        console.log(response.data);
      } catch (error) {
        console.error(error);
        alert("Error: " + error);
      }
    },
    fetchTests() {
      this.$axios
        .get("api.php?action=get_custom_tests")
        .then((response) => {
          this.customTests = response.data;         
        })
        .catch((error) => {
          console.log(error);
        });
    },
    fetchIDs() {
      this.$axios
        .get("api.php?action=get_custom_ids")
        .then((response) => {
          this.leftColumn = response.data;         
        })
        .catch((error) => {
          console.log(error);
        });
    },
    handleTechSelect(test){
      if(this.selectedTech === test){
        this.selectedTech = null;
      }else{
        this.selectedTech = test;
      }
    },
    handleTestSelect(test){
      if(this.selectedTest === test){
        this.selectedTest = null;
      }else{
        this.selectedTest = test;
      }
    }
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
