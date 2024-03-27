<template>
  <div class="text-xl p-2">
    <CategoryHeader :name="name" :item-count="items.length" />
    <ColumnItem
      v-for="item in items"
      :key="item.id"
      :item="item"
    />
  </div>
</template>

<script>
import CategoryHeader from "./CategoryHeader.vue";
import ColumnItem from "./ColumnItem.vue";
export default {
  name: "CategoryColumn",
  components: { CategoryHeader, ColumnItem },
  props: {
    name: {
      type: String,
      required: true,
    },
    items: {
      type: Array,
      required: true,
    },
  },
};
</script>
