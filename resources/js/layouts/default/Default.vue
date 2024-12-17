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
        <v-list-item v-if="user.role == 'company'" :key="'devices'" prepend-icon="mdi-elevator-passenger-outline"
          to="/devices">
          {{ $t('Lifts') }}
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
        <v-list-item :key="'notifications'" v-if="user.lvl >= 4" prepend-icon="mdi-bell" to="/notifications">
          ნოტიფიკაციების მენეჯერი
        </v-list-item>
        <v-list-item :key="'test'" v-if="user.lvl >= 4" prepend-icon="mdi-fire" to="/PageTest">
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
        <div style="cursor:pointer;" @click=" openNotifications()">
          <p style="font-size: 1.7rem;">🔔</p>
          <p v-if="notificationCount > 0"
            style="border-radius: 50%; background-color:red; width: 20px;height: 20px;color:white;position: absolute; top:35px;font-size:12px;text-align: center;display: flex;align-items: center;justify-content: center;">
            {{ notificationCount }}</p>
        </div>

        <v-btn :disabled="this.$i18n.locale === language.code" v-for="language in languages" :key="language.code"
          @click="setLanguage(language.code)">
          <span style="cursor: pointer;">
            <span :class="['flag-icon', `flag-icon-${language.flag}`]"></span>
            <span v-if="!$vuetify.display.xs">{{ language.name }}</span>
          </span>
        </v-btn>
        <v-btn icon="mdi-logout" @click="logout" />



      </template>
    </v-app-bar>

    <v-container :class="{
      'pa-1': $vuetify.display.xs,
    }" class="d-md-flex justify-md-space-between">
      <default-view />
    </v-container>
  </v-layout>
  <div class="notification-wrapper" @click="checkOutsideClick">
    <div v-if="notificationOpen" class="notification-container" @click.stop>
      <div v-for="notification in notificationData" :key="notification.id" class="notification">
        <p> <span v-if="notification.isRead == 0">🔵</span> {{ notification.message.slice(0, 50) }}</p>
        <p>{{ formatDate(notification.created_at) }} </p>

      </div>
      <div class="pagination">
        <button @click="prevPage" :disabled="currentPage === 1">Previous</button>
        <span>Page {{ currentPage }} of {{ totalPages }}</span>
        <button @click="nextPage" :disabled="currentPage === totalPages">Next</button>
      </div>
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
      notificationOpen: false,
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
      selectedLanguage: 'ka',
    }
  },
  created() {
    this.loadItems()
  },
  mounted() {
    document.addEventListener('click', this.checkOutsideClick);
  },
  beforeDestroy() {
    document.removeEventListener('click', this.checkOutsideClick);
  },
  methods: {
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
      return new Date(dateString).toLocaleString('en-US', options);
    },
    openNotifications() {
      this.notificationOpen = !this.notificationOpen
      this.notificationCheck();
    },
    checkOutsideClick(event) {
      const notificationContainer = this.$el.querySelector('.notification-container');
      if (this.notificationOpen && !notificationContainer.contains(event.target)) {
        this.notificationOpen = false;
      }
    },
    loadItems(page = 1) {
      axios.get(`api/notifications/${this.user.id}?page=${page}`).then(({ data }) => {


        this.notificationCount = data.data.filter((val) => val.isRead == 0).length;
        console.log(data.data)
        this.notificationData = data.data;

        this.totalPages = data.last_page;
        this.currentPage = data.current_page;
      });
    },
    notificationCheck() {
      axios.post(`api/notifications/read/${this.user.id}`).then(({ data }) => {
        console.log(data)
      });
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.loadItems(this.currentPage + 1);
      }
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.loadItems(this.currentPage - 1);
      }
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
  flex-direction: column;
  /* Stack notifications */
  align-items: center;
  justify-content: space-between;
  position: fixed;
  z-index: 10000;
  background-color: rgb(255, 255, 255);
  /* White background for visibility */
  width: 400px;
  height: 600px;
  top: 370px;
  right: 50px;

  transform: translate(-50%, -50%);
  color: rgb(29, 22, 22);
  /* White text for contrast */
  border-radius: 5px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
  /* Add shadow */
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
