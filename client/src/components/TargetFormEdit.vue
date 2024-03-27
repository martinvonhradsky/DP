<template>
  <form @submit.prevent="editTarget">
    <div class="mb-5">
      <label class="block text-gray-700 font-bold mb-2" for="target-ip">
        Target IP
      </label>
      <input
        class="w-full h-fit border border-gray-300 rounded-md py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        id="target-ip"
        type="text"
        v-model="formData.ip"
        required
      />
    </div>
    <div class="mb-5">
      <label class="block text-gray-700 font-bold mb-2" for="sudo-user">
        Sudo User
      </label>
      <input
        class="w-full h-fit border border-gray-300 rounded-md py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        id="sudo-user"
        type="text"
        v-model="formData.sudo_user"
        required
      />
    </div>
    <div class="mb-5">
      <label class="block text-gray-700 font-bold mb-2" for="password">
        Password
      </label>
      <input
        class="w-full h-fit border border-gray-300 rounded-md py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        id="password"
        type="password"
        v-model="password"
        required
      />
    </div>
    <div class="mb-5">
      <label class="block text-gray-700 font-bold mb-2" for="platform">
        Platform
      </label>
      <input
        class="w-full h-fit border border-gray-300 rounded-md py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        id="platform"
        type="text"
        v-model="formData.platform"
        required
      />
    </div>
    <!--
    <button
      class="w-48 h-12 border-solid border border-black bg-gray-400 px-4 py-2 rounded-md shadow-md focus:shadow-md mb-10"
      type="submit"
    >
      Edit Target
    </button>
    -->
  </form>
</template>

<script>
export default {
  name: "TargetFormEdit",
  props: {
    selectedTarget: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      formData: null,
      password: "",
    };
  },
  watch: {
    selectedTarget: {
      deep: true,
      immediate: true,
      handler(newValue) {
        this.formData = { ...newValue[0] };
      },
    },
  },
  methods: {
    editTarget() {
      const requestData = {
        action: "edit_target",
        ip: this.formData.ip,
        username: this.formData.sudo_user,
        password: this.password,
        alias: this.formData.alias,
        platform: this.formData.platform,
      };

      this.$axios
        .post("api.php", JSON.stringify(requestData), {
          headers: {
            "Content-Type": "application/json",
          },
        })
        .then(() => {
          this.resetFormData();
        })
        .catch((error) => {
          console.log(error);
          alert("Error: " + error);
        });

      //this.$emit("edit-target");
    },
    resetFormData() {
      this.formData = {
        ip: "",
        sudo_user: "",
        platform: "",
        alias: "",
      };
      this.password = "";
    },
  },
};
</script>

<style scoped></style>
