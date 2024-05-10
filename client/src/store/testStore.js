import { defineStore } from "pinia";
import axios from "axios";

export const useTestStore = defineStore("testStore", {
  state: () => ({
    fields: {
      technique_id: {
        value: "",
        tooltip: "Enter the ID of the target",
        placeholder: "Enter ID",
      },
      name: {
        value: "",
        tooltip: "Enter the name of the test",
        placeholder: "Enter test name",
      },
      description: {
        value: "", 
        tooltip: "Enter a description for the test",
        placeholder: "Enter description",
      },
      file_name: {
        value: "",
        tooltip: "Enter the filename of the test",
        placeholder: "Enter filename",
      },
      executable: {
        value: "",
        tooltip: "Enter the path to the executable",
        placeholder: "Enter executable path",
      },
      local_execution: {
        value: false,
        tooltip: "Check if the test is to be executed locally",
      },
      args: {
        value: false,
        tooltip: "Enter any additional arguments for the test",
        placeholder: "Enter additional arguments",
      },
    },
    customTests: null,
    selectedTest: null,
    selectedTech: null,
    leftColumn: null,
    isLoading: false,
  }),
  getters: {
    isFormValid(state) {
      const nonCheckboxFields = Object.values(state.fields).filter(
        (field) => typeof field.value !== "boolean"
      );
      return nonCheckboxFields.every((field) => field.value);
    },
  },
  actions: {
    handleFieldUpdate({ field, value }) {
      if (this.fields[field]) {
        this.fields[field].value = value;
      }
    },
    resetFields() {
      this.fields.technique_id.value = "";
      this.fields.name.value = "";
      this.fields.description.value = "";
      this.fields.file_name.value = "";
      this.fields.executable.value = "";
      this.fields.local_execution.value = false;
      this.fields.args.value = false;
    },
    async submitCustomTest() {
      const requestData = {
        action: "edit_test",
        test_number: this.selectedTest.test_number,
        ...Object.fromEntries(
          Object.entries(this.fields).map(([key, field]) => [key, field.value])
        ),
      };
      try {
        const response = await axios.post("/api.php", requestData);
        console.log(response.data);
        this.resetFields();
        this.fetchTests();
      } catch (error) {
        console.error("Failed to submit custom test:", error);
        alert("Error submitting test: " + error.message);
      }
    },
    async fetchTests() {
      try {
        const response = await axios.get("/api.php?action=get_custom_tests");
        this.customTests = response.data;
      } catch (error) {
        console.error("Failed to fetch tests:", error);
      }
    },
    async fetchIDs() {
      try {
        const response = await axios.get("/api.php?action=get_custom_ids");
        this.leftColumn = response.data;
      } catch (error) {
        console.error("Failed to fetch IDs:", error);
      }
    },
    handleTechSelect(test) {
      if (this.selectedTech === test) {
        this.selectedTech = null;
        this.selectedTest = null;
      } else {
        this.selectedTech = test;
        this.selectedTest = null;
      }
    },
    async deleteCustomTest(test) {
      const requestData = {
        action: "test",
        test_number: test.test_number,
        technique_id: test.technique_id,
      };

      try {
        const response = await axios({
          method: "delete",
          url: "http://localhost/app/api.php",
          data: requestData,
        });
        console.log("Delete response:", response.data);
        this.fetchTests();
      } catch (error) {
        console.error("Failed to delete custom test:", error);
        alert("Error deleting test: " + error.message);
      }
    },
    setSelectedTest(test) {
      this.selectedTest = this.selectedTest === test ? null : test;
      this.fields.technique_id.value = test.technique_id || "";
      this.fields.name.value = test.name || "";
      this.fields.description.value = test.description || "";
      this.fields.file_name.value = test.file_name || "";
      this.fields.executable.value = test.executable || "";
      this.fields.local_execution.value = test.local_execution || false;
      this.fields.args.value = test.arguments || false;
    },
  },
});
