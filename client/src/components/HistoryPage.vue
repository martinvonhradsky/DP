<template>
  <div>
    <RouterLink
      :to="{ name: 'ItemDetail', params: { id: this.$route.params.id } }"
    >
      Back to item detail {{ this.$route.params.id }}
    </RouterLink>
    <table class="table-auto border-collapse w-full">
      <thead>
        <tr class="text-left bg-gray-300">
          <th class="px-4 py-2">Alias</th>
          <th class="px-4 py-2">Tech ID</th>
          <th class="px-4 py-2">Test time</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(item, index) in items"
          :key="index"
          :class="{
            'bg-green-500': item.detected,
            'bg-red-500': !item.detected,
          }"
          @click="openModal(item)"
          class="hover:bg-opacity-70 cursor-pointer"
          title="Click to view output details"
        >
          <td class="border px-4 py-2">{{ item.target }}</td>
          <td class="border px-4 py-2">{{ item.test_id }}</td>
          <td class="border px-4 py-2">
            {{ formatDateTime(item.start_time) }}
          </td>
        </tr>
      </tbody>
    </table>
    <OutputModal v-if="selectedItem" :outputFileId="selectedItem.output" @close="closeModal" />
  </div>
</template>

<!-- The rest of the script remains the same -->

<script>
import OutputModal from "./OutputModal.vue";

import { RouterLink } from "vue-router";

export default {
  components: {
    OutputModal,
    RouterLink,
  },
  data() {
    return {
      items: null,
      selectedItem: null,
    };
  },
  created() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      const id = this.$route.params.id;
      this.$axios
        .get(`api.php?action=history&id=${id}`)
        .then((response) => {
          this.items = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    formatDateTime(dateTime) {
      const date = new Date(dateTime);
      return date.toLocaleString("en-US", {
        day: "numeric",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
      });
    },
    openModal(item) {
      this.selectedItem = item;
    },
    closeModal() {
      this.selectedItem = null;
    },
  },
};
</script>
