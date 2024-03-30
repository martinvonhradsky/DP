<template>
  <div
    :class="{ hidden: !show }"
    :aria-hidden="!show"
    class="fixed inset-0 z-50 flex justify-center items-center overflow-x-hidden overflow-y-auto bg-gray-800 bg-opacity-50"
    @click.self="$emit('toggleModal')"
  >
    <div class="bg-white rounded-lg shadow-lg overflow-hidden w-4/6">
      <div class="px-2 pt-2 border-b flex justify-between">
        <slot name="header"></slot>
        <div>
          <button
            type="button"
            class="ml-auto text-gray-500 hover:text-gray-700"
            @click="$emit('toggleModal')"
          >
            <svg
              class="w-6 h-6 fill-current"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
            >
              <path
                d="M6.293 6.707a1 1 0 0 0 0 1.414L8.586 10l-2.293 2.293a1 1 0 0 0 1.414 1.414L10 11.414l2.293 2.293a1 1 0 0 0 1.414-1.414L11.414 10l2.293-2.293a1 1 0 0 0-1.414-1.414L10 8.586 7.707 6.293a1 1 0 0 0-1.414 0z"
              />
            </svg>
          </button>
        </div>
      </div>
      <div class="px-6 py-4">
        <slot name="body"></slot>
      </div>
      <div class="px-6 py-4 border-t">
        <slot name="footer"></slot>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "TargetModal",
  props: {
    show: {
      type: Boolean,
      required: true,
    },
  },
};
</script>
