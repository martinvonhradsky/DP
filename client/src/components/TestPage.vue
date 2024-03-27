<template>
    <div>
      <NavbarVue />
      <CustomTestForm class="test" />
    </div>
  </template>
  
  <script>
  import NavbarVue from './PageNavbar.vue';
  import CustomTestForm from './CustomTestForm.vue';
  
  export default {
    components: {
      NavbarVue,
      CustomTestForm,
    },
  };
  </script>
  
  <style scoped>
    .test {
        margin-top: 30px;
        margin-left: 30px;
    }
  </style>
  