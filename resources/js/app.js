// Components
import App from './App.vue'
// Composables
import { createApp } from 'vue'
import axios from 'axios';
import router from "./router";

import store from "./store";
import 'sweetalert2/dist/sweetalert2.min.css';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.interceptors.response.use(
    response => {
        return response;  // If the response was successful, just return it.
    },
    error => {
        if (error && error.response && error.response.status) {

            if(error && error.response.status === 401) {
                store.dispatch('auth/logout');
                 router.push({name : "Login"})

            }
            if(error && error.response.status !== 401)
                // alert(error.response.data.message)
                Swal.fire({
                    icon: 'error',
                    position:'top',
                    allowOutsideClick: false,
                    text: error.response.data.message,
                })
        }

        // Always reject the promise in case of an error.
        return Promise.reject(error);
    }
);


const token = localStorage.getItem('token');

if (token) {
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;
}
// Plugins
import { registerPlugins } from './plugins'

import './registerServiceWorker'
import Swal from "sweetalert2";

const app = createApp(App)

registerPlugins(app)
app.mount('#app')
