

<template>
    <span v-if="outputFileId !== null" class="font-bold">Output:</span>
    <p
    v-if="outputFileId !== null"
    class="bg-white text-gray-800 px-4 py-2 rounded-md shadow-md border-2 border-black my-4"
    style="font-family: 'Courier New', Courier, monospace;"
    v-html="formattedOutput"
    >
    
    </p>
</template>

<script>

export default {
    name: "OutputBox",
    props: {
        outputFileId: {
            type: String,
            default: () => (null),
        },
    },
    data() {
        return {
            output: "",
        };
    },
    computed: {
        formattedOutput() {
            return this.output ? this.output.replace(/\\u[^;]*;1m/g, "").replace(/\\u[^[]+\[0m/g, "").replace(/(\n|\\n)/g, '<br>') : '';
        }
    },
    watch: {
        outputFileId: {
        deep: true,
        immediate: true,
        handler() {
            this.outputUpdate();
        },
        },
    },
    mounted() {
        setInterval(this.outputUpdate, 2000);
    },
    methods: {  
        outputUpdate() {
            if (this.outputFileId == null) return;
            this.$axios
                .get("api.php?action=result&outputFileId=" + this.outputFileId)
                .then((response) => {              
                    this.output = response.data.output;
                })
                .catch((error) => {
                    console.log(error);
                });
        },
    },
};
</script>

<style scoped></style>
