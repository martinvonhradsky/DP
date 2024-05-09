<template lang="">
  <div
    class="rounded-lg shadow-md inline-flex items-center w-full border-black border mb-2 border-solid"
    :class="statusColorClass"
  >
    <div class="relative flex-grow justify-center items-center">
      <RouterLink
        :to="{ name: 'ItemDetail', params: { id: item.id } }"
        class="text-xs pl-1 link"
      >
        {{ item.name }}
      </RouterLink>
    </div>
  </div>
</template>

<script>
import { RouterLink } from "vue-router";

export default {
  name: "ColumnItem",
  components: {
    RouterLink,
  },
  props: {
    item: {
      type: Object,
      required: true,
    },
  },
  computed: {
    statusColorClass() {
      switch (this.item.status) {
        case "available":
          return "bg-yellow-100 text-black";
        case "not available":
          return "bg-gray-100 text-black";
        case "detected":
          return "bg-green-300 text-black";
        case "executed":
          return "bg-orange-300 text-black";
        default:
        return "bg-gray-100 text-black";
      }
    },
  },
};
</script>

<style scoped>
.link {
  cursor: pointer;
  text-decoration: none;
  padding: 10px;
  display: flex;
  text-align: center;
  align-items: center;
  justify-content: center;
  height: 100%;
  border-radius: 5px; /* Add a slight border radius */
  transition: background-color 0.3s, color 0.3s; /* Add a smooth transition */
}

.link:hover {
  background-color: #e2e8f0; /* Change background color on hover */
}

.link:visited {
  color: inherit;
}

.link span {
  transition: transform 0.3s; /* Add a smooth transition for the text */
}

.link:hover span {
  transform: scale(1.05); /* Scale the text on hover */
}
</style>

