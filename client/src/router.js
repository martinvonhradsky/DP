import { createRouter, createWebHistory } from "vue-router";

const routes = [
  {
    path: "/",
    component: () => import("./components/LandingPage.vue"),
    name: "Home",
  },
  {
    path: "/items/:id",
    component: () => import("./components/ItemDetail.vue"),
    name: "ItemDetail",
  },
  {
    path: "/history/:id",
    component: () => import("./components/HistoryPage.vue"),
    name: "HistoryPage",
  },
  {
    path: "/target",
    component: () => import("./components/TargetPage.vue"),
    name: "ManageTarget",
  },
  {
    path: "/test",
    component: () => import("./components/TestPage.vue"),
    name: "ManageCustomTest",
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router; // export the router instance
