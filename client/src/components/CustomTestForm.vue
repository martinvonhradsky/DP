<template>
  <div>
    <div v-for="(field, fieldName) in fields" :key="fieldName" class="mb-5">
      <label class="block text-gray-700 font-bold mb-2" :for="fieldName">
        {{ labels[fieldName] }} <!-- Use labels[fieldName] for label text -->
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
      <button
        class="btn btn-blue"
        type="submit"
        @click="submitCustomTest"
      >
        Add Custom Test
      </button>
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
