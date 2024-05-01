import { defineStore } from "pinia";
import axios from "axios";

export const useTestStore = defineStore("testStore", {
  state: () => ({
    fields: {
      url: {
        value: "",
        tooltip: "Enter the URL of the target",
        placeholder: "Enter URL",
      },
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
    async submitCustomTest() {
      if (!this.isFormValid) return;
      const requestData = {
        action: "test",
        ...Object.fromEntries(
          Object.entries(this.fields).map(([key, field]) => [key, field.value])
        ),
      };
      try {
        const response = await axios.post("/api.php", requestData);
        console.log(response.data);
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
    handleTestSelect(test) {
      this.selectedTest = this.selectedTest === test ? null : test;
    },
  },
});
