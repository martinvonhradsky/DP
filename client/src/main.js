import { createApp } from "vue";
import App from "./App.vue";
import "./style.css";

import router from "./router.js";
import axios from "axios";
import { SERVER_IP } from "../config"; // Import the SERVER_IP constant

axios.defaults.baseURL = SERVER_IP; // Set the default base URL for axios

const app = createApp(App);
app.use(router);
app.config.globalProperties.$axios = axios;
app.mount("#app");
