import Vue from 'vue'
import VueRouter from 'vue-router'
import lanhu_02transactionv2dark from '../views/lanhu_02transactionv2dark/index.vue'
import lanhu_01homev2dark from '../views/lanhu_01homev2dark/index.vue'

Vue.use(VueRouter)

const routes = [
    {
    path: '/',
    redirect: "/lanhu_02transactionv2dark"
  },
  {
    path: '/lanhu_02transactionv2dark',
    name: 'lanhu_02transactionv2dark',
    component: lanhu_02transactionv2dark
  },
  {
    path: '/lanhu_01homev2dark',
    name: 'lanhu_01homev2dark',
    component: lanhu_01homev2dark
  }
]

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  routes
})

export default router
