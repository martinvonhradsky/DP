import { defineStore } from "pinia";
import axios from "axios";

export const useAddCustomTestStore = defineStore("addCustomTestStore", {
  state: () => ({
    fields: {
      url: {
        value: "",
        tooltip: "Enter the URL of the target",
        placeholder: "Enter URL",
      },
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
    isSaving: false,
    // format:
    // {
    //   class: String,
    //   message: String,
    // },
    savingNotification: null,
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
      const requestData = {
        action: "test",
        ...Object.fromEntries(
          Object.entries(this.fields).map(([key, field]) => [key, field.value])
        ),
      };
      try {
        this.savingNotification = {
          class: 'bg-yellow-300',
          message: 'Saving...'
        }
        this.isSaving = true;
        await axios.post("/api.php", requestData);
        this.savingNotification = {
          class: 'bg-green-300',
          message: 'Ok.'
        }
      } catch (error) {
        this.savingNotification = {
          class: 'bg-red-300',
          message: String(error)
        }
      } finally {
        this.isSaving = false;
      }
    },
  },
});
