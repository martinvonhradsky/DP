import { defineStore } from "pinia";

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
    handleFieldUpdate(fieldName, value) {
      this.fields[fieldName].value = value;
    },
    async submitCustomTest() {
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
    async fetchTests() {
      this.$axios
        .get("api.php?action=get_custom_tests")
        .then((response) => {
          this.customTests = response.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },
    async fetchIDs() {
      this.$axios
        .get("api.php?action=get_custom_ids")
        .then((response) => {
          this.leftColumn = response.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },
  },
});
