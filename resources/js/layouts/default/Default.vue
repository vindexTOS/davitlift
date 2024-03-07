<template>
  <v-layout  class="rounded rounded-md">
    <v-navigation-drawer color="white"
                        v-if="user.lvl > 1"
                         v-model="drawer"
     :width="$vuetify.display.xs? $vuetify.display.width : 260"
    >

      <v-list
        density="compact"
        nav
      >
        <v-list-item :key="'profile'" prepend-icon="mdi-account" :to="{name: 'Home'}">{{$t('Profile')}}</v-list-item>
          <v-list-item :key="'dashboard'" v-if="user.lvl === 2 || user.lvl === 3" prepend-icon="mdi-view-dashboard" :to="generateRoute()">{{$t('Manage')}}</v-list-item>
          <v-list-item :key="'company'"  v-if="user.lvl === 4" prepend-icon="mdi-domain" to="/company">{{$t('Companies')}}</v-list-item>
        <v-list-item :key="'users'" v-if="user.lvl === 4" prepend-icon="mdi-account-group"  to="/users" >{{$t('Users')}}</v-list-item>
          <v-list-item :key="'devices'" v-if="user.lvl === 4" prepend-icon="mdi-elevator-passenger-outline"  to="/devices" >{{$t('Lifts')}}</v-list-item>
          <v-list-item :key="'unregistreddevices'" v-if="user.lvl >= 4" prepend-icon="mdi-elevator-passenger"  to="/unregistreddevices" >{{$t('Unregistered lifts')}}</v-list-item>
          <v-list-item :key="'files'" v-if="user.lvl >= 4" prepend-icon="mdi-file"  to="/file" >{{$t('Firmware update')}}</v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-app-bar
      :order="order"
      color="black"
    >

      <template v-slot:prepend>
        <v-btn
            v-if="user.lvl >= 2"
            icon="mdi-menu"
            @click.stop="drawer = !drawer"
        >
        </v-btn>
        <img src="../../assets/eideas-logo-vector.svg" width="100" height="40" alt="">

      </template>
      <template v-slot:append>
          <v-btn
              :disabled="this.$i18n.locale === language.code"
              v-for="language in languages"
              :key="language.code"
              @click="setLanguage(language.code)"
          >
              <span
                  style="cursor: pointer;"
              >
      <span
          :class="['flag-icon', `flag-icon-${language.flag}`]"></span>
      <span v-if="!$vuetify.display.xs ">{{ language.name }}</span>
    </span>
              </v-btn>
              <v-btn icon="mdi-logout"  @click="logout"/>
      </template>
    </v-app-bar>

    <v-container :class="{
      'pa-1' : $vuetify.display.xs
    }" class="d-md-flex  justify-md-space-between" >
      <default-view />
    </v-container>
  </v-layout>
</template>

<script>
  import DefaultView from './View.vue'
  import {mapActions} from 'vuex'
  export default {
    components : {DefaultView},
    data () {
      return {
        order: 0,
        drawer: null,
        user:this.$store.state.auth.user,
          languages: [
              { name: 'English', code: 'en', flag: 'us' },
              { name: 'ქართული', code: 'ka', flag: 'ge' },
              // ... add more languages as necessary
          ],
          selectedLanguage: 'ka'  // default selected language

      }
    },
    methods: {
        ...mapActions({
            signOut:"auth/logout",
        }),
        async logout(){
            await axios.post('/api/logout').then(({data})=>{
                this.signOut()
                this.$router.push({name:"Login"})
            })
        },
        generateRoute() {
            if(this.user.company) {
                return '/company/' + this.user.company.id
            }
            if(this.user.device) {
                return `/manager/${this.user.id}/${this.user.device.company_id}`
            }
        },
        setLanguage(lang) {
            console.log("Selected language:", lang);
            // If you're using a package like vue-i18n, you'd set the locale here:
            this.$i18n.locale = lang;
        }
    }
  }
</script>
<style>
@import 'flag-icon-css/css/flag-icons.min.css';

</style>
