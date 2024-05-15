<template>
  <div>
    <NavbarVue />
    <CustomTest />
  </div>
</template>

<script>
import CustomTest from "./CustomTest.vue";
import NavbarVue from "./PageNavbar.vue";

export default {
  components: {
    NavbarVue,
    CustomTest,
  },
};
</script>
<style scoped>
.test {
  margin-top: 30px;
  margin-left: 30px;
}
</style>
