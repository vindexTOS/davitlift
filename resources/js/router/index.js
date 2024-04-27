// Composables
import { createRouter, createWebHistory } from 'vue-router'
import store from '../store'

const routes = [
  {
    path: '/',
    component: () => import('../layouts/default/Default.vue'),
    children: [
      {
        path: '',
        name: 'Home',
        component: () =>
          import(/* webpackChunkName: "home" */ '../views/Home.vue'),
      },
      {
        path: '/PageTest',
        name: 'PageTest',
        component: () =>
          import(/* webpackChunkName: "home" */ '../views/ComercialTest.vue'),
      },
      {
        path: 'company',
        name: 'company',
        component: () =>
          import(/* webpackChunkName: "home" */ '../views/CompanyPage.vue'),
      },
      {
        path: 'user/:id',
        name: 'userDetail',
        component: () =>
          import(/* webpackChunkName: "home" */ '../views/UserDetailed.vue'),
      },
      {
        path: 'company/:id',
        name: 'companyDetail',
        component: () =>
          import(
            /* webpackChunkName: "home" */ '../views/CompanyDetailPage.vue'
          ),
      },
      {
        path: 'manager/:id/:companyId',
        name: 'managerDetail',
        component: () =>
          import(
            /* webpackChunkName: "home" */ '../views/ManagerDetailPage.vue'
          ),
      },
      {
        path: 'users',
        name: 'users',
        component: () =>
          import(/* webpackChunkName: "home" */ '../views/UsersPage.vue'),
      },
      {
        path: 'devices',
        name: 'devices',
        component: () =>
          import(/* webpackChunkName: "home" */ '../views/DevicesPage.vue'),
      },
      {
        path: 'devices/:id',
        name: 'devicesDetail',
        component: () =>
          import(
            /* webpackChunkName: "home" */ '../views/DevicesDetailPage.vue'
          ),
      },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () =>
          import(/* webpackChunkName: "home" */ '../views/DashboardPage.vue'),
      },
      {
        path: 'file',
        name: 'file',
        component: () =>
          import(/* webpackChunkName: "home" */ '../views/FileUpload.vue'),
      },
      {
        path: 'unregistreddevices',
        name: 'unregistreddevices',
        component: () =>
          import(
            /* webpackChunkName: "home" */ '../views/UnregistredDevicesPage.vue'
          ),
      },
    ],
    beforeRouteLeave(to, from, next) {
      return next(false)
    },
  },
  {
    beforeRouteLeave(to, from, next) {
      return next(false)
    },
    name: 'Login',
    path: '/login',
    component: () => import('../views/LoginPage.vue'),
  },
  {
    beforeRouteLeave(to, from, next) {
      return next(false)
    },
    name: 'Registration',
    path: '/registration',
    component: () => import('../views/RegistrationPage.vue'),
  },
  {
    beforeRouteLeave(to, from, next) {
      return next(false)
    },
    name: 'Forgot',
    path: '/forgot',
    component: () => import('../views/ForgotPage.vue'),
  },
  {
    beforeRouteLeave(to, from, next) {
      return next(false)
    },
    name: 'Default',
    path: '/home',
    component: () => import('../views/DefoultPage.vue'),
  },
  {
    beforeRouteLeave(to, from, next) {
      return next(false)
    },
    path: '/:catchAll(.*)',
    component: () => import('../views/404Page.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(''),
  routes,
})
router.beforeEach(async (to, from) => {
  if (
    !!store.state.auth.authenticated &&
    // ❗️ Avoid an infinite redirect
    to.name === 'Login'
  ) {
    return { name: 'Home' }
  }
  if (
    // make sure the user is authenticated
    !store.state.auth.authenticated &&
    // ❗️ Avoid an infinite redirect
    to.name !== 'Login' &&
    to.name !== 'Registration' &&
    to.name !== 'Forgot' &&
    to.name !== 'Default'
  ) {
    // redirect the user to the login page
    return { name: 'Default' }
  }
})

export default router
