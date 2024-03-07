/**
 * plugins/index.js
 *
 * Automatically included in `./src/main.js`
 */

// Plugins
import { loadFonts } from './webfontloader'
import vuetify from './vuetify'
import store  from '../store'
import router from '../router'
import { createI18n } from 'vue-i18n'
import {en} from "../languages/en";
import {ka} from "../languages/ka";
import VueSweetalert2 from 'vue-sweetalert2';

const i18n = createI18n({
  locale: 'ka',
  fallbackLocale: 'en',
  messages : {
    en : en,
    ka : ka
  }
})
export function registerPlugins (app) {
  loadFonts()
  app
    .use(vuetify)
    .use(store )
    .use(router)
    .use(i18n).use(VueSweetalert2)

}
