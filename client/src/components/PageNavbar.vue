<template>
  <nav>
    <router-link to="/" :class="{ active: isActive('Home') }" class="nav-link">Home</router-link>
    <router-link to="/target" :class="{ active: isActive('ManageTarget') }" class="nav-link">Manage Targets</router-link>
    <router-link to="/test" :class="{ active: isActive('ManageCustomTest') }" class="nav-link">Manage Custom Tests</router-link>
  </nav>
</template>

<script>
export default {
  methods: {
    isActive(routeName) {
      return this.$route.name === routeName;
    },
  },
};
</script>

<style>
nav {
  display: flex;
  background-color: #87b5ff;
  padding: 10px;
}

.nav-link {
  color: white;
  margin-right: 10px; /* Adjust the margin to add space between links */
  text-decoration: none;
}

.active {
  text-decoration: underline;
}
</style>
