<template>
  <div @submit.prevent>
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
    <div class="mb-5">
      <label class="block text-gray-700 font-bold mb-2" for="alias">
        Alias
      </label>
      <input
        class="w-full h-fit border border-gray-300 rounded-md py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        :class="{'bg-gray-200': !isNew}"
        id="alias"
        type="text"
        :disabled="!isNew"
        v-model="formData.alias"
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
      <label class="block text-gray-700 font-bold mb-2">Authentication Method</label>
      <div>
        <input type="radio" id="ssh-key" value="ssh-key" v-model="authMethod">
        <label for="ssh-key">SSH Key</label>
        <br />
        <input type="radio" id="pwd-auth" value="pwd-auth" v-model="authMethod">
        <label for="pwd-auth">Password</label>
      </div>
    </div>
    <div v-if="authMethod == 'ssh-key'" class="mb-5">
      <label class="block text-gray-700 font-bold mb-2">Public SSH Key</label>

      <p style="word-break: break-all;">{{ sshKey }}</p>
    </div>
    <div v-if="authMethod == 'pwd-auth'" class="mb-5">
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
    <!--

    <div class="flex justify-between">
      <button
        class="w-48 h-12 border-solid border border-black bg-gray-400 px-4 py-2 rounded-md shadow-md focus:shadow-md mb-10"
        @click="addTarget"
      >
        Add Target
      </button>
      <button
        class="w-48 h-12 border-solid border border-black px-4 py-2 rounded-md shadow-md focus:shadow-md mb-10"
        :class="{
          'bg-gray-400': isFormValid,
          'bg-gray-500': !isFormValid,
          'cursor-not-allowed': !isFormValid,
        }"
        @click="runSetup(selectedTarget)"
        :disabled="!isFormValid"
      >
        <span v-if="!isLoading">RUN SETUP</span>
        <div v-else>
          <div
            class="flex w-full justify-center items-center spinner inline-block"
          ></div>
        </div>
      </button>
    </div>
    -->
    <div
      v-if="notificationMessage"
      :class="notificationClass"
      class="rounded-md p-4 mb-4 mt-4"
    >
      {{ notificationMessage }}
    </div>
  </div>
</template>

<script>
export default {
  name: "TargetFormEdit",
  props: {
    selectedTarget: {
      type: Object,
      default: () => ({}),
    },
    isNew: {
      type: Boolean,
      default: () => (true),
    },
  },
  data() {
    return {
      formData: null,
      password: "",
      notificationMessage: null,
      authMethod: "ssh-key",
      sshKey: null,

      // Constants
      notificationClassGood: "bg-green-500 text-white",
      notificationClassBad: "bg-red-500 text-white",
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
  mounted() {
    this.fetchSshKey();
  },
  computed: {
    isFormValid() {
      return (
        this.formData.ip &&
        this.formData.sudo_user &&
        (this.authMethod == 'ssh-key' || this.password) &&
        this.formData.platform &&
        this.formData.alias
      );
    },
  },
  methods: {
    showInvalidFormWarning() {
      this.notificationMessage = "All fields have to be filled.";
      this.notificationClass = this.notificationClassBad;
    },
    editTarget() {
      if (!this.isFormValid) {
        this.showInvalidFormWarning();
        return;
      }
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
          this.notificationMessage = "Target updated successfully.";
          this.notificationClass = this.notificationClassGood;
        })
        .catch((error) => {
          console.log(error);
          this.notificationMessage = "Target update failed.";
          this.notificationClass = this.notificationClassBad;
        });

      //this.$emit("edit-target");
    },
    addTarget() {
      if (!this.isFormValid) {
        this.showInvalidFormWarning();
        return;
      }

      const requestData = JSON.stringify({
        action: "create_target",
        ip: this.formData.ip,
        username: this.formData.sudo_user,
        password: this.password,
        alias: this.formData.alias,
        platform: this.formData.platform,
      });
      let axiosConfig = {
        headers: {
          "Content-Type": "application/json",
        },
      };
      this.$axios
        .post("api.php", requestData, axiosConfig)
        .then((response) => {
          console.log(response.data);
          this.notificationMessage = "Target added successfully.";
          this.notificationClass = this.notificationClassGood;
          this.$emit("newTargetAdded")
        })
        .catch((error) => {
          console.log(error);
          this.notificationMessage = "Error: " + error;
          this.notificationClass = this.notificationClassBad;
        });
    },
    async fetchSshKey() {
      try {
        const response = await this.$axios.get("api.php?action=get_pub_ssh_key");
        this.sshKey = response.data;
      } catch (error) {
        this.sshKey = "Error fetching SSH key";
      }
    },
  },
};
</script>

<style scoped></style>
