import axios from 'axios'
import router from '../router'
export default {
  namespaced: true,
  state: {
    authenticated: false,
    user: {},
  },
  getters: {
    authenticated(state) {
      return state.authenticated
    },
    user(state) {
      return state.user
    },
  },
  mutations: {
    SET_AUTHENTICATED(state, value) {
      state.authenticated = value
    },
    SET_USER(state, value) {
      console.log(value, 'VALUE')
      state.user = value
    },
    SET_LVL(state, value) {
      state.lvl = value
    },
  },
  actions: {
    login({ commit }) {
      return axios
        .get('/api/user')
        .then(({ data }) => {
          console.log(data)
          commit('SET_USER', data)
          commit('SET_AUTHENTICATED', true)
          router.push({ name: 'Home' })
        })
        .catch(({ response: { data } }) => {
          commit('SET_USER', {})
          commit('SET_AUTHENTICATED', false)
        })
    },
    logout({ commit }) {
      commit('SET_USER', {})
      commit('SET_AUTHENTICATED', false)
    },
  },
}
