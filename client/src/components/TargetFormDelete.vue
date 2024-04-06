<template>
  <div>
    <button
      class="btn btn-blue"
      type="submit"
      @click="deleteTarget"
    >
      DELETE
    </button>
    <div
      v-if="notificationMessage"
      :class="notificationClass"
      class="rounded-md p-4 mb-4"
    >
      {{ notificationMessage }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    selectedTarget: {
      type: String,
      default: () => (''),
    },
  },
  data() {
    return {
      notificationMessage: "",
      notificationClass: "",
    };
  },
  methods: {
    deleteTarget() {
      const requestData = JSON.stringify({
        action: "target",
        alias: this.selectedTarget,
      });

      let axiosConfig = {
        headers: {
          "Content-Type": "application/json",
        },
        data: requestData,
      };

      this.$axios
        .delete("api.php", axiosConfig)
        .then((response) => {
          console.log(response.data);
          this.notificationMessage = "Deletion successful.";
          this.notificationClass = "bg-green-500 text-white";
        })
        .catch((error) => {
          console.log(error);
          this.notificationMessage = "Error: " + error;
          this.notificationClass = "bg-red-500 text-white";
        });
    },
  },
};
</script>

<style scoped></style>
