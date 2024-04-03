<template>
  <div
    class="border-gray-500 border-solid border h-4/5 bg-white rounded shadow-inner shadow-sm p-5 overflow-scroll"
  >
    <div class="flex">
      <CategoryColumn
        v-for="categoryName in sortedCategories"
        :key="categoryName"
        class="flex-1 mr-2"
        :name="categoryName"
        :items="data[categoryName].items"
      />
    </div>
  </div>
</template>

<script>
import CategoryColumn from "./CategoryColumn.vue";

export default {
  name: "ContentWrapper",
  components: { CategoryColumn },
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  computed: {
    sortedCategories() {
      const columnOrder = [
        "Reconnaissance",
        "Resource Development",
        "Initial Access",
        "Execution",
        "Persistence",
        "Privilege Escalation",
        "Defense Evasion",
        "Credential Access",
        "Discovery",
        "Lateral Movement",
        "Collection",
        "Command and Control",
        "Exfiltration",
        "Impact",
      ];
      // Filter out the categories in the data object based on the column order
      return columnOrder.filter(categoryName => this.data.hasOwnProperty(categoryName));
    },
  },
};
</script>
