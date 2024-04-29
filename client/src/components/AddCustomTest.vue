<template>
  <div class="flex w-full justify-center h-100vw align-center">
    <div class="w-1/5">
      <FormCustomTest
        :fields="fields"
        :labels="labels"
        @updateField="handleFieldUpdate"
      />
      <div class="flex justify-between">
        <button
          class="btn btn-blue"
          type="submit"
          @click="testStore.submitCustomTest"
        >
          Add Custom Test
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import FormCustomTest from "./shared/FormCustomTest.vue";

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
  data() {
    return {
      labels: {
        id: "ID of Technic",
        name: "Test Name to be displayed in application",
        desc: "Description",
        filename: "Name of entrypoint - file to be executed",
        executable: "Executable Path",
        local: "Execute Test on Target Device",
        args: "Additional Arguments",
      },
      fields: {
        id: {
          value: "",
          tooltip: "Enter the ID of the target",
          placeholder: "Enter ID",
        },
        name: {
          value: "",
          tooltip: "Enter the name of the test",
          placeholder: "Enter test name",
        },
        desc: {
          value: "",
          tooltip: "Enter a description for the test",
          placeholder: "Enter description",
        },
        filename: {
          value: "",
          tooltip: "Enter the filename of the test",
          placeholder: "Enter filename",
        },
        executable: {
          value: "",
          tooltip: "Enter the path to the executable",
          placeholder: "Enter executable path",
        },
        local: {
          value: false,
          tooltip: "Check if the test is to be executed locally",
        },
        args: {
          value: "",
          tooltip: "Enter any additional arguments for the test",
          placeholder: "Enter additional arguments",
        },
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
    getFieldValue(fieldName) {
      return fieldName.value;
      //return this.selectedTest ? this.selectedTest[fieldName] : '';
    },
  },
  mounted() {
    this.fetchTests();
    this.fetchIDs();
  },
  methods: {
    handleFieldUpdate({ fieldName, value }) {
      this.fields[fieldName].value = value;
    },
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
    handleTechSelect(test) {
      if (this.selectedTech === test) {
        this.selectedTech = null;
      } else {
        this.selectedTech = test;
        this.selectedTest = null;
      }
    },
    handleTestSelect(test) {
      if (this.selectedTest === test) {
        this.selectedTest = null;
      } else {
        this.selectedTest = test;
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
