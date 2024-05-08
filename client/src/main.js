import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";
import "./style.css";

import router from "./router.js";
import axios from "axios";
import { SERVER_IP } from "../config"; // Import the SERVER_IP constant
import "vue-universal-modal/dist/index.css";
import VueUniversalModal from "vue-universal-modal";

axios.defaults.baseURL = SERVER_IP; // Set the default base URL for axios

const pinia = createPinia();
const app = createApp(App);
app.use(router);
app.config.globalProperties.$axios = axios;
app.use(pinia);
app.use(VueUniversalModal, {
  teleportTarget: "#modals",
});
app.mount("#app");
