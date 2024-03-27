<template>
  <div
    class="border-gray-500 border-solid border h-4/5 bg-white rounded shadow-inner shadow-sm p-5 overflow-scroll"
  >
    <div class="flex">
      <CategoryColumn
        v-for="(category, categoryName) in data"
        :key="categoryName"
        class="flex-1 mr-2"
        :name="categoryName"
        :items="category.items"
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
};
</script>

<style>
.CategoryColumn {
  flex: 1;
}
</style>
