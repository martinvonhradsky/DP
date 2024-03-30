<template>
  <div class="">
    <div class="tab__header">
      <h1
        href="#"
        class="tab__link p-4 block cursor-pointer bg-indigo-700 hover:bg-indigo-800 no-underline text-white border-b-2 border-indigo-500 flex justify-between transition-colors duration-200 ease-in-out"
        @click.prevent="active = !active"
        @click="fetchTests(item.id)"
      >
        <div class="flex">
          <strong>{{ item.id }}&nbsp;</strong>
          <h2>- {{ item.name }}</h2>
        </div>
        <span class="down-Arrow" v-show="!active">&#9660;</span>
        <span class="up-Arrow" v-show="active">&#9650;</span>
      </h1>
    </div>
    <div
      class="tab__content p-2 overflow-hidden transition-all ease-in-out duration-300 h-0"
      :style="`height: ${active ? 'auto' : '0'}; opacity: ${
        active ? '1' : '0'
      };`"
    >
      <div class="flex flex-col">
        <!-- Right -->
        <!-- Right -->
        <div>
          <br />
          <p>
            <span class="font-bold">Description -</span>&nbsp;{{
              item.description
            }}
          </p>
          <br />
          <p>
            <span class="font-bold">url -</span>&nbsp;<a :href="item.url">{{
              item.url
            }}</a>
          </p>
          <br />
          <p>
            <span v-if="!noTests" class="font-bold">Available tests:</span>
            <span v-if="noTests" class="font-bold border-b-2 border-black">
              Tests are not available for this technique
            </span>
          </p>

          <div v-for="test in tests" :key="test.id">
            <input
              type="radio"
              :id="'radio' + test"
              :name="'radio_group'"
              :value="test"
              v-model="selectedTest"
              @change="selectTest(test)"
            />
            <label :for="'radio' + test">{{ test.id }} - {{ test.name }}</label
            ><br />
          </div>

          <span v-if="executeOutput !== null" class="font-bold">Output:</span>
          <p
            v-if="executeOutput !== null"
            class="bg-white text-gray-800 px-4 py-2 rounded-md shadow-md border-2 border-black my-4"
          >
            {{ executeOutput }}
          </p>
        </div>
      </div>

      <!-- END RIGHT -->
    </div>
  </div>
</template>

<script>
export default {
  name: "TechDetail",
  props: {
    item: {
      type: Object,
      required: true,
    },
    title: {
      type: String,
      default: "",
    },
    executeOutput: {
      type: String,
      default: null,
    },
  },
  data() {
    return {
      active: false,
      localExecuteOutput: this.executeOutput,
      noTests: null,
      tests: null,
    };
  },
  setup() {
    return {};
  },
  methods: {
    fetchTests(techId) {
      this.$axios
        .get(`api.php?action=test_by_id&id=${techId}`)
        .then((response) => {
          if (response.data[0].Error) {
            this.noTests = response.data[0].Error;
          } else {
            this.tests = response.data;
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    selectTest(test) {
      this.$emit("test-selected", test);
    },
  },
  watch: {
    executeOutput(newValue) {
      // Update localExecuteOutput when executeOutput prop changes
      this.localExecuteOutput = newValue;
    },
  },
};
</script>

<style lang="scss" scoped></style>
