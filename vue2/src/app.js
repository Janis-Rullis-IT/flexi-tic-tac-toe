import Vue from 'vue';
import router from "./router";
import App from './App.vue';
import VueResource from "vue-resource";
Vue.use(VueResource);

Vue.http.options.root = 'http://api..lm1.local';

new Vue({router, render: h => h(App)}).$mount("#app");
