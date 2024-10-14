<template>
  <v-layout class="rounded rounded-md">
    <v-navigation-drawer color="white" v-if="user.lvl > 1" v-model="drawer"
      :width="$vuetify.display.xs ? $vuetify.display.width : 260">
      <v-list density="compact" nav>
        <v-list-item :key="'profile'" prepend-icon="mdi-account" :to="{ name: 'Home' }">
          {{ $t('Profile') }}
        </v-list-item>
        <v-list-item :key="'dashboard'" v-if="user.lvl === 2 || user.lvl === 3" prepend-icon="mdi-view-dashboard"
          :to="generateRoute()">
          {{ $t('Manage') }}
        </v-list-item>
        <v-list-item :key="'company'" v-if="user.lvl === 4" prepend-icon="mdi-domain" to="/company">
          {{ $t('Companies') }}
        </v-list-item>
        <v-list-item :key="'users'" v-if="user.lvl === 4" prepend-icon="mdi-account-group" to="/users">
          {{ $t('Users') }}
        </v-list-item>
        <v-list-item :key="'devices'" v-if="user.lvl === 4" prepend-icon="mdi-elevator-passenger-outline" to="/devices">
          {{ $t('Lifts') }}
        </v-list-item>
        <v-list-item :key="'unregistreddevices'" v-if="user.lvl >= 4" prepend-icon="mdi-elevator-passenger"
          to="/unregistreddevices">
          {{ $t('Unregistered lifts') }}
        </v-list-item>
        <v-list-item :key="'files'" v-if="user.lvl >= 4" prepend-icon="mdi-file" to="/file">
          {{ $t('Firmware update') }}
        </v-list-item>
        <v-list-item :key="'files'" v-if="user.lvl >= 4" prepend-icon="mdi-file" to="/PageTest">
          სატესტო
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-app-bar :order="order" color="black">
      <template v-slot:prepend>
        <v-btn v-if="user.lvl >= 2" icon="mdi-menu" @click.stop="drawer = !drawer"></v-btn>
        <img src="../../assets/eideas-logo-vector.svg" width="100" height="40" alt="" />
      </template>
      <template v-slot:append>
        <div>
          <p style="font-size: 1.7rem;">🔔</p>
          <p v-if="notificationCount > 0"
            style="border-radius: 50%; background-color:red; width: 20px;height: 20px;color:white;position: absolute; top:35px;font-size:12px;text-align: center;display: flex;align-items: center;justify-content: center;">
            {{ notificationCount }}</p>
        </div>
      </template>
    </v-app-bar>

    <v-container :class="{
      'pa-1': $vuetify.display.xs,
    }" class="d-md-flex justify-md-space-between">
      <default-view />
    </v-container>


  </v-layout>
  <div class="notification-container">
    <div v-for="notification in  notificationData" :key="notification.id">
  <p>{{ notification.message }}</p>
</div>

  <div class="pagination">
    <button @click="prevPage" :disabled="currentPage === 1">Previous</button>
    <span>Page {{ currentPage }} of {{ totalPages }}</span>
    <button @click="nextPage" :disabled="currentPage === totalPages">Next</button>
  </div>
</div>
</template>


<script>
import DefaultView from './View.vue'
import { mapActions } from 'vuex'
import NotificationComponent from '../../components/NotificationTable.vue';  
export default {
  components: { DefaultView },
  data() {
    return {
      notificationData: [],
      notificationCount: 0,
      totalPages: 1,
      currentPage: 1,
      order: 0,
      drawer: null,
      user: this.$store.state.auth.user,
      languages: [
        { name: 'English', code: 'en', flag: 'us' },
        { name: 'ქართული', code: 'ka', flag: 'ge' },
        // ... add more languages as necessary
      ],
      selectedLanguage: 'ka', // default selected language
    }
  },
  created() {
    this.loadItems()
  },

  methods: {


    loadItems(page = 1) {
      axios.get(`api/notifications/${this.user.id}?page=${page}`).then(({ data }) => {
        console.log("PAGE LOADED")
        console.log(data.data)
        this.notificationCount = data.data.length;
        this.notificationData = data.data;
  
        this.totalPages = data.last_page; 
        this.currentPage = data.current_page;  
      });
    },
    ...mapActions({
      signOut: 'auth/logout',
    }),
    async logout() {
      await axios.post('/api/logout').then(({ data }) => {
        this.signOut()
        this.$router.push({ name: 'Login' })
      })
    },
    generateRoute() {
      if (this.user.company) {
        return '/company/' + this.user.company.id
      }
      if (this.user.device) {
        return `/manager/${this.user.id}/${this.user.device.company_id}`
      }
    },
    setLanguage(lang) {
      console.log('Selected language:', lang)
      // If you're using a package like vue-i18n, you'd set the locale here:
      this.$i18n.locale = lang
    },
  },
}
</script>
<style>
@import 'flag-icon-css/css/flag-icons.min.css';
.notification-container {
    display: flex;
    flex-direction: column; /* Stack notifications */
    align-items: center;
    justify-content: center;
    position: fixed;
    z-index: 10000;
    background-color: black; /* Black background for visibility */
    width: 600px;
    height: 600px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white; /* White text for contrast */
    border: 2px solid red; /* Border for visibility */
}
  .notification {
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-bottom: 10px;
    padding: 10px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  .notification:hover {
    background-color: #f5f5f5;
  }
  .pagination {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
  }
</style>
