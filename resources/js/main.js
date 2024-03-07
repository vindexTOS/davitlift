/**
 * main.js
 *
 * Bootstraps Vuetify and other plugins then mounts the App`
 */
import axios from 'axios';
window.axios = axios;

import store from "@/store";
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Components
import App from './App.vue'

// Composables
import { createApp } from 'vue'

// Plugins
import { registerPlugins } from '@/plugins'

import './registerServiceWorker'

const app = createApp(App)
app.config.globalProperties.canAccess = function (number) {
    console.log(store)
    return number > 3;
}
registerPlugins(app)

app.mount('#app')
