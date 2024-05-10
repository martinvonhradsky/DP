<template>
  
  <div
    v-if="loading"
    class="h-screen w-screen flex items-center justify-center"
  >
    <LoadingSpinner style="font-size: 3em" />
  </div>
  <div v-else>
    <NavbarVue/>
    <ContentWrapper :data="data" />
  </div>
</template>

<script>
import ContentWrapper from "./ContentWrapper.vue";
import LoadingSpinner from "./LoadingSpinner.vue";
import NavbarVue from "./PageNavbar.vue"

export default {
  name: "LandingPage",

  components: {
    NavbarVue,
    ContentWrapper,
    LoadingSpinner,
  },

  data() {
    return {
      theme: 'light',
      activePage: 0,
      data: {},
      loading: true,
    };
  },

  async mounted() {
    try {
      const response = await this.$axios.get("api.php?action=startpage");
      const newData = this.restructureData(response.data);
      this.data = newData;
      console.log(this.data);
      this.loading = false;
    } catch (error) {
      console.log(error);
      this.loading = false;
    }
  },

  methods: {
    restructureData(data) {
      const newData = {};

      data.forEach((item) => {
        const tacticList = item.tactics.split(", ");
        tacticList.forEach((tactic) => {
          if (!newData[tactic]) {
            newData[tactic] = {
              name: tactic,
              items: [],
            };
          }
          newData[tactic].items.push({
            id: item.id,
            name: item.name,
            tactics: item.tactics,
            status: item.status,
          });
        });
      });

      // Sort by Name inside columns
      Object.keys(newData).forEach((tactic) => {
          newData[tactic].items.sort((a, b) => a.name.localeCompare(b.name));
      });
      return newData;
    },
  },
};
</script>
