import Vue from 'vue';
import Router from 'vue-router';
Vue.use(Router);
const router = new Router({mode: 'history', base: '/', routes: [
		{path: '/', name: 'HomePage', component: () => import(/* webpackChunkName: "GamePage" */ '../pages/Game.vue')},
	]
});
export default router;
