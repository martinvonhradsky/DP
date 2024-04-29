import { defineStore } from "pinia";

export const useTestStore = defineStore("testStore", {
  state: () => ({
    url: "",
    id: "",
    name: "",
    description: "",
    filename: "",
    executable: "",
    local: false,
    args: "",
  }),
  actions: {
    updateField(payload) {
      this[payload.field] = payload.value;
      console.log(payload.field);
    },
  },
});
