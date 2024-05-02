<template>
  <div class="">
    <div class="tab__header">
      <h1
        href="#"
        class="tab__link p-4 block cursor-pointer bg-indigo-700 hover:bg-indigo-800 no-underline text-white border-b-2 border-indigo-500 flex justify-between transition-colors duration-200 ease-in-out"
        @click.prevent="active = !active"
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
            <span class="font-bold">Url -</span>&nbsp;<a :href="item.url">{{
              item.url
            }}</a>
          </p>
          <br />

          <span class="font-bold">Available tests:</span>

          <div v-if="!item.tests">
            <p>Fetching...</p>
          </div>
          <div v-else>
            <p>
              <span v-if="item.tests.length == 0">
                (No tests available for this technique)<br>
              </span>
            </p>
            <div v-for="test in item.tests" :key="test.test_number">
              <input
                  type="radio"
                  :id="'radio' + test.technique_id + test.test_number"
                  :name="'radio_group'"
                  :value="test"
                  v-bind="selectedTest"
                  @change="selectTest(test)"
              />
              <label :for="'radio' + test.technique_id + test.test_number">{{ test.test_number }} - {{ test.name }}</label>
              <div class="space-x-1" v-if="selectedTest === test && test.arguments">
                  <label for="textInput">Arguments</label>
                  <input type="text" id="textInput" v-model="test.argumentsValue"/>
              </div>
              <br />
            </div>
          </div>
          
          <output-box :outputFileId="outputFileId" />  
        </div>
      </div>

      <!-- END RIGHT -->
    </div>
  </div>
</template>

<script>
import OutputBox from './OutputBox.vue';

export default {
  name: "TechDetail",
  components: {
    OutputBox,
  },
  props: {
    item: {
      type: Object,
      required: true,
    },
    selectedTest: {
      type: [Object, null],
      required: true,
    },
    outputFileId: {
      type: [String, null],
      required: true,
    }
  },
  data() {
    return {
      active: false,
    };
  },

  setup() {
    return {};
  },
  methods: {
    selectTest(test) {
      this.$emit("test-selected", test);
    },
  },
};
</script>

<style lang="scss" scoped></style>
