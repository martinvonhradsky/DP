<template>
    <div>
      <navbar-vue/>
      
      <div class="flex">
        <!-- Target Form covering 2/3 of the screen -->
        <div class="w-2/3 m-3">
            <div class="p-2">
                <h1 class="text-3xl font-bold text-gray-800 mb-4" v-if="selectedTarget !== null">
                    Selected Target Alias: {{ selectedTarget.alias }}
                </h1>
                <h1 class="text-3xl font-bold text-gray-800 mb-4" v-else>
                    Add New Target
                </h1>
            </div>

            <div>
                <target-form-edit @newTargetAdded="fetchTargets()" ref="refEditForm" :is-new="selectedTarget == null" :selected-target="targetDetails"></target-form-edit>
                <output-box :outputFileId="selectedTarget != null ? selectedTarget.outputFileId : null" />  
            </div>
        </div>
        <!-- Table with Targets -->
        <div class="w-1/3 m-3">
            <div class="flex flex-col">
                <table class="table-auto border border-gray-200 shadow-md mb-10 rounded-2xl">
                    <thead class="bg-gray-100 rounded-2xl">
                        <tr>
                            <th class="px-4 py-2 rounded-2xl">Select Target</th>
                        </tr>
                    </thead>
                    <tbody>
                      <tr v-for="target in targets" :key="target ? target.alias : null" @click="handleTargetSelect(target)" :class="{ 'bg-blue-200': selectedTarget === target }">
                            <td class="px-4 py-2">
                                <span v-if="target === null"><i>New Target</i></span>
                                <span v-else>{{ target.alias }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

          
            <!-- this is where i want the buttons from target editForm if selectedTarget is not null-->
            <div v-if="selectedTarget !== null" class="flex justify-center items-center flex-col space-y-4">
                
                <button 
                    class="btn btn-blue"
                    type="submit"
                    @click="callTargetEdit">Edit Target</button>

                <button 
                    class="btn btn-blue"
                    :class="{
                    'bg-gray-500': !callIsFormValid,
                    'cursor-not-allowed': !callIsFormValid,
                    }"
                    type="submit"
                    @click="runSetup()"
                    >Run setup
                </button>      
                <target-form-delete @targetDeleted="fetchTargets()" :selected-target="selectedTarget.alias"></target-form-delete>
            </div>
            <div v-else class="flex justify-center items-center flex-col space-y-4">
              <button 
                  class="btn btn-blue"
                  :class="{
                  'bg-gray-500': !callIsFormValid,
                  'cursor-not-allowed': !callIsFormValid,
                  }"
                  type="submit"
                  @click="callTargetAdd"
                  >Add target
              </button>
            </div>
        </div>
      </div>
    </div>
  </template>
  
  
  <script>
  import NavbarVue from "./PageNavbar.vue";
  import TargetFormDelete from './TargetFormDelete.vue';
  import TargetFormEdit from "./TargetFormEdit.vue";
  import OutputBox from "./OutputBox.vue";

  export default {
    name: "TargetPage",
    components: {
      NavbarVue,
      TargetFormEdit,
      TargetFormDelete,
      OutputBox,
    },
    data() {
      return {
        selectedTarget: null,
        targets: [],
        targetDetails: [],
      };
    },
    methods: {
        callTargetEdit() {
            this.$refs.refEditForm.editTarget();
        },
        callTargetAdd() {
            this.$refs.refEditForm.addTarget();
        },
        callIsFormValid(){
            this.$refs.refEditForm.isFormValid();
        },
        fetchTargetDetails(alias) {
            if (!this.selectedTarget) {
                return;
            }
            const apiUrl = `api.php?action=target_detail&alias=${alias}`;
            this.$axios
                .get(apiUrl)
                .then((response) => {
                this.targetDetails = response.data;
                })
                .catch((error) => {
                console.log(error);
                });
    },fetchTargets() {
      this.$axios
        .get("api.php?action=targets")
        .then((response) => {
          const newTargets = response.data;

          // Persist output over refetches of data.
          for (let j = 0; j < newTargets.length; ++j) {
            newTargets[j].outputFileId = null;
            
            // The last target is skiped -- it's `null`, see below.
            for (let i = 0; i < this.targets.length - 1; ++i) {
              if (this.targets[i].alias == newTargets[j].alias) {
                newTargets[j].outputFileId = this.targets[i].outputFileId;
              }
            }
          }
          this.targets = newTargets;
          // Represents "New Target".
          this.targets.push(null);
        })
        .catch((error) => {
          console.log(error);
        });
    },
    runSetup() {
      const apiUrl = `run-ansible.php?action=setupTarget&alias=${this.selectedTarget.alias}`;
      this.$axios
        .get(apiUrl)
        .then((response) => {
          console.log(response.data.output_file_id);
          this.selectedTarget.outputFileId = response.data.output_file_id;
          this.notificationMessage = "Setup started successfully.";
          this.notificationClass = "bg-green-500 text-white";
        })
        .catch((error) => {
          console.log(error);
          this.notificationMessage = "Error: " + error;
          this.notificationClass = "bg-red-500 text-white";
        })
        .finally(() => {
          
        });
        this.$emit("run-setup");
    },
    handleTargetSelect(target) {
        console.log(target);
        if (target == this.selectedTarget){
            return;
        }
        this.selectedTarget = target;
        if (target == null) { // New Target
          this.targetDetails = [];
        } else {
          this.targetDetails = this.fetchTargetDetails(target.alias);
        }
        
    }

    },
    mounted() {
      this.fetchTargets()
    },
  };
  </script>
  
  <style scoped>
  /* Component styles */
  </style>
  