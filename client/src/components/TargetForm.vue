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
        v-model="targetIp"
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
        v-model="sudoUser"
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
        v-model="platform"
        required
      />
    </div>
    <div class="mb-5">
      <label class="block text-gray-700 font-bold mb-2" for="alias">
        Alias
      </label>
      <input
        class="w-full h-fit border border-gray-300 rounded-md py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        id="alias"
        type="text"
        v-model="alias"
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
    {{ this.setupOutput }}
  </div>
</template>

<script>
export default {
  name: "TargetForm",
  props: {
    selectedTarget: {
      type: String,
    },
  },
  data() {
    return {
      targetIp: "",
      sudoUser: "",
      password: "",
      platform: "",
      alias: "",
      isLoading: false,
      notificationMessage: "",
      notificationClass: "",
      setupOutput: "",
    };
  },
  computed: {
    isFormValid() {
      return (
        this.targetIp &&
        this.sudoUser &&
        this.password &&
        this.platform &&
        this.alias
      );
    },
  },

  methods: {
    addTarget() {
      if(!this.isFormValid){
        this.notificationMessage = "To add target all fields have to be filled.";
        this.notificationClass = "bg-red-500 text-white";
        return;
      }

      const requestData = JSON.stringify({
        action: "create_target",
        ip: this.targetIp,
        username: this.sudoUser,
        password: this.password,
        alias: this.alias,
        platform: this.platform,
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
          this.notificationClass = "bg-green-500 text-white";
        })
        .catch((error) => {
          console.log(error);
          this.notificationMessage = "Error: " + error;
          this.notificationClass = "bg-red-500 text-white";
        });
        this.$emit("add-target")
    },
    runSetup() {
      if(!this.isFormValid){
        this.notificationMessage = "To add target all fields have to be filled.";
        this.notificationClass = "bg-red-500 text-white";
        return;
      }
      const apiUrl = `run-ansible.php?action=setupTarget&ip=${this.targetIp}&user=${this.sudoUser}&pass=${this.password}`;
      this.$axios
        .get(apiUrl)
        .then((response) => {
          console.log(response.data);
          this.setupOutput = response.data;
          this.notificationMessage = "Setup started successfully.";
          this.notificationClass = "bg-green-500 text-white";
          this.isLoading = true;
          this.updateSetupOutput();
        })
        .catch((error) => {
          console.log(error);
          this.notificationMessage = "Error: " + error;
          this.notificationClass = "bg-red-500 text-white";
        })
        .finally(() => {
          
        });
        this.$emit("run-setup");
    },
    updateSetupOutput() {
      if (this.isLoading) {
        const intervalId = setInterval(() => {
          this.$axios
            .get("api.php?action=result")
            .then((response) => {              
              this.setupOutput = response.data.output;
              
              if (response.data.end) {
                clearInterval(intervalId); // Stop the interval when response.data.end is true
              }
            })
            .catch((error) => {
              console.log(error);
            });
        }, 2000);
        this.isLoading = false;
      }
    }
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