<template>
  <div>
    <NavbarVue />
    <CustomTest class="test" />
  </div>
</template>

<script>
import NavbarVue from "./PageNavbar.vue";
import CustomTest from "./CustomTest.vue";

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
