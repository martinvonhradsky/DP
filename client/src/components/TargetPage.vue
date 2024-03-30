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

            <div  v-if="selectedTarget !== null">
                <target-form-edit ref="refEditForm" :selected-target="targetDetails"></target-form-edit>
            </div>
            <div v-else>
                <target-form ref="refAddTargetForm"></target-form>            
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
                        <tr v-for="target in targets" :key="target.id" @click="handleTargetSelect(target)" :class="{ 'bg-blue-200': selectedTarget === target }">
                            <td class="px-4 py-2">{{ target.alias }}</td>
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

                <target-form-delete :selected-target="selectedTarget.alias"></target-form-delete>
            </div>
            <div v-else class="flex justify-center items-center flex-col space-y-4">
                <div>
                    <button 
                        class="btn btn-blue"
                        :class="{
                        'bg-gray-500': !callIsFormValid,
                        'cursor-not-allowed': !callIsFormValid,
                        }"
                        type="submit"
                        @click="callTargetRunSetup"
                        >Run setup
                    </button>      
                </div>   
                <div>       
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
    </div>
  </template>
  
  
  <script>
  import NavbarVue from "./PageNavbar.vue";
  import TargetForm from "./TargetForm.vue";
  import TargetFormDelete from './TargetFormDelete.vue';
  import TargetFormEdit from "./TargetFormEdit.vue";


  
  export default {
    name: "TargetPage",
    components: {
      NavbarVue,
      TargetForm,
      TargetFormEdit,
      TargetFormDelete
    },
    data() {
      return {
        selectedTarget: null,
        showTargetModal: false,
        toggleModal: true,
        targets: [],
        targetDetails: [],
      };
    },
    methods: {
        callTargetEdit() {
            this.$refs.refEditForm.editTarget();
        },
        callTargetAdd() {
            this.$refs.refAddTargetForm.addTarget();
            

        },
        callTargetRunSetup() {
            this.$refs.refAddTargetForm.runSetup();

        },
        callIsFormValid(){
            this.$refs.refAddTargetForm.isFormValid();
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
          this.targets = response.data;
        })
        .catch((error) => {
          console.log(error);
        });
    },
    handleTargetSelect(target) {
        console.log(target);
        if (target == this.selectedTarget){
            this.selectedTarget = null;
            return;
        }
        this.selectedTarget = target;
        this.targetDetails = this.fetchTargetDetails(target.alias);
        
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
  