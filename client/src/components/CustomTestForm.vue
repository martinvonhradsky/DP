<template>
  <div>
    <div v-for="(field, fieldName) in fields" :key="fieldName" class="mb-5">
      <label class="block text-gray-700 font-bold mb-2" :for="fieldName">
        {{ fieldName.charAt(0).toUpperCase() + fieldName.slice(1) }}
      </label>
      <input
        v-if="fieldName !== 'git' && fieldName !== 'local'"
        class="w-full h-fit border border-gray-300 rounded-md py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        :id="fieldName"
        type="text"
        v-model="field.value"
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
      fields: {
        url: { value: "" },
        id: { value: "" },
        executable: { value: "" },
        desc: { value: "" },
        local: { value: false },
        name: { value: "" },
        filename: { value: "" },
        extension: { value: "" },
        git: { value: false },
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
