<template>
  <div class="px-2 border-black border-b border-solid mb-2">
    <div class="text-center min-h-[3rem] w-70px items-center justify-center"> <!-- Adjust the height value as needed -->
      <h1 class="text-sm ">{{ name }}</h1>
    </div>
    <p class="text-xs text-center">{{ itemCount }} techniques</p>
  </div>
</template>

<script>
export default {
  name: "CategoryHeader",
  props: {
    name: {
      type: String,
      required: true,
    },
    itemCount: {
      type: Number,
      required: true,
    },
  },
};
</script>
